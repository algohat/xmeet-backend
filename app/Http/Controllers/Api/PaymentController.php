<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ManagePaidMembershipJob;
use App\Libraries\Membership;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
                    $subscription->update(['order_id' => $response['id']]);
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


        $subscription = Subscription::where('id', $subscriptionId)->where('order_id', $orderId)->first();
        if(!$subscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subscription not found',
            ], 404);
        }
        $user = User::findOrFail($subscription->user_id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }


        if ($subscription->status === 'active') {
            return view('payment.success');
        }

        // Update user design id
        $user->update(['design_id' => 1]);
        $package = Package::find($subscription->package_id);
        $package_end_time = (new Membership())->getExpiredTime($package->validity, strtolower($package->validity_type));

        // Update subscription status
        $subscription->update([
            'status' => 'active',
            'start_date' => now(),
            'end_date' => $package_end_time,
        ]);

        // Create user package
        $membership = new Membership([
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
        $membership->createUserPackage();
        // SETUP JOB FOR SUBSCRIPTION STATUS CHECK
        try {
            ManagePaidMembershipJob::dispatch(["user" => $user])->delay($package_end_time);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch ManagePaidMembershipJob', ['error' => $e->getMessage()]);
        }

        // Generate PDF Invoice
        $invoiceData = [
            'user' => $user,
            'subscription' => $subscription,
            'package' => $package,
            'start_time' => now()->format('Y-m-d H:i'),
            'end_time' => $package_end_time->format('Y-m-d H:i'),
            'payment_method' => 'PayPal',
            'transaction_id' => $request->query('token'),
            'date' => now()->format('Y-m-d'),
        ];

        $pdf = Pdf::loadView('backend.packages.invoice', $invoiceData);
        $fileName = 'invoice_' . $subscription->id . '.pdf';
        $filePath = 'invoices/' . $fileName;

        // Store the PDF
        Storage::put('public/' . $filePath, $pdf->output());

        // Save PDF path in database for admin panel
        $subscription->update(['invoice_path' => $filePath]);

        // Send Invoice via Email
        Mail::send('emails.invoice', $invoiceData, function ($message) use ($user, $filePath, $fileName) {
            $message->to($user->email)
                ->subject('Package purchase invoice')
                ->attach(storage_path('app/public/' . $filePath), [
                    'as' => $fileName,
                    'mime' => 'application/pdf',
                ]);
        });
        return view('payment.success');
    }

    public function cancel(Request $request)
    {
        $subscriptionId = $request->query('subscription_id');

        $subscription = Subscription::findOrFail($subscriptionId);
        if ($subscription->status === 'pending') {
            $subscription->update(['status' => 'canceled']);
        }

        return view('payment.cancel');
    }

}
