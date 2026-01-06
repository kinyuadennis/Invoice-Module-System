<?php

namespace App\Http\Controllers;

use App\Config\PaymentConstants;
use App\Http\Services\PaymentGatewayService;
use App\Jobs\Payments\ProcessWebhookRetry;
use App\Models\Payment;
use App\Payments\DTOs\GatewayCallbackPayload;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * M-Pesa Controller
 *
 * Centralized controller for handling all M-Pesa related operations including:
 * - STK Push callbacks (invoice and subscription payments)
 * - Payment status checking
 * - M-Pesa configuration utilities
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class MpesaController extends Controller
{
    public function __construct(
        private PaymentGatewayService $paymentGatewayService,
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Handle M-Pesa STK Push callback.
     *
     * This is a unified callback handler that processes both invoice and subscription payments.
     * It determines the payment type based on the AccountReference in the callback.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        // Log webhook request for audit trail
        Log::info('M-Pesa callback received', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Verify M-Pesa IP whitelist (if configured)
        $allowedIps = config('services.mpesa.allowed_ips', []);
        $clientIp = $request->ip();

        if (! empty($allowedIps) && ! in_array($clientIp, $allowedIps)) {
            Log::warning('M-Pesa webhook rejected - IP not whitelisted', [
                'ip' => $clientIp,
                'allowed_ips' => $allowedIps,
            ]);

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Unauthorized IP',
            ], 403);
        }

        $payload = $request->all();

        // Extract callback data
        $body = $payload['Body'] ?? $payload;
        $stkCallback = $body['stkCallback'] ?? $body;
        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;

        Log::info('M-Pesa callback processing', [
            'checkout_request_id' => $checkoutRequestId,
            'result_code' => $resultCode,
        ]);

        if (! $checkoutRequestId) {
            Log::warning('M-Pesa callback missing CheckoutRequestID', ['payload' => $payload]);

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Missing CheckoutRequestID',
            ], 200); // M-Pesa expects 200 even on failure
        }

        try {
            // Determine payment type from AccountReference
            $accountReference = $stkCallback['AccountReference'] ?? null;
            $isSubscription = $accountReference && str_starts_with($accountReference, 'SUB_');

            if ($isSubscription) {
                // Handle subscription payment callback
                return $this->handleSubscriptionCallback($payload, $checkoutRequestId);
            }

            // Handle invoice payment callback
            return $this->handleInvoiceCallback($payload, $checkoutRequestId);
        } catch (\Exception $e) {
            Log::error('M-Pesa callback processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Failed',
            ], 200); // M-Pesa expects 200 even on failure
        }
    }

    /**
     * Handle subscription payment callback.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleSubscriptionCallback(array $payload, string $checkoutRequestId)
    {
        try {
            // Create callback payload DTO
            $callbackPayload = new GatewayCallbackPayload(
                rawData: $payload,
                gatewayReference: $checkoutRequestId,
                signature: null // M-Pesa doesn't use signature
            );

            // Confirm payment via SubscriptionService
            $payment = $this->subscriptionService->confirmPayment(PaymentConstants::GATEWAY_MPESA, $callbackPayload);

            if ($payment) {
                return response()->json([
                    'ResultCode' => 0,
                    'ResultDesc' => 'Accepted',
                ], 200);
            }

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Payment not found or processing failed',
            ], 200);
        } catch (\Exception $e) {
            // Queue retry job for failed webhook processing
            Log::warning('M-Pesa subscription callback processing failed, queuing retry', [
                'error' => $e->getMessage(),
                'checkout_request_id' => $checkoutRequestId,
            ]);

            $callbackPayload = new GatewayCallbackPayload(
                rawData: $payload,
                gatewayReference: $checkoutRequestId,
                signature: null
            );

            ProcessWebhookRetry::dispatch(
                PaymentConstants::GATEWAY_MPESA,
                $callbackPayload,
                1
            );

            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Accepted (queued for retry)',
            ], 200);
        }
    }

    /**
     * Handle invoice payment callback.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleInvoiceCallback(array $payload, string $checkoutRequestId)
    {
        try {
            $payment = $this->paymentGatewayService->processMpesaCallback($payload);

            if ($payment) {
                return response()->json([
                    'ResultCode' => 0,
                    'ResultDesc' => 'Accepted',
                ], 200);
            }

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Payment not found or processing failed',
            ], 200);
        } catch (\Exception $e) {
            Log::error('M-Pesa invoice callback processing failed', [
                'error' => $e->getMessage(),
                'checkout_request_id' => $checkoutRequestId,
            ]);

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Failed',
            ], 200);
        }
    }

    /**
     * Check M-Pesa payment status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request, Payment $payment)
    {
        // Verify payment belongs to user's company (if authenticated)
        if ($request->user()) {
            $companyId = \App\Services\CurrentCompanyService::requireId();
            if ($payment->company_id !== $companyId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        // Refresh payment from database
        $payment->refresh();

        return response()->json([
            'status' => $payment->status,
            'gateway' => $payment->gateway,
            'gateway_transaction_id' => $payment->gateway_transaction_id,
            'amount' => $payment->amount,
            'is_terminal' => $payment->isTerminal(),
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'gateway_metadata' => $payment->gateway_metadata,
        ]);
    }

    /**
     * Verify M-Pesa configuration.
     *
     * Utility endpoint to check if M-Pesa is properly configured.
     * Useful for testing and debugging.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyConfig(Request $request)
    {
        // Only allow in non-production environments
        if (config('app.env') === 'production') {
            return response()->json(['error' => 'Not available in production'], 403);
        }

        $config = [
            'consumer_key' => config('services.mpesa.consumer_key') ? 'Set' : 'Missing',
            'consumer_secret' => config('services.mpesa.consumer_secret') ? 'Set' : 'Missing',
            'shortcode' => config('services.mpesa.shortcode'),
            'passkey' => config('services.mpesa.passkey') ? 'Set' : 'Missing',
            'environment' => config('services.mpesa.environment', 'sandbox'),
            'callback_url' => config('services.mpesa.callback_url'),
        ];

        $isConfigured = ! empty($config['consumer_key']) &&
            ! empty($config['consumer_secret']) &&
            ! empty($config['shortcode']) &&
            ! empty($config['passkey']);

        return response()->json([
            'configured' => $isConfigured,
            'config' => $config,
        ]);
    }

    /**
     * Test M-Pesa access token retrieval.
     *
     * Utility endpoint to test M-Pesa API connectivity.
     * Only available in non-production environments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection(Request $request)
    {
        // Only allow in non-production environments
        if (config('app.env') === 'production') {
            return response()->json(['error' => 'Not available in production'], 403);
        }

        $consumerKey = config('services.mpesa.consumer_key');
        $consumerSecret = config('services.mpesa.consumer_secret');
        $environment = config('services.mpesa.environment', 'sandbox');

        if (! $consumerKey || ! $consumerSecret) {
            return response()->json([
                'success' => false,
                'error' => 'M-Pesa credentials not configured',
            ], 400);
        }

        try {
            $tokenUrl = $environment === 'production'
                ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
                : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

            $response = \Illuminate\Support\Facades\Http::withBasicAuth($consumerKey, $consumerSecret)
                ->get($tokenUrl);

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'success' => true,
                    'message' => 'M-Pesa API connection successful',
                    'token_received' => isset($data['access_token']),
                    'expires_in' => $data['expires_in'] ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to get access token',
                'response' => $response->body(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
