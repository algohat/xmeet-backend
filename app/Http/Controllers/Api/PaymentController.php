<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ManagePaidMembershipJob;
use App\Libraries\Membership;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentController extends Controller
{
    public function subscribeToPackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $package = Package::findOrFail($request->package_id);
        $startDate = now();
        $endDate = (new Membership())->getExpiredTime($package->validity, strtolower($package->validity_type));

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
        if(!$subscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subscription not found',
            ], 404);
        }

        // Update user design id
        $user = User::findOrFail($subscription->user_id);
        if ($user) {
            $user->update(['design_id' => 1]);
        }
        $package = Package::find($subscription->package_id);
        $package_end_time = (new Membership())->getExpiredTime($package->validity, strtolower($package->validity_type));

        // Update subscription status
        $subscription->update([
            'status' => 'active',
            'start_date' => now(),
            'end_date' => $package_end_time,
        ]);

        // Create user package
        $subscription = new Membership([
            'user' => $user,
            'package_info' => $package,
            'request_input' => [
                'order_id' => $request->query('token'),
                'payment_medium' => 'paypal',
                'payer_id' => $request->query('PayerID'),
                'transaction_id' => $request->query('paymentId'),
            ],
            'payment_status' => 2,
        ]);
        $subscription->createUserPackage();
        // SETUP JOB FOR SUBSCRIPTION STATUS CHECK
        try {
            ManagePaidMembershipJob::dispatch(["user" => $user])->delay($package_end_time);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch ManagePaidMembershipJob', ['error' => $e->getMessage()]);
        }

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
