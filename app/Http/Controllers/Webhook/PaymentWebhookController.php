<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Http\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private PaymentGatewayService $gatewayService
    ) {}

    /**
     * Handle Stripe webhooks.
     */
    public function stripe(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('Stripe-Signature');

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
                // In development, we might skip verification
                if (config('app.env') === 'production') {
                    return response()->json(['error' => 'Invalid signature'], 400);
                }
            }
        }

        try {
            $payment = $this->gatewayService->processStripeWebhook($payload);

            if ($payment) {
                return response()->json(['received' => true], 200);
            }

            return response()->json(['received' => true, 'ignored' => true], 200);
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle M-Pesa callbacks.
     */
    public function mpesa(Request $request)
    {
        $payload = $request->all();

        Log::info('M-Pesa callback received', ['payload' => $payload]);

        try {
            $payment = $this->gatewayService->processMpesaCallback($payload);

            // M-Pesa expects a specific response format
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Accepted',
            ], 200);
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
}
