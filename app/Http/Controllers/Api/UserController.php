<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ExampleJob;
use App\Models\Interest;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserPackage;
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
use Illuminate\Validation\ValidationException;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class UserController extends Controller
{

    public function showProfile()
    {
        $user = Auth::user();
        $user->update(['last_login_at' => now()]);
        $subscription = Subscription::where('user_id', $user->id)->where('status', 'active')->latest()->first();

        $chatOpenCounter = $user->chatOpens()->count();
        $user->userPackage;
        $user->chatOpenedUsers;

        $MessageSendCount = DB::table('chat_opens')
            ->where('sender_id', auth()->id())
            ->count();

        $MessageReceiveCount = DB::table('chat_opens')
            ->where('receiver_id', auth()->id())
            ->count();

        // Hide phone if the user is verified
        if ($user->is_verified == 1) {
            $user->makeHidden('phone');
        }

        return response()->json([
            'total_chat_open' => $chatOpenCounter,
            'total_message_send' => $MessageSendCount,
            'total_message_received' => $MessageReceiveCount,
            'user' => $user,
            'subscription' => $subscription
        ], 200);
    }

    public function userDetails($identifier)
    {
        $user = User::where('identifier', $identifier)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->makeHidden('phone');
        $user->is_blocked = auth()->user()->blockedUsers->contains($user->id) ? 1 : 0;

        return response()->json(['user' => $user], 200);
    }

    public function disableAccount(Request $request)
    {
        $request->validate([
            'disable' => 'required|boolean'
        ]);

        $user = Auth::user();
        $user->update(['is_disable' => $request->query('disable')]);

        return response()->json(['message' => 'Account disable status changed successfully'], 200);
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
            'design_id' => 'nullable',
            'phone' => 'nullable',
        ]);


        // Update user details
        $user = Auth::user();
        $user->update($request->only('name', 'email', 'phone', 'age', 'post_code', 'interest', 'gender', 'design_id'));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
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

    public function checkCurrentPackage()
    {
        $userId = auth()->id();

        $current_package = UserPackage::with('userPackageFeature')->where('user_id', $userId)
            ->where('status', 2)
            ->first();

        $subscription = Subscription::where('user_id', $userId)
            ->where('end_date', '>=', Carbon::now())
            ->get();

        if ($current_package) {
            return response()->json(['status' => 'active', 'package' => $current_package, 'subscription' => $subscription], 200);
        } else {
            return response()->json(['status' => 'expired'], 200);
        }
    }

    public function getAllUsers(Request $request)
    {
        $query = User::query()->where('id', '!=', auth()->id())->where('is_verified', 1);

        if ($request->has('gender') && $request->query('gender') !== null) {
            $query->where('gender', $request->query('gender'));
        }

        if ($request->has('post_code') && $request->query('post_code') !== null) {
            $query->where('post_code', 'LIKE', $request->query('post_code') . '%');
        }

        if ($request->has('age') && $request->query('age') !== null) {
            $ageRange = explode('-', $request->query('age'));
            if (count($ageRange) === 2) {
                $minAge = trim($ageRange[0]);
                $maxAge = trim($ageRange[1]);
                $query->whereBetween('age', [$minAge, $maxAge]);
            }
        }

        if ($request->has('interest') && $request->query('interest') !== null) {
            $interestArray = explode(',', $request->query('interest'));
            $query->where(function ($q) use ($interestArray) {
                foreach ($interestArray as $interest) {
                    $q->orWhereRaw("FIND_IN_SET(?, interest)", [trim($interest)]);
                }
            });
        }

        $postCodeUsers = clone $query;
        $postCodeUsers->where('post_code', auth()->user()->post_code);

        $otherPostCodeUsers = clone $query;
        $otherPostCodeUsers->where('post_code', '!=', auth()->user()->post_code);

        $users = $postCodeUsers->union($otherPostCodeUsers);
        $users = $users->paginate($request->query('limit', 20));

        // Count total chat entries for the authenticated user
        $chatOpenCounter = DB::table('chat_opens')
            ->where('sender_id', auth()->id())
            ->orWhere('receiver_id', auth()->id())
            ->count();

        $MessageSendCount = DB::table('chat_opens')
            ->where('sender_id', auth()->id())
            ->count();

        $MessageReceiveCount = DB::table('chat_opens')
            ->where('receiver_id', auth()->id())
            ->count();

        $users->getCollection()->transform(function ($user) {
            $user->makeHidden('phone'); // Hide the phone number
            $user->is_chat_started = isChatStarted($user->id);
            $user->message_send_count = DB::table('chat_opens')
                ->where('sender_id', $user->id)
                ->count();

            $user->message_received_count = DB::table('chat_opens')
                ->where('receiver_id', $user->id)
                ->count();
            $user->is_blocked = auth()->user()->blockedUsers->contains($user->id) ? 1 : 0;
            return $user;
        });

        $usersData = $users->toArray();
        $usersData['total_chat_opened'] = $chatOpenCounter;
        $usersData['total_message_send'] = $MessageSendCount;
        $usersData['total_message_received'] = $MessageReceiveCount;

        return response()->json($usersData, 200);
    }

    public function packages()
    {
        $package = DB::table('packages')->get();
        return response()->json($package);
    }

    public function deleteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'confirm_password' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Password is incorrect.'], 400);
        }

        try {
            DB::transaction(function () use ($user) {
                $userPackage = $user->userPackage;
                if ($userPackage) {
                    $userPackage->delete();
                }
                $user->delete();
            });

            return response()->json(['message' => 'User deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the user information.'], 500);
        }
    }

    public function setIdentifier()
    {
        $users = User::whereNull('identifier')->get();
        foreach ($users as $user) {
            $userHash = encryptUserId($user->id);
            $user->identifier = $userHash;
            $user->save();
        }
        return response()->json(['message' => 'User identifiers set successfully'], 200);
    }


    /*Deprecated Functions*/

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

    public function forgotPassword(Request $request)
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

    public function forgotPasswordOld(Request $request)
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
                ->where('is_verified', 1)
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


    public function success(Request $request)
    {
        $orderId = $request->query('token');
        $subscriptionId = $request->query('subscription_id');


        $subscription = Subscription::findOrFail($subscriptionId);
        $user = User::findOrFail($subscription->user_id);
        if ($user) {
            $user->update(['design_id' => 1]);
        }


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


}
