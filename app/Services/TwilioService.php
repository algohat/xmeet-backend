<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    }

    public function sendOTP($phoneNumber, $otp)
    {
        try {
            $message = "Your OTP code is: $otp";

            // Send the OTP
            $response = $this->client->messages->create(
                $phoneNumber,
                [
                    'messagingServiceSid' => env('TWILIO_MESSAGING_SERVICE_SID'),
                    'body' => $message,
                ]
            );

            if ($response->sid) {
                return $response;
            } else {
                \Log::error('Failed to send OTP. Twilio response missing SID.', [
                    'phoneNumber' => $phoneNumber,
                    'otp' => $otp,
                ]);
                return false;
            }
        } catch (\Twilio\Exceptions\RestException $e) {
            \Log::error('Twilio REST Error: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'phoneNumber' => $phoneNumber,
            ]);
            return false;
        } catch (\Exception $e) {
            \Log::error('General Error: ' . $e->getMessage(), [
                'phoneNumber' => $phoneNumber,
                'otp' => $otp,
            ]);
            return false;
        }
    }

}
