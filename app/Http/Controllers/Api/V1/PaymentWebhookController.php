<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Services\PaymentGateway\MpesaGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * PaymentWebhookController
 *
 * Handles webhook notifications from payment gateways.
 * All webhooks are validated before processing.
 */
class PaymentWebhookController extends Controller
{
    /**
     * Handle M-Pesa webhook.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mpesa(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Mpesa-Signature') ?? $request->header('Signature') ?? '';

        $gateway = new MpesaGateway;

        try {
            $result = $gateway->processWebhook($payload, $signature);

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Webhook processed successfully.',
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => $result['message'] ?? 'Webhook processing failed.',
            ], 400);
        } catch (\Exception $e) {
            Log::error('M-Pesa webhook processing error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook processing error.',
            ], 500);
        }
    }
}
