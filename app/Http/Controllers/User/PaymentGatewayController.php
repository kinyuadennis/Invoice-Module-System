<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\PaymentGatewayService;
use App\Models\Invoice;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function __construct(
        private PaymentGatewayService $gatewayService
    ) {}

    /**
     * Initiate Stripe payment for an invoice.
     */
    public function initiateStripe(Invoice $invoice)
    {
        $companyId = CurrentCompanyService::requireId();

        // Verify invoice belongs to company
        if ($invoice->company_id !== $companyId) {
            abort(403);
        }

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This invoice has already been paid.',
            ], 422);
        }

        $result = $this->gatewayService->initiateStripePayment($invoice);

        if ($result['success']) {
            // Create pending payment record
            $payment = \App\Models\Payment::create([
                'company_id' => $invoice->company_id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->grand_total,
                'payment_method' => 'stripe',
                'gateway' => 'stripe',
                'gateway_payment_intent_id' => $result['payment_intent_id'],
                'gateway_status' => 'pending',
                'gateway_metadata' => [
                    'payment_intent_id' => $result['payment_intent_id'],
                ],
                'payment_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $result['client_secret'],
                'payment_intent_id' => $result['payment_intent_id'],
                'payment_id' => $payment->id,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to initiate payment',
        ], 500);
    }

    /**
     * Initiate M-Pesa STK Push payment for an invoice.
     */
    public function initiateMpesa(Request $request, Invoice $invoice)
    {
        $companyId = CurrentCompanyService::requireId();

        // Verify invoice belongs to company
        if ($invoice->company_id !== $companyId) {
            abort(403);
        }

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This invoice has already been paid.',
            ], 422);
        }

        $validated = $request->validate([
            'phone_number' => 'required|string|regex:/^(\+?254|0)?[17]\d{8}$/',
        ]);

        $result = $this->gatewayService->initiateMpesaPayment($invoice, $validated['phone_number']);

        if ($result['success']) {
            // Create pending payment record
            $payment = \App\Models\Payment::create([
                'company_id' => $invoice->company_id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->grand_total,
                'payment_method' => 'mpesa',
                'gateway' => 'mpesa',
                'gateway_status' => 'processing',
                'gateway_metadata' => [
                    'CheckoutRequestID' => $result['checkout_request_id'],
                    'MerchantRequestID' => $result['merchant_request_id'],
                    'phone_number' => $validated['phone_number'],
                ],
                'payment_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'checkout_request_id' => $result['checkout_request_id'],
                'payment_id' => $payment->id,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to initiate payment',
        ], 500);
    }

    /**
     * Check payment status.
     */
    public function checkStatus(Invoice $invoice)
    {
        $companyId = CurrentCompanyService::requireId();

        if ($invoice->company_id !== $companyId) {
            abort(403);
        }

        $payment = $invoice->payments()
            ->whereIn('gateway_status', ['pending', 'processing'])
            ->latest()
            ->first();

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'No pending payment found',
            ]);
        }

        return response()->json([
            'success' => true,
            'payment' => [
                'id' => $payment->id,
                'gateway' => $payment->gateway,
                'gateway_status' => $payment->gateway_status,
                'amount' => $payment->amount,
            ],
            'invoice_status' => $invoice->fresh()->status,
        ]);
    }
}
