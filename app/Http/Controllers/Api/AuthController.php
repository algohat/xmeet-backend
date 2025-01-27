<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use DB;


class AuthController extends Controller
{



    public function getInterest()
    {


        $interests = Interest::all();
        return response()->json($interests);

    }

    // Register a new user
    public function register(Request $request)
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
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        $subscription = Subscription::where('user_id', $user->id)->where('status','active')->latest()->first();


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
        $subscription = Subscription::where('user_id', $user->id)->where('status','active')->latest()->first();

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
        $user->update($request->only('name', 'email', 'phone','age','post_code','interest','gender'));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }


    public function forgotPassword(Request $request)
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
    $interests = $request->query('interests'); // New parameter for interests

    $cacheKey = 'random_users_' . $postal_code . '_' . $gender . '_' . $age . '_' . $interests;

    $randomUsers = Cache::remember($cacheKey, 86400, function () use ($postal_code, $gender, $age, $interests) {
        $query = User::where('post_code', $postal_code);

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

    $query = User::query();

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
 }
 else{

    $users = $query->where('post_code',Auth::user()->post_code)->get();
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
        // $captureResponse = captureOrder($orderId);

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



public function packages(){

    $package = DB::table('packages')->get();
    return response()->json($package);


}


}
