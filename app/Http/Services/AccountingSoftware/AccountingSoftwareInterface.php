<?php

namespace App\Http\Services\AccountingSoftware;

use App\Models\Invoice;

/**
 * AccountingSoftwareInterface
 *
 * Contract for accounting software integrations.
 * All integrations must implement this interface.
 *
 * Rules:
 * - Export only (read-only operations)
 * - Use snapshot data for finalized invoices
 * - Never modify invoices
 */
interface AccountingSoftwareInterface
{
    /**
     * Export invoice to accounting software format.
     *
     * @param  Invoice  $invoice  Invoice to export
     * @param  array  $options  Export options
     * @return array Export result with file/data
     */
    public function exportInvoice(Invoice $invoice, array $options = []): array;

    /**
     * Get supported export formats.
     *
     * @return array List of supported formats (e.g., ['csv', 'json', 'xml'])
     */
    public function getSupportedFormats(): array;

    /**
     * Get the software name.
     *
     * @return string Software identifier (e.g., 'quickbooks', 'xero', 'sage')
     */
    public function getSoftwareName(): string;

    /**
     * Check if integration is configured.
     *
     * @return bool True if properly configured
     */
    public function isConfigured(): bool;
}
