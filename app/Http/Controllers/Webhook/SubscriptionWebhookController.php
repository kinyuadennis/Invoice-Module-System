<?php

namespace App\Http\Controllers\Webhook;

use App\Config\PaymentConstants;
use App\Http\Controllers\Controller;
use App\Jobs\Payments\ProcessWebhookRetry;
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
        // Log webhook request for audit trail (per blueprint section 7)
        Log::info('Stripe subscription webhook received', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
            'event_type' => $request->input('type'),
        ]);

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

        $eventType = $payload['type'] ?? null;
        $dataObject = ($payload['data'] ?? [])['object'] ?? [];

        try {
            // Handle different Stripe webhook events for subscriptions
            switch ($eventType) {
                case 'payment_intent.succeeded':
                    // One-time payment or renewal payment succeeded
                    if (! isset($dataObject['id'])) {
                        return response()->json(['received' => true, 'ignored' => true], 200);
                    }

                    // Check if this is a subscription payment (via metadata)
                    $subscriptionId = $dataObject['metadata']['subscription_id'] ?? null;
                    if (! $subscriptionId) {
                        // Not a subscription payment, ignore
                        return response()->json(['received' => true, 'ignored' => true], 200);
                    }

                    // Create callback payload DTO
                    $callbackPayload = new GatewayCallbackPayload(
                        rawData: $payload,
                        gatewayReference: $dataObject['id'],
                        signature: $signature
                    );

                    // Confirm payment via SubscriptionService
                    try {
                        $payment = $this->subscriptionService->confirmPayment(PaymentConstants::GATEWAY_STRIPE, $callbackPayload);

                        if ($payment) {
                            return response()->json(['received' => true], 200);
                        }

                        return response()->json(['received' => true, 'ignored' => true], 200);
                    } catch (\Exception $e) {
                        // Queue retry job for failed webhook processing
                        Log::warning('Stripe webhook processing failed, queuing retry', [
                            'error' => $e->getMessage(),
                            'gateway_reference' => $dataObject['id'],
                        ]);

                        ProcessWebhookRetry::dispatch(
                            PaymentConstants::GATEWAY_STRIPE,
                            $callbackPayload,
                            1
                        );

                        // Return 200 to acknowledge receipt (we'll retry in background)
                        return response()->json(['received' => true, 'queued_for_retry' => true], 200);
                    }

                case 'invoice.payment_succeeded':
                    // Stripe subscription invoice payment succeeded (native Stripe subscriptions)
                    // Note: We handle this for Stripe's native subscription feature
                    // Our system primarily uses manual renewals, but this supports Stripe subscriptions
                    $subscriptionId = $dataObject['subscription'] ?? null;
                    if ($subscriptionId) {
                        // Find subscription by Stripe subscription ID
                        $subscription = \App\Models\Subscription::where('payment_reference', $subscriptionId)
                            ->where('gateway', PaymentConstants::GATEWAY_STRIPE)
                            ->first();

                        if ($subscription) {
                            // Update next_billing_at from Stripe invoice
                            $periodEnd = $dataObject['period_end'] ?? null;
                            if ($periodEnd) {
                                $subscription->update([
                                    'next_billing_at' => \Carbon\Carbon::createFromTimestamp($periodEnd),
                                ]);
                            }
                        }
                    }

                    return response()->json(['received' => true], 200);

                case 'invoice.payment_failed':
                    // Stripe subscription invoice payment failed
                    $subscriptionId = $dataObject['subscription'] ?? null;
                    if ($subscriptionId) {
                        $subscription = \App\Models\Subscription::where('payment_reference', $subscriptionId)
                            ->where('gateway', PaymentConstants::GATEWAY_STRIPE)
                            ->first();

                        if ($subscription && $subscription->isActive()) {
                            // Transition to GRACE on payment failure
                            $this->subscriptionService->handleRenewalFailure($subscription);
                        }
                    }

                    return response()->json(['received' => true], 200);

                case 'customer.subscription.deleted':
                    // Stripe subscription cancelled
                    $subscriptionId = $dataObject['id'] ?? null;
                    if ($subscriptionId) {
                        $subscription = \App\Models\Subscription::where('payment_reference', $subscriptionId)
                            ->where('gateway', PaymentConstants::GATEWAY_STRIPE)
                            ->first();

                        if ($subscription && ! $subscription->isCancelled()) {
                            $this->subscriptionService->cancelSubscription($subscription, 'Cancelled via Stripe webhook');
                        }
                    }

                    return response()->json(['received' => true], 200);

                default:
                    // Ignore other event types
                    return response()->json(['received' => true, 'ignored' => true], 200);
            }
        } catch (\Exception $e) {
            Log::error('Stripe subscription webhook processing failed', [
                'event_type' => $eventType,
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
        // Log webhook request for audit trail (per blueprint section 7)
        Log::info('M-Pesa subscription webhook received', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Verify M-Pesa IP whitelist (per blueprint section 7)
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

        // Log payload (sanitized - no sensitive data per blueprint section 7)
        $checkoutRequestId = $payload['Body']['stkCallback']['CheckoutRequestID'] ?? null;
        $resultCode = $payload['Body']['stkCallback']['ResultCode'] ?? null;

        Log::info('M-Pesa subscription callback received', [
            'checkout_request_id' => $checkoutRequestId,
            'result_code' => $resultCode,
        ]);

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
            try {
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
                // Queue retry job for failed webhook processing
                Log::warning('M-Pesa webhook processing failed, queuing retry', [
                    'error' => $e->getMessage(),
                    'checkout_request_id' => $checkoutRequestId,
                ]);

                ProcessWebhookRetry::dispatch(
                    PaymentConstants::GATEWAY_MPESA,
                    $callbackPayload,
                    1
                );

                // Return 200 to acknowledge receipt (we'll retry in background)
                return response()->json([
                    'ResultCode' => 0,
                    'ResultDesc' => 'Accepted (queued for retry)',
                ], 200);
            }
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
