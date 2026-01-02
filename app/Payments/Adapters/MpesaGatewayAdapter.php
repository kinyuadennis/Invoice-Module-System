<?php

namespace App\Payments\Adapters;

use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\DTOs\GatewayCallbackPayload;
use App\Payments\DTOs\GatewayResponse;
use App\Payments\DTOs\GatewayResult;
use App\Payments\DTOs\PaymentContext;
use App\Payments\DTOs\PaymentResult;
use App\Payments\DTOs\SubscriptionContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * M-Pesa Gateway Adapter
 *
 * Implements PaymentGatewayInterface for M-Pesa Daraja API.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1, Section 5.1
 *
 * M-Pesa-specific behavior:
 * - Manual, system-driven renewals (no native recurring support)
 * - STK Push for payment initiation
 * - Callback-based confirmation
 * - No native subscription cancellation (throws UnsupportedOperationException)
 */
class MpesaGatewayAdapter implements PaymentGatewayInterface
{
    /**
     * Initiate a payment request with M-Pesa STK Push.
     *
     * @param  PaymentContext  $context  Payment context
     * @return GatewayResponse Response with CheckoutRequestID and metadata
     *
     * @throws \Exception If STK Push initiation fails
     */
    public function initiatePayment(PaymentContext $context): GatewayResponse
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

            // Format phone number (extract from userDetails)
            $phoneNumber = $context->userDetails['phone'] ?? '';
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            if (strlen($phoneNumber) === 9) {
                $phoneNumber = '254'.$phoneNumber;
            } elseif (strlen($phoneNumber) === 10 && substr($phoneNumber, 0, 1) === '0') {
                $phoneNumber = '254'.substr($phoneNumber, 1);
            }

            if (empty($phoneNumber)) {
                throw new \Exception('Phone number is required for M-Pesa payment');
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
                    'Amount' => (int) $context->amount,
                    'PartyA' => $phoneNumber,
                    'PartyB' => $shortcode,
                    'PhoneNumber' => $phoneNumber,
                    'CallBackURL' => $callbackUrl,
                    'AccountReference' => $context->reference,
                    'TransactionDesc' => $context->description,
                ]
            );

            if (! $stkResponse->successful()) {
                throw new \Exception('M-Pesa API error: '.$stkResponse->body());
            }

            $responseData = $stkResponse->json();

            if (isset($responseData['ResponseCode']) && $responseData['ResponseCode'] === '0') {
                return new GatewayResponse(
                    transactionId: $responseData['CheckoutRequestID'],
                    clientSecret: null, // M-Pesa doesn't use client_secret
                    success: true,
                    metadata: [
                        'CheckoutRequestID' => $responseData['CheckoutRequestID'],
                        'MerchantRequestID' => $responseData['MerchantRequestID'],
                        'CustomerMessage' => $responseData['CustomerMessage'] ?? 'STK Push sent successfully',
                        'ResponseCode' => $responseData['ResponseCode'],
                    ]
                );
            }

            throw new \Exception($responseData['errorMessage'] ?? 'M-Pesa request failed');
        } catch (\Exception $e) {
            Log::error('M-Pesa payment initiation failed', [
                'subscription_id' => $context->subscriptionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Confirm a payment from M-Pesa callback.
     *
     * Note: This method parses the callback and returns PaymentResult.
     * It does NOT create or update Payment records - that is the responsibility
     * of the SubscriptionService (per blueprint: gateways never mutate domain models).
     *
     * @param  GatewayCallbackPayload  $payload  Callback payload from M-Pesa
     * @return PaymentResult Result with payment status and metadata
     *
     * @throws \Exception If payment confirmation fails
     */
    public function confirmPayment(GatewayCallbackPayload $payload): PaymentResult
    {
        // M-Pesa sends data in Body.stkCallback format
        $body = $payload->rawData['Body'] ?? $payload->rawData;
        $stkCallback = $body['stkCallback'] ?? $body;

        if (! $stkCallback || ! isset($stkCallback['CheckoutRequestID'])) {
            throw new \Exception('M-Pesa callback missing required fields: CheckoutRequestID');
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

        // Determine payment status
        if ($resultCode === 0 && isset($metadata['MpesaReceiptNumber'])) {
            // Payment successful
            $status = 'confirmed';
            $gatewayReference = $metadata['MpesaReceiptNumber'];
            $metadata['ResultCode'] = $resultCode;
            $metadata['ResultDesc'] = $stkCallback['ResultDesc'] ?? 'Success';
        } else {
            // Payment failed
            $status = 'failed';
            $gatewayReference = $checkoutRequestId; // Use CheckoutRequestID as reference for failed payments
            $metadata['ResultCode'] = $resultCode;
            $metadata['ResultDesc'] = $stkCallback['ResultDesc'] ?? 'Payment failed';
            $metadata['error'] = $stkCallback['ResultDesc'] ?? 'Payment failed';
        }

        // Note: paymentId will be set by SubscriptionService after Payment record is created/found
        return new PaymentResult(
            status: $status,
            paymentId: '', // Will be set by service layer
            gatewayReference: $gatewayReference,
            metadata: array_merge($metadata, [
                'CheckoutRequestID' => $checkoutRequestId,
                'MerchantRequestID' => $stkCallback['MerchantRequestID'] ?? null,
            ])
        );
    }

    /**
     * Cancel a subscription with M-Pesa.
     *
     * M-Pesa does not support native subscription cancellation.
     * This method always throws UnsupportedOperationException.
     *
     * @param  SubscriptionContext  $context  Subscription context
     * @return GatewayResult Never returns (always throws)
     *
     * @throws \UnsupportedOperationException Always thrown - M-Pesa does not support cancellation
     */
    public function cancelSubscription(SubscriptionContext $context): GatewayResult
    {
        throw new \UnsupportedOperationException('M-Pesa does not support subscription cancellation. Cancellation is handled internally.');
    }

    /**
     * Check if M-Pesa supports recurring payments.
     *
     * @return bool Always returns false - M-Pesa has no native recurring support
     */
    public function supportsRecurring(): bool
    {
        return false;
    }
}
