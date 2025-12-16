<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Initiate a Stripe payment.
     */
    public function initiateStripePayment(Invoice $invoice, array $metadata = []): array
    {
        $stripeKey = config('services.stripe.key');
        $stripeSecret = config('services.stripe.secret');

        if (! $stripeKey || ! $stripeSecret) {
            throw new \Exception('Stripe credentials not configured');
        }

        try {
            // Create Stripe Payment Intent
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$stripeSecret,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => (int) ($invoice->grand_total * 100), // Convert to cents
                'currency' => strtolower($invoice->company->currency ?? 'kes'),
                'metadata' => array_merge([
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number ?? $invoice->invoice_reference,
                    'company_id' => $invoice->company_id,
                ], $metadata),
                'description' => 'Invoice Payment: '.($invoice->invoice_number ?? $invoice->invoice_reference),
            ]);

            if ($response->successful()) {
                $paymentIntent = $response->json();

                return [
                    'success' => true,
                    'client_secret' => $paymentIntent['client_secret'],
                    'payment_intent_id' => $paymentIntent['id'],
                ];
            }

            throw new \Exception('Stripe API error: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Stripe payment initiation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Initiate an M-Pesa STK Push payment.
     */
    public function initiateMpesaPayment(Invoice $invoice, string $phoneNumber): array
    {
        $consumerKey = config('services.mpesa.consumer_key');
        $consumerSecret = config('services.mpesa.consumer_secret');
        $shortcode = config('services.mpesa.shortcode');
        $passkey = config('services.mpesa.passkey');
        $environment = config('services.mpesa.environment', 'sandbox');
        $callbackUrl = config('services.mpesa.callback_url');

        if (! $consumerKey || ! $consumerSecret || ! $shortcode || ! $passkey) {
            throw new \Exception('M-Pesa credentials not configured');
        }

        try {
            // Get access token
            $tokenResponse = Http::withBasicAuth($consumerKey, $consumerSecret)
                ->get($environment === 'production'
                    ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
                    : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
                );

            if (! $tokenResponse->successful()) {
                throw new \Exception('Failed to get M-Pesa access token');
            }

            $accessToken = $tokenResponse->json()['access_token'];

            // Generate timestamp and password
            $timestamp = date('YmdHis');
            $password = base64_encode($shortcode.$passkey.$timestamp);

            // Format phone number (remove + and ensure it starts with 254)
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            if (strlen($phoneNumber) === 9) {
                $phoneNumber = '254'.$phoneNumber;
            } elseif (strlen($phoneNumber) === 10 && substr($phoneNumber, 0, 1) === '0') {
                $phoneNumber = '254'.substr($phoneNumber, 1);
            }

            // Initiate STK Push
            $stkResponse = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
            ])->post($environment === 'production'
                ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
                : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
                [
                    'BusinessShortCode' => $shortcode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'Amount' => (int) $invoice->grand_total,
                    'PartyA' => $phoneNumber,
                    'PartyB' => $shortcode,
                    'PhoneNumber' => $phoneNumber,
                    'CallBackURL' => $callbackUrl,
                    'AccountReference' => 'INV'.($invoice->invoice_number ?? $invoice->id),
                    'TransactionDesc' => 'Invoice Payment: '.($invoice->invoice_number ?? $invoice->invoice_reference),
                ]
            );

            if ($stkResponse->successful()) {
                $responseData = $stkResponse->json();

                if (isset($responseData['ResponseCode']) && $responseData['ResponseCode'] === '0') {
                    return [
                        'success' => true,
                        'checkout_request_id' => $responseData['CheckoutRequestID'],
                        'merchant_request_id' => $responseData['MerchantRequestID'],
                        'message' => $responseData['CustomerMessage'] ?? 'STK Push sent successfully',
                    ];
                }

                throw new \Exception($responseData['errorMessage'] ?? 'M-Pesa request failed');
            }

            throw new \Exception('M-Pesa API error: '.$stkResponse->body());
        } catch (\Exception $e) {
            Log::error('M-Pesa payment initiation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify and process a Stripe webhook.
     */
    public function processStripeWebhook(array $payload): ?Payment
    {
        $eventType = $payload['type'] ?? null;
        $paymentIntent = $payload['data']['object'] ?? null;

        if (! $eventType || ! $paymentIntent) {
            return null;
        }

        // Only process payment_intent.succeeded
        if ($eventType !== 'payment_intent.succeeded') {
            return null;
        }

        $invoiceId = $paymentIntent['metadata']['invoice_id'] ?? null;
        if (! $invoiceId) {
            Log::warning('Stripe webhook missing invoice_id', ['payload' => $paymentIntent]);

            return null;
        }

        $invoice = Invoice::find($invoiceId);
        if (! $invoice) {
            Log::warning('Stripe webhook invoice not found', ['invoice_id' => $invoiceId]);

            return null;
        }

        // Check if payment already exists
        $existingPayment = Payment::where('gateway_transaction_id', $paymentIntent['id'])->first();
        if ($existingPayment) {
            return $existingPayment;
        }

        // Create payment record
        $payment = Payment::create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
            'amount' => $paymentIntent['amount'] / 100, // Convert from cents
            'payment_method' => 'stripe',
            'gateway' => 'stripe',
            'gateway_transaction_id' => $paymentIntent['id'],
            'gateway_payment_intent_id' => $paymentIntent['id'],
            'gateway_status' => 'completed',
            'gateway_metadata' => $paymentIntent,
            'payment_date' => now(),
            'paid_at' => now(),
        ]);

        // Update invoice status
        $paymentService = app(\App\Http\Services\PaymentService::class);
        $paymentService->recordPayment($invoice, [
            'amount' => $payment->amount,
            'payment_method' => 'stripe',
            'payment_date' => now(),
        ]);

        return $payment;
    }

    /**
     * Process an M-Pesa callback.
     */
    public function processMpesaCallback(array $payload): ?Payment
    {
        // M-Pesa sends data in Body.stkCallback format
        $body = $payload['Body'] ?? $payload;
        $stkCallback = $body['stkCallback'] ?? $body;

        if (! $stkCallback || ! isset($stkCallback['CheckoutRequestID'])) {
            Log::warning('M-Pesa callback missing required fields', ['payload' => $payload]);

            return null;
        }

        $resultCode = $stkCallback['ResultCode'] ?? null;
        $checkoutRequestId = $stkCallback['CheckoutRequestID'];
        $callbackMetadata = $stkCallback['CallbackMetadata']['Item'] ?? [];

        // Extract metadata
        $metadata = [];
        foreach ($callbackMetadata as $item) {
            if (isset($item['Name']) && isset($item['Value'])) {
                $metadata[$item['Name']] = $item['Value'];
            }
        }

        // Find payment by checkout request ID
        $payment = Payment::where('gateway', 'mpesa')
            ->whereJsonContains('gateway_metadata->CheckoutRequestID', $checkoutRequestId)
            ->first();

        if (! $payment) {
            Log::warning('M-Pesa callback payment not found', ['checkout_request_id' => $checkoutRequestId]);

            return null;
        }

        if ($resultCode === 0 && isset($metadata['MpesaReceiptNumber'])) {
            // Payment successful
            $payment->update([
                'gateway_status' => 'completed',
                'gateway_transaction_id' => $metadata['MpesaReceiptNumber'],
                'mpesa_reference' => $metadata['MpesaReceiptNumber'],
                'gateway_metadata' => array_merge($payment->gateway_metadata ?? [], $metadata, [
                    'ResultCode' => $resultCode,
                    'ResultDesc' => $stkCallback['ResultDesc'] ?? 'Success',
                ]),
                'paid_at' => now(),
            ]);

            // Update invoice status
            $paymentService = app(\App\Http\Services\PaymentService::class);
            $paymentService->recordPayment($payment->invoice, [
                'amount' => $payment->amount,
                'payment_method' => 'mpesa',
                'mpesa_reference' => $metadata['MpesaReceiptNumber'],
                'payment_date' => now(),
            ]);

            return $payment;
        }

        // Payment failed
        $payment->update([
            'gateway_status' => 'failed',
            'gateway_metadata' => array_merge($payment->gateway_metadata ?? [], [
                'ResultCode' => $resultCode,
                'ResultDesc' => $stkCallback['ResultDesc'] ?? 'Payment failed',
                'error' => $stkCallback['ResultDesc'] ?? 'Payment failed',
            ]),
        ]);

        return null;
    }
}
