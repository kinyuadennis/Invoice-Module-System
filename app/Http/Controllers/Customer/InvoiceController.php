<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Services\InvoiceAccessTokenService;
use App\Http\Services\PaymentGatewayService;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceAccessTokenService $tokenService,
        private PaymentGatewayService $paymentGatewayService,
        private \App\Services\InvoiceSnapshotService $snapshotService,
        private \App\Services\PdfInvoiceRenderer $pdfRenderer
    ) {}

    /**
     * Show invoice using access token.
     */
    public function show(string $token)
    {
        $accessToken = $this->tokenService->validateToken($token);

        if (! $accessToken || ! $accessToken->isValid()) {
            return view('customer.invalid-token', [
                'message' => 'This invoice link is invalid or has expired.',
            ]);
        }

        $invoice = $accessToken->invoice;
        $invoice->load(['company', 'client', 'items', 'payments']);

        // Get payment summary
        $paymentService = app(\App\Http\Services\PaymentService::class);
        $paymentSummary = $paymentService->getPaymentSummary($invoice);

        return view('customer.invoices.show', [
            'invoice' => $invoice,
            'accessToken' => $accessToken,
            'paymentSummary' => $paymentSummary,
        ]);
    }

    /**
     * Download invoice PDF using access token.
     */
    public function downloadPdf(string $token)
    {
        $accessToken = $this->tokenService->validateToken($token);

        if (! $accessToken || ! $accessToken->isValid()) {
            abort(403, 'Invalid or expired access token');
        }

        $invoice = $accessToken->invoice;

        // Find existing snapshot or create one
        $snapshot = $this->snapshotService->findLatestSnapshot($invoice);

        if (! $snapshot) {
            // If no snapshot exists, create one based on current status
            $snapshot = $this->snapshotService->createSnapshot($invoice, $invoice->status);
        }

        // Render PDF from snapshot
        try {
            $pdfContent = $this->pdfRenderer->render($snapshot);
            
            // Generate filename
            $filename = 'invoice-'.($snapshot->snapshot_data['invoice_details']['full_number'] ?? $invoice->invoice_number ?? $invoice->id).'.pdf';

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');

        } catch (\Exception $e) {
            \Log::error('Customer PDF generation error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Failed to generate PDF');
        }
    }

    /**
     * Initiate Stripe payment from customer portal.
     */
    public function payStripe(Request $request, string $token)
    {
        $accessToken = $this->tokenService->validateToken($token);

        if (! $accessToken || ! $accessToken->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired access token',
            ], 403);
        }

        $invoice = $accessToken->invoice;

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This invoice has already been paid.',
            ], 422);
        }

        $result = $this->paymentGatewayService->initiateStripePayment($invoice);

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
                    'access_token_id' => $accessToken->id,
                ],
                'payment_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $result['client_secret'],
                'payment_intent_id' => $result['payment_intent_id'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to initiate payment',
        ], 500);
    }

    /**
     * Initiate M-Pesa payment from customer portal.
     */
    public function payMpesa(Request $request, string $token)
    {
        $accessToken = $this->tokenService->validateToken($token);

        if (! $accessToken || ! $accessToken->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired access token',
            ], 403);
        }

        $invoice = $accessToken->invoice;

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

        $result = $this->paymentGatewayService->initiateMpesaPayment($invoice, $validated['phone_number']);

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
                    'access_token_id' => $accessToken->id,
                ],
                'payment_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'checkout_request_id' => $result['checkout_request_id'],
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
    public function paymentStatus(string $token)
    {
        $accessToken = $this->tokenService->validateToken($token);

        if (! $accessToken || ! $accessToken->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired access token',
            ], 403);
        }

        $invoice = $accessToken->invoice->fresh();
        $payment = $invoice->payments()
            ->whereIn('gateway_status', ['pending', 'processing'])
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'payment' => $payment ? [
                'id' => $payment->id,
                'gateway' => $payment->gateway,
                'gateway_status' => $payment->gateway_status,
                'amount' => $payment->amount,
            ] : null,
            'invoice_status' => $invoice->status,
        ]);
    }
}
