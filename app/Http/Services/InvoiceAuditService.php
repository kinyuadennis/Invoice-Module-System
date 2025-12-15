<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\InvoiceAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * InvoiceAuditService
 *
 * Centralized service for logging all invoice-related actions.
 * This service ensures consistent audit logging across the application.
 *
 * Rules:
 * - Never modifies invoices
 * - Logs are immutable (created, never updated)
 * - Always includes context (user, IP, timestamp)
 * - Hooks into all critical invoice operations
 */
class InvoiceAuditService
{
    /**
     * Log an invoice action.
     *
     * @param  Invoice  $invoice  Invoice being acted upon
     * @param  string  $actionType  Action type (use InvoiceAuditLog::ACTION_* constants)
     * @param  array  $options  Additional options:
     *                          - old_data: Previous state (for updates)
     *                          - new_data: New state (for updates)
     *                          - metadata: Additional context
     *                          - source: Source of action (ui, api, integration, job)
     *                          - request: Request object (for IP and user agent)
     * @return InvoiceAuditLog Created audit log entry
     */
    public function log(Invoice $invoice, string $actionType, array $options = []): InvoiceAuditLog
    {
        $request = $options['request'] ?? null;
        $user = Auth::user();

        // Extract IP address
        $ipAddress = null;
        if ($request instanceof Request) {
            $ipAddress = $request->ip();
        } elseif (isset($options['ip_address'])) {
            $ipAddress = $options['ip_address'];
        }

        // Extract user agent
        $userAgent = null;
        if ($request instanceof Request) {
            $userAgent = $request->userAgent();
        } elseif (isset($options['user_agent'])) {
            $userAgent = $options['user_agent'];
        }

        // Determine source
        $source = $options['source'] ?? InvoiceAuditLog::SOURCE_UI;
        if ($request instanceof Request && $request->is('api/*')) {
            $source = InvoiceAuditLog::SOURCE_API;
        }

        return InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user?->id,
            'action_type' => $actionType,
            'old_data' => $options['old_data'] ?? null,
            'new_data' => $options['new_data'] ?? null,
            'metadata' => $options['metadata'] ?? [],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'source' => $source,
        ]);
    }

    /**
     * Log invoice creation.
     */
    public function logCreate(Invoice $invoice, ?Request $request = null): InvoiceAuditLog
    {
        return $this->log($invoice, InvoiceAuditLog::ACTION_CREATE, [
            'new_data' => $this->extractInvoiceData($invoice),
            'request' => $request,
        ]);
    }

    /**
     * Log invoice update.
     */
    public function logUpdate(Invoice $invoice, array $oldData, ?Request $request = null): InvoiceAuditLog
    {
        return $this->log($invoice, InvoiceAuditLog::ACTION_UPDATE, [
            'old_data' => $oldData,
            'new_data' => $this->extractInvoiceData($invoice),
            'request' => $request,
        ]);
    }

    /**
     * Log invoice finalization.
     */
    public function logFinalize(Invoice $invoice, ?Request $request = null): InvoiceAuditLog
    {
        return $this->log($invoice, InvoiceAuditLog::ACTION_FINALIZE, [
            'new_data' => $this->extractInvoiceData($invoice),
            'metadata' => [
                'invoice_number' => $invoice->invoice_number,
                'grand_total' => $invoice->grand_total,
                'has_snapshot' => $invoice->snapshot !== null,
            ],
            'request' => $request,
        ]);
    }

    /**
     * Log invoice send (email/WhatsApp).
     */
    public function logSend(Invoice $invoice, string $method, ?Request $request = null, array $additionalMetadata = []): InvoiceAuditLog
    {
        $metadata = array_merge([
            'method' => $method, // email, whatsapp
            'invoice_number' => $invoice->invoice_number,
        ], $additionalMetadata);

        $options = [
            'metadata' => $metadata,
            'request' => $request,
        ];

        if (isset($additionalMetadata['source'])) {
            $options['source'] = $additionalMetadata['source'];
        }

        return $this->log($invoice, InvoiceAuditLog::ACTION_SEND, $options);
    }

    /**
     * Log payment received.
     */
    public function logPayment(Invoice $invoice, array $paymentData, ?Request $request = null): InvoiceAuditLog
    {
        return $this->log($invoice, InvoiceAuditLog::ACTION_PAY, [
            'metadata' => [
                'payment_amount' => $paymentData['amount'] ?? null,
                'payment_method' => $paymentData['payment_method'] ?? null,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'invoice_number' => $invoice->invoice_number,
            ],
            'request' => $request,
            'source' => $paymentData['source'] ?? InvoiceAuditLog::SOURCE_INTEGRATION,
        ]);
    }

    /**
     * Log PDF generation.
     */
    public function logPdfGenerate(Invoice $invoice, ?Request $request = null): InvoiceAuditLog
    {
        return $this->log($invoice, InvoiceAuditLog::ACTION_PDF_GENERATE, [
            'metadata' => [
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'used_snapshot' => $invoice->isFinalized() && $invoice->snapshot !== null,
            ],
            'request' => $request,
        ]);
    }

    /**
     * Log API access.
     */
    public function logApiAccess(Invoice $invoice, string $endpoint, ?Request $request = null): InvoiceAuditLog
    {
        return $this->log($invoice, InvoiceAuditLog::ACTION_API_ACCESS, [
            'metadata' => [
                'endpoint' => $endpoint,
                'invoice_number' => $invoice->invoice_number,
            ],
            'request' => $request,
            'source' => InvoiceAuditLog::SOURCE_API,
        ]);
    }

    /**
     * Log ETIMS export.
     */
    public function logEtimsExport(Invoice $invoice, string $format, ?Request $request = null): InvoiceAuditLog
    {
        return $this->log($invoice, InvoiceAuditLog::ACTION_ETIMS_EXPORT, [
            'metadata' => [
                'format' => $format, // json, xml
                'invoice_number' => $invoice->invoice_number,
            ],
            'request' => $request,
        ]);
    }

    /**
     * Log accounting software export.
     */
    public function logAccountingExport(Invoice $invoice, string $software, ?Request $request = null): InvoiceAuditLog
    {
        return $this->log($invoice, InvoiceAuditLog::ACTION_ACCOUNTING_EXPORT, [
            'metadata' => [
                'software' => $software, // quickbooks, xero, sage
                'invoice_number' => $invoice->invoice_number,
            ],
            'request' => $request,
        ]);
    }

    /**
     * Extract relevant invoice data for logging.
     * Only includes non-sensitive, audit-relevant fields.
     *
     * @param  Invoice  $invoice  Invoice to extract data from
     * @return array Invoice data snapshot
     */
    protected function extractInvoiceData(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'client_id' => $invoice->client_id,
            'issue_date' => $invoice->issue_date?->toDateString(),
            'due_date' => $invoice->due_date?->toDateString(),
            'subtotal' => $invoice->subtotal,
            'vat_amount' => $invoice->vat_amount,
            'grand_total' => $invoice->grand_total,
            'currency' => $invoice->currency,
        ];
    }
}
