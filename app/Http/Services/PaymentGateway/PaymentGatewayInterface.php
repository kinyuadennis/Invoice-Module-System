<?php

namespace App\Http\Services\PaymentGateway;

use App\Models\Invoice;
use App\Models\Payment;

/**
 * PaymentGatewayInterface
 *
 * Contract for payment gateway implementations.
 * All payment gateways must implement this interface.
 */
interface PaymentGatewayInterface
{
    /**
     * Initialize a payment request for an invoice.
     *
     * @param  Invoice  $invoice  Invoice to create payment for
     * @param  array  $options  Gateway-specific options (amount, currency, etc.)
     * @return array Payment request data (e.g., payment link, QR code, etc.)
     */
    public function initiatePayment(Invoice $invoice, array $options = []): array;

    /**
     * Verify a payment transaction.
     *
     * @param  string  $transactionId  Transaction ID from gateway
     * @param  array  $metadata  Additional metadata for verification
     * @return array Verification result with 'verified', 'amount', 'status', etc.
     */
    public function verifyPayment(string $transactionId, array $metadata = []): array;

    /**
     * Process a webhook notification from the gateway.
     *
     * @param  array  $payload  Webhook payload
     * @param  string  $signature  Webhook signature for validation
     * @return array Processing result
     */
    public function processWebhook(array $payload, string $signature): array;

    /**
     * Get the gateway name.
     *
     * @return string Gateway identifier (e.g., 'mpesa', 'stripe', 'paypal')
     */
    public function getGatewayName(): string;

    /**
     * Check if gateway is configured and ready.
     *
     * @return bool True if gateway is properly configured
     */
    public function isConfigured(): bool;
}
