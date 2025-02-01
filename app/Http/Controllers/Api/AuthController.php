<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\Membership;
use App\Models\Interest;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\TwilioService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Srmklive\PayPal\Services\PayPal as PayPalClient;


class AuthController extends Controller
{

    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }


    public function getInterest()
    {
        $interests = Interest::all();
        return response()->json($interests);
    }

    // Register a new user
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:20',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'age' => 'required',
            'post_code' => 'nullable',
            'interest' => 'required',
            'gender' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // Create a new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'age' => $request->age,
                'post_code' => $request->post_code,
                'interest' => $request->interest,
                'gender' => $request->gender,
                'is_verified' => false,
            ]);

            $userHash = encryptUserId($user->id);
            $user->identifier = $userHash;
            $user->save();

            // Assign default package to the user
            try {
                (new Membership())->createUserDefaultPackage($user);
            } catch (Exception $exception) {
                return response()->json(['error' => 'Failed to assign package to the user'], 500);
            }

            // Generate OTP for the user
            $otp = rand(100000, 999999);
            DB::table('user_otps')->updateOrInsert(
                ['phone' => $user->phone],
                [
                    'otp' => $otp,
                    'expires_at' => now()->addMinutes(5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Send OTP via Twilio
            $otpResponse = $this->twilioService->sendOTP($user->phone, $otp);

            if ($otpResponse === false) {
                return response()->json(['error' => 'Failed to send OTP'], 500);
            }

            DB::commit();

            return response()->json(['message' => 'OTP sent successfully, OTP will expire in 5 minutes'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to register user or send OTP', 'details' => $e->getMessage()], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $otpRecord = UserOtp::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return response()->json(['error' => 'Invalid or expired OTP'], 400);
        }

        DB::beginTransaction();

        try {
            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $user->update(['is_verified' => true, 'otp_verified_at' => now()]);

            // Delete the OTP from `user_otps` table
            DB::table('user_otps')->where('phone', $request->phone)->delete();

            DB::commit();

            // Send a welcome email
            Mail::send('emails.welcome', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Welcome to Our Platform!');
            });

            return response()->json(['message' => 'OTP verified successfully', 'user' => $user], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to verify OTP', 'details' => $e->getMessage()], 500);
        }
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|exists:users,phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $userVerifyCheck = User::where('phone', $request->phone)->where('is_verified', 1)->first();
            if($userVerifyCheck){
                return response()->json(['message' => 'User is already verified'], 200);
            }
            // Retrieve the user's OTP record
            $otpRecord = DB::table('user_otps')
                ->where('phone', $request->phone)
                ->where('expires_at', '>', now())
                ->first();

            if ($otpRecord) {
                $otp = $otpRecord->otp;
            } else {
                $otp = rand(100000, 999999);

                DB::table('user_otps')->updateOrInsert(
                    ['phone' => $request->phone],
                    [
                        'otp' => $otp,
                        'expires_at' => now()->addMinutes(5),
                        'updated_at' => now(),
                    ]
                );
            }

            // Send the OTP via Twilio
            $otpResponse = $this->twilioService->sendOTP($request->phone, $otp);

            // Check if OTP was successfully sent
            if ($otpResponse === false) {
                return response()->json(['error' => 'Failed to send OTP'], 500);
            }

            DB::commit();

            return response()->json(['message' => 'OTP sent successfully, OTP will expire in 5 minutes'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to resend OTP', 'details' => $e->getMessage()], 500);
        }
    }


    public function registerOld(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'age' => 'required',
            'post_code' => 'nullable',
            'interest' => 'required',
            'gender' => 'required',
            'phone' => 'required',
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'age' => $request->age,
            'post_code' => $request->post_code,
            'interest' => $request->interest,
            'gender' => $request->gender,
            'phone' => $request->phone,
        ]);


        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);


    }


    // Login a user
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'password' => 'required|string',
        ]);

        $user = User::with('userPackage')->where('email', $request->email)->first();
        $subscription = Subscription::where('user_id', $user->id)->where('status', 'active')->latest()->first();


        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The email or password incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'subscription' => $subscription
        ]);
    }

    // Logout a user
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successfully.']);
    }

    public function changePassword(Request $request)
    {

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match our records.'],
            ]);
        }

        Auth::user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function showProfile()
    {
        $user = Auth::user();
        $subscription = Subscription::where('user_id', $user->id)->where('status', 'active')->latest()->first();

        return response()->json([
            'user' => $user,
            'subscription' => $subscription
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string',
            'age' => 'required',
            'post_code' => 'nullable',
            'interest' => 'required',
            'gender' => 'required',
            'phone' => 'required',
        ]);


        // Update user details
        $user = Auth::user();
        $user->update($request->only('name', 'email', 'phone', 'age', 'post_code', 'interest', 'gender'));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }

    public function forgotPasswordGenerate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $newPassword = $this->generatePassword();

        $user->password = bcrypt($newPassword);
        $user->save();

        // Send the new password via email
        try {
            Mail::send('emails.reset-password', ['user' => $user, 'password' => $newPassword], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Password Has Been Reset');
            });

            return response()->json(['message' => 'New password sent successfully to your email address.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send email. Please try again later.', 'error' => $e->getMessage()], 500);
        }
    }

    private function generatePassword()
    {
        $length = 8;
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
        $password = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $max)];
        }

        return $password;
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);


        $user = User::where('email', $request->email)->first();
        $token = Str::random(60);

        $checkToken = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        if ($checkToken) {
            DB::table('password_reset_tokens')->where('email', $user->email)->update([
                'token' => $token,
                'created_at' => now(),
            ]);
        } else {
            DB::table('password_reset_tokens')->insert([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
            ]);
        }

        // Send the reset email
        Mail::send('emails.reset-password', ['user' => $user, 'token' => $token], function ($message) use ($user) {
            $message->to($user->email)->subject('Password Reset Request');
        });

        return response()->json(['message' => 'Password reset link sent successfully.'], 200);
    }

    public function forgotPasswordLaravel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent successfully.'], 200);
        } else {
            return response()->json(['message' => 'Failed to send reset link. Please try again later.'], 500);
        }
    }

    public function getRandomUserByPostalCode(Request $request)
    {
        $postal_code = Auth::user()->post_code;
        $gender = $request->query('gender');
        $age = $request->query('age');
        $interests = $request->query('interests');
        $post_code = $request->query('post_code');

        $cacheKey = 'random_users_' . $postal_code . '_' . $gender . '_' . $age . '_' . $interests;

        $randomUsers = Cache::remember($cacheKey, 86400, function () use ($postal_code) {
            return User::where('post_code', $postal_code)
                ->where('id', '!=', Auth::id())
                ->inRandomOrder()
                ->limit(10)
                ->get();
        });

        // Apply filters on the fetched users
        $filteredUsers = $randomUsers->filter(function ($user) use ($gender, $age, $interests, $post_code) {
            if ($gender && $user->gender !== $gender) {
                return false;
            }

            if ($age) {
                $ageRange = explode('-', $age);
                if (count($ageRange) === 2) {
                    $userAge = $user->age;
                    if ($userAge < trim($ageRange[0]) || $userAge > trim($ageRange[1])) {
                        return false;
                    }
                }
            }

            if ($interests) {
                $interestArray = explode(',', $interests);
                $userInterests = explode(',', $user->interest);
                $matchingInterests = array_intersect($interestArray, $userInterests);
                if (empty($matchingInterests)) {
                    return false;
                }
            }

            if ($post_code && $user->post_code !== $post_code) {
                return false;
            }

            return true;
        });

        $filteredUsers = $filteredUsers->values();

        if ($filteredUsers->isEmpty()) {
            return response()->json(['message' => 'No users found for the provided postal code'], 404);
        }

        return response()->json(['users' => $filteredUsers], 200);
    }

    public function getRandomUserByPostalCodeOld(Request $request)
    {
        $postal_code = Auth::user()->post_code;
        $gender = $request->query('gender');
        $age = $request->query('age');
        $interests = $request->query('interests'); // New parameter for interests

        $cacheKey = 'random_users_' . $postal_code . '_' . $gender . '_' . $age . '_' . $interests;

        $randomUsers = Cache::remember($cacheKey, 86400, function () use ($postal_code, $gender, $age, $interests) {
            $query = User::where('post_code', $postal_code)->where('id', '!=', Auth::id());

            if ($gender) {
                $query->where('gender', $gender);
            }

            if ($age) {
                $ageRange = explode('-', $age);
                if (count($ageRange) === 2) {
                    $query->whereBetween('age', [trim($ageRange[0]), trim($ageRange[1])]);
                }
            }

            if ($interests) {
                $interestArray = explode(',', $interests); // Example: 'football,music'
                $query->whereJsonContains('interests', $interestArray);
            }

            return $query->inRandomOrder()->limit(10)->get();
        });

        if ($randomUsers->isEmpty()) {
            return response()->json(['message' => 'No users found for the provided postal code'], 404);
        }

        return response()->json(['users' => $randomUsers], 200);
    }


    public function checkSubscriptionStatus()
    {
        $userId = auth()->id();

        $subscription = Subscription::where('user_id', $userId)
            ->where('end_date', '>', Carbon::now())
            ->first();

        if ($subscription) {
            return response()->json(['status' => 'active', 'end_date' => $subscription->end_date], 200);
        } else {
            return response()->json(['status' => 'expired'], 200);
        }
    }


    public function getAllUsers(Request $request)
    {
        $subscription = Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->first();

        if (!$subscription) {
            return response()->json(['message' => 'Subscription required or expired'], 403);
        }

        $query = User::query()->where('id', '!=', auth()->id());

        if ($request->has('gender')) {
            $query->where('gender', $request->query('gender'));
        }

        if ($request->has('postal_code')) {
            $query->where('post_code', $request->query('postal_code'));
        } else {
            $query->where('post_code', auth()->user()->post_code);
        }

        if ($request->has('age')) {
            $ageRange = explode('-', $request->query('age'));
            if (count($ageRange) === 2) {
                $query->whereBetween('age', [trim($ageRange[0]), trim($ageRange[1])]);
            }
        }

        if ($request->has('interests')) {
            $interestArray = explode(',', $request->query('interests'));
            $query->where(function ($q) use ($interestArray) {
                foreach ($interestArray as $interest) {
                    $q->orWhere('interest', 'LIKE', '%' . trim($interest) . '%');
                }
            });
        }

        // Pagination
        $limit = $request->query('limit', 20);
        $users = $query->paginate($limit);

        return response()->json($users, 200);
    }

    public function getAllUsersOld(Request $request)
    {
        $subscription = Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->first();

        if (!$subscription) {
            return response()->json(['message' => 'Subscription required or expired'], 403);
        }

        $query = User::query()->where('id', '!=', Auth::id());

        if ($request->has('gender')) {
            $query->where('gender', $request->query('gender'));
        }

        if ($request->has('postal_code')) {
            $query->where('post_code', $request->query('postal_code'));
        }

        if ($request->has('age')) {
            $ageRange = explode('-', $request->query('age'));
            if (count($ageRange) === 2) {
                $query->whereBetween('age', [trim($ageRange[0]), trim($ageRange[1])]);
            }
        }

        if ($request->has('interests')) {
            $interestArray = explode(',', $request->query('interests'));
            $query->whereJsonContains('interests', $interestArray);
        }


        if ($request->has('postal_code')) {
            $users = $query->get();
        } else {

            $users = $query->where('post_code', Auth::user()->post_code)->get();
        }


        return response()->json(['users' => $users], 200);
    }


    public function subscribeToPackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $package = Package::findOrFail($request->package_id);
        $startDate = now();
        $endDate = $startDate->copy()->addDays($package->duration);

        // Step 1: Create the subscription record with a pending status
        $subscription = Subscription::create([
            'user_id' => auth()->id(),
            'package_id' => $package->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
        ]);

        $provider = new PayPalClient();
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        // Step 2: Create PayPal order with subscription details
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('paypal.success', ['subscription_id' => $subscription->id]),
                "cancel_url" => route('paypal.cancel', ['subscription_id' => $subscription->id])
            ],
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $package->price,
                    ],
                ],
            ],
        ]);

        // Step 3: Handle PayPal response and return approval link
        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return response()->json([
                        'status' => 'success',
                        'approval_link' => $link['href'],
                        'order_id' => $response['id'],
                        'subscription_id' => $subscription->id,
                    ], 200);
                }
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create PayPal order',
        ], 400);
    }


    public function success(Request $request)
    {
        $orderId = $request->query('token');
        $subscriptionId = $request->query('subscription_id');


        $subscription = Subscription::findOrFail($subscriptionId);


        $subscription->update([
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays($subscription->package->duration),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment completed successfully.',
            'order_id' => $orderId,
            'subscription_id' => $subscriptionId,
        ]);
    }


    public function cancel(Request $request)
    {
        $subscriptionId = $request->query('subscription_id');

        $subscription = Subscription::findOrFail($subscriptionId);
        if ($subscription->status === 'pending') {
            $subscription->update(['status' => 'canceled']);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Payment was canceled.',
        ]);
    }


    public function packages()
    {

        $package = DB::table('packages')->get();
        return response()->json($package);
    }


}
