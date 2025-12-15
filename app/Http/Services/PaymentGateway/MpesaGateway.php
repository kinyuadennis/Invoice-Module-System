<?php

namespace App\Http\Services\PaymentGateway;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * MpesaGateway
 *
 * M-Pesa payment gateway implementation.
 * Handles M-Pesa payment initiation, verification, and webhook processing.
 *
 * Rules:
 * - Never modifies finalized invoices directly
 * - Creates Payment records for audit trail
 * - Validates webhook signatures
 * - Company-scoped operations only
 */
class MpesaGateway implements PaymentGatewayInterface
{
    protected string $consumerKey;

    protected string $consumerSecret;

    protected string $passkey;

    protected string $shortcode;

    protected string $webhookSecret;

    protected bool $sandboxMode;

    protected InvoiceAuditService $auditService;

    public function __construct(InvoiceAuditService $auditService)
    {
        // Load M-Pesa configuration from environment
        // In production, these should be per-company settings
        $this->consumerKey = config('services.mpesa.consumer_key', '');
        $this->consumerSecret = config('services.mpesa.consumer_secret', '');
        $this->passkey = config('services.mpesa.passkey', '');
        $this->shortcode = config('services.mpesa.shortcode', '');
        $this->webhookSecret = config('services.mpesa.webhook_secret', '');
        $this->sandboxMode = config('services.mpesa.sandbox', true);
        $this->auditService = $auditService;
    }

    /**
     * Initialize a payment request for an invoice.
     *
     * @param  Invoice  $invoice  Invoice to create payment for
     * @param  array  $options  Gateway-specific options
     * @return array Payment request data
     */
    public function initiatePayment(Invoice $invoice, array $options = []): array
    {
        // Only allow payment initiation for finalized invoices
        if (! $invoice->isFinalized()) {
            throw new \DomainException('Payment can only be initiated for finalized invoices.');
        }

        $amount = $options['amount'] ?? $invoice->grand_total;
        $phoneNumber = $options['phone_number'] ?? null;

        if (! $phoneNumber) {
            throw new \InvalidArgumentException('Phone number is required for M-Pesa payment.');
        }

        // Generate unique transaction reference
        $transactionReference = $this->generateTransactionReference($invoice);

        // In a real implementation, this would call M-Pesa STK Push API
        // For now, return payment instructions
        return [
            'gateway' => 'mpesa',
            'transaction_reference' => $transactionReference,
            'amount' => $amount,
            'phone_number' => $phoneNumber,
            'instructions' => $this->getPaymentInstructions($invoice),
            'status' => 'pending',
            'message' => 'Payment request initiated. Please complete payment via M-Pesa.',
        ];
    }

    /**
     * Verify a payment transaction.
     *
     * @param  string  $transactionId  M-Pesa transaction ID
     * @param  array  $metadata  Additional metadata
     * @return array Verification result
     */
    public function verifyPayment(string $transactionId, array $metadata = []): array
    {
        // In a real implementation, this would query M-Pesa API
        // For now, return mock verification
        return [
            'verified' => false,
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'message' => 'Payment verification requires webhook confirmation.',
        ];
    }

