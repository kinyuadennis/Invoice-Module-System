<?php

namespace App\Http\Controllers\Webhook;

use App\Config\PaymentConstants;
use App\Http\Controllers\Controller;
use App\Payments\DTOs\GatewayCallbackPayload;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Subscription Webhook Controller
 *
 * Handles gateway webhooks/callbacks for subscription payments.
 * Separated from PaymentWebhookController to handle subscription-specific logic.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class SubscriptionWebhookController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Handle Stripe webhooks for subscription payments.
     */
    public function stripe(Request $request)
    {
        $signature = $request->header('Stripe-Signature');
        $payload = $request->all();

        // Verify webhook signature
        $webhookSecret = config('services.stripe.webhook_secret');
        if ($webhookSecret) {
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $event = \Stripe\Webhook::constructEvent(
                    $request->getContent(),
                    $signature,
                    $webhookSecret
                );
                $payload = $event->toArray();
            } catch (\Exception $e) {
                Log::warning('Stripe webhook signature verification failed', [
                    'error' => $e->getMessage(),
                ]);

                // In production, reject invalid signatures
                if (config('app.env') === 'production') {
                    return response()->json(['error' => 'Invalid signature'], 400);
                }
            }
        }

        // Only process payment_intent.succeeded for subscription payments
        $eventType = $payload['type'] ?? null;
        if ($eventType !== 'payment_intent.succeeded') {
            return response()->json(['received' => true, 'ignored' => true], 200);
        }

        try {
            $paymentIntent = $payload['data']['object'] ?? null;
            if (! $paymentIntent || ! isset($paymentIntent['id'])) {
                return response()->json(['received' => true, 'ignored' => true], 200);
            }

            // Create callback payload DTO
            $callbackPayload = new GatewayCallbackPayload(
                rawData: $payload,
                gatewayReference: $paymentIntent['id'],
                signature: $signature
            );

            // Confirm payment via SubscriptionService
            $payment = $this->subscriptionService->confirmPayment(PaymentConstants::GATEWAY_STRIPE, $callbackPayload);

            if ($payment) {
                return response()->json(['received' => true], 200);
            }

            return response()->json(['received' => true, 'ignored' => true], 200);
        } catch (\Exception $e) {
            Log::error('Stripe subscription webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle M-Pesa callbacks for subscription payments.
     */
    public function mpesa(Request $request)
    {
        $payload = $request->all();

        Log::info('M-Pesa subscription callback received', ['payload' => $payload]);

        try {
            // Extract CheckoutRequestID from M-Pesa callback
            $body = $payload['Body'] ?? $payload;
            $stkCallback = $body['stkCallback'] ?? $body;
            $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;

            if (! $checkoutRequestId) {
                Log::warning('M-Pesa callback missing CheckoutRequestID', ['payload' => $payload]);

                return response()->json([
                    'ResultCode' => 1,
                    'ResultDesc' => 'Missing CheckoutRequestID',
                ], 200); // M-Pesa expects 200 even on failure
            }

            // Create callback payload DTO
            $callbackPayload = new GatewayCallbackPayload(
                rawData: $payload,
                gatewayReference: $checkoutRequestId,
                signature: null // M-Pesa doesn't use signature
            );

            // Confirm payment via SubscriptionService
            $payment = $this->subscriptionService->confirmPayment(PaymentConstants::GATEWAY_MPESA, $callbackPayload);

            // M-Pesa expects a specific response format
            if ($payment) {
                return response()->json([
                    'ResultCode' => 0,
                    'ResultDesc' => 'Accepted',
                ], 200);
            }

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Payment not found or processing failed',
            ], 200); // M-Pesa expects 200 even on failure
        } catch (\Exception $e) {
            Log::error('M-Pesa subscription callback processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Failed',
            ], 200); // M-Pesa expects 200 even on failure
        }
    }
}
