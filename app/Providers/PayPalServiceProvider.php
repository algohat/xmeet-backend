<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PayPalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ApiContext::class, function ($app) {
            return (new ApiContext(
                new OAuthTokenCredential(
                    env('PAYPAL_CLIENT_ID'), // Your PayPal Client ID
                    env('PAYPAL_SECRET')      // Your PayPal Secret
                )
            ))->setConfig([
                'mode' => env('PAYPAL_MODE', 'sandbox'), // Set the mode (sandbox or live)
            ]);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