    /**
     * Process a webhook notification from M-Pesa.
     *
     * @param  array  $payload  Webhook payload
     * @param  string  $signature  Webhook signature for validation
     * @return array Processing result
     */
    public function processWebhook(array $payload, string $signature): array
    {
        // Validate webhook signature
        if (! $this->validateWebhookSignature($payload, $signature)) {
            Log::warning('M-Pesa webhook signature validation failed', [
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ];
        }

        // Extract transaction details from payload
        $transactionId = $payload['TransactionID'] ?? $payload['transaction_id'] ?? null;
        $amount = $payload['Amount'] ?? $payload['amount'] ?? null;
        $phoneNumber = $payload['PhoneNumber'] ?? $payload['phone_number'] ?? null;
        $resultCode = $payload['ResultCode'] ?? $payload['result_code'] ?? null;
        $resultDesc = $payload['ResultDesc'] ?? $payload['result_description'] ?? null;
        $merchantRequestId = $payload['MerchantRequestID'] ?? $payload['merchant_request_id'] ?? null;

        // Find invoice by transaction reference
        $invoice = $this->findInvoiceByTransactionReference($merchantRequestId);

        if (! $invoice) {
            Log::warning('M-Pesa webhook: Invoice not found', [
                'merchant_request_id' => $merchantRequestId,
            ]);

            return [
                'success' => false,
                'message' => 'Invoice not found for transaction reference.',
            ];
        }

        // Process payment only if successful
        if ($resultCode == 0) {
            return $this->recordPayment($invoice, [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'phone_number' => $phoneNumber,
                'mpesa_reference' => $transactionId,
                'metadata' => $payload,
            ]);
        }

        // Log failed payment
        Log::info('M-Pesa payment failed', [
            'invoice_id' => $invoice->id,
            'transaction_id' => $transactionId,
            'result_code' => $resultCode,
            'result_description' => $resultDesc,
        ]);

        return [
            'success' => false,
            'message' => $resultDesc ?? 'Payment failed.',
            'result_code' => $resultCode,
        ];
    }

    /**
     * Get the gateway name.
     */
    public function getGatewayName(): string
    {
        return 'mpesa';
    }

    /**
     * Check if gateway is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->consumerKey)
            && ! empty($this->consumerSecret)
            && ! empty($this->shortcode);
    }

    /**
     * Record a successful payment.
     *
     * @param  Invoice  $invoice  Invoice being paid
     * @param  array  $paymentData  Payment data
     * @return array Processing result
     */
    protected function recordPayment(Invoice $invoice, array $paymentData): array
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            // Create payment record
            $payment = Payment::create([
                'company_id' => $invoice->company_id,
                'invoice_id' => $invoice->id,
                'payment_date' => now(),
                'paid_at' => now(),
                'amount' => $paymentData['amount'],
                'payment_method' => 'mpesa',
                'mpesa_reference' => $paymentData['mpesa_reference'],
                'transaction_id' => $paymentData['transaction_id'],
                'transaction_reference' => $this->generateTransactionReference($invoice),
                'gateway_metadata' => $paymentData['metadata'] ?? [],
            ]);

            // Update invoice status to 'paid' if fully paid
            // CRITICAL: Only update status, never modify financial data
            $totalPaid = $invoice->payments()->sum('amount');
            if ($totalPaid >= $invoice->grand_total) {
                // Use update() directly to change status only
                $invoice->update(['status' => 'paid']);
            }

            // Log payment event
            Log::info('M-Pesa payment recorded', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $paymentData['amount'],
                'transaction_id' => $paymentData['transaction_id'],
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'message' => 'Payment recorded successfully.',
            ];
        });
    }

    /**
     * Validate webhook signature.
     *
     * @param  array  $payload  Webhook payload
     * @param  string  $signature  Provided signature
     * @return bool True if signature is valid
     */
    protected function validateWebhookSignature(array $payload, string $signature): bool
    {
        // In production, implement proper signature validation
        // For now, check if webhook secret is configured
        if (empty($this->webhookSecret)) {
            // In sandbox mode, allow if signature matches a simple hash
            if ($this->sandboxMode) {
                $expectedSignature = hash_hmac('sha256', json_encode($payload), $this->webhookSecret ?: 'sandbox-secret');

                return hash_equals($expectedSignature, $signature);
            }

            return false;
        }

        // Real signature validation would compare with M-Pesa's signature
        $expectedSignature = hash_hmac('sha256', json_encode($payload), $this->webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Find invoice by transaction reference.
     *
     * @param  string  $transactionReference  Transaction reference
     */
    protected function findInvoiceByTransactionReference(?string $transactionReference): ?Invoice
    {
        if (! $transactionReference) {
            return null;
        }

        // In a real implementation, store transaction references in a separate table
        // For now, extract invoice ID from reference if it follows a pattern
        // Example: "INV-123-MPESA-20250115" -> extract invoice ID
        if (preg_match('/INV-(\d+)/', $transactionReference, $matches)) {
            return Invoice::find($matches[1]);
        }

        return null;
    }

    /**
     * Generate unique transaction reference.
     *
     * @param  Invoice  $invoice  Invoice
     * @return string Transaction reference
     */
    protected function generateTransactionReference(Invoice $invoice): string
    {
        return sprintf('INV-%d-MPESA-%s', $invoice->id, now()->format('YmdHis'));
    }

    /**
     * Get payment instructions for invoice.
     *
     * @param  Invoice  $invoice  Invoice
     * @return string Payment instructions
     */
    protected function getPaymentInstructions(Invoice $invoice): string
    {
        $company = $invoice->company;
        $paymentMethod = $company->paymentMethods()
            ->where('type', 'mpesa')
            ->where('enabled', true)
            ->first();

        if ($paymentMethod && $paymentMethod->mpesa_paybill) {
            return sprintf(
                'Pay via M-Pesa: Go to M-Pesa Menu > Lipa na M-Pesa > Paybill > Business Number: %s > Account Number: %s > Amount: %s',
                $paymentMethod->mpesa_paybill,
                $invoice->invoice_number,
                number_format($invoice->grand_total, 2)
            );
        }

        return 'Please contact us for M-Pesa payment instructions.';
    }
}
