<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS message.
     *
     * @param  string  $to  Phone number (E.164 format recommended)
     * @param  string  $message  SMS message content
     */
    public function send(string $to, string $message): bool
    {
        $provider = config('services.sms.provider', 'log');

        return match ($provider) {
            'africastalking' => $this->sendViaAfricasTalking($to, $message),
            'twilio' => $this->sendViaTwilio($to, $message),
            'log' => $this->logSms($to, $message),
            default => $this->logSms($to, $message),
        };
    }

    /**
     * Send SMS via Africa's Talking API.
     */
    protected function sendViaAfricasTalking(string $to, string $message): bool
    {
        try {
            $apiKey = config('services.sms.africastalking.api_key');
            $username = config('services.sms.africastalking.username');
            $from = config('services.sms.africastalking.from');

            if (! $apiKey || ! $username) {
                Log::warning('Africa\'s Talking SMS credentials not configured');

                return false;
            }

            $response = Http::withHeaders([
                'apiKey' => $apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
                'username' => $username,
                'to' => $this->formatPhoneNumber($to),
                'message' => $message,
                'from' => $from,
            ]);

            if ($response->successful()) {
                Log::info('SMS sent via Africa\'s Talking', [
                    'to' => $to,
                    'response' => $response->json(),
                ]);

                return true;
            }

            Log::error('Failed to send SMS via Africa\'s Talking', [
                'to' => $to,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception sending SMS via Africa\'s Talking', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send SMS via Twilio API.
     */
    protected function sendViaTwilio(string $to, string $message): bool
    {
        try {
            $accountSid = config('services.sms.twilio.account_sid');
            $authToken = config('services.sms.twilio.auth_token');
            $from = config('services.sms.twilio.from');

            if (! $accountSid || ! $authToken || ! $from) {
                Log::warning('Twilio SMS credentials not configured');

                return false;
            }

            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'From' => $from,
                    'To' => $this->formatPhoneNumber($to),
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info('SMS sent via Twilio', [
                    'to' => $to,
                    'response' => $response->json(),
                ]);

                return true;
            }

            Log::error('Failed to send SMS via Twilio', [
                'to' => $to,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception sending SMS via Twilio', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Log SMS (for development/testing).
     */
    protected function logSms(string $to, string $message): bool
    {
        Log::info('SMS (logged only)', [
            'to' => $to,
            'message' => $message,
        ]);

        return true;
    }

    /**
     * Format phone number to E.164 format.
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If it starts with 0, replace with country code (Kenya: +254)
        if (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        }

        // If it doesn't start with +, add it
        if (! str_starts_with($phone, '+')) {
            $phone = '+'.$phone;
        }

        return $phone;
    }
}
