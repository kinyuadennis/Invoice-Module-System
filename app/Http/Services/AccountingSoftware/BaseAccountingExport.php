<?php

namespace App\Http\Services\AccountingSoftware;

use App\Http\Services\InvoiceSnapshotFormatter;
use App\Models\Invoice;

/**
 * BaseAccountingExport
 *
 * Base class for accounting software exports.
 * Provides common functionality for all accounting integrations.
 */
abstract class BaseAccountingExport implements AccountingSoftwareInterface
{
    protected InvoiceSnapshotFormatter $snapshotFormatter;

    public function __construct(InvoiceSnapshotFormatter $snapshotFormatter)
    {
        $this->snapshotFormatter = $snapshotFormatter;
    }

    /**
     * Export invoice to accounting software format.
     * Uses snapshot data for finalized invoices.
     *
     * @param  Invoice  $invoice  Invoice to export
     * @param  array  $options  Export options
     * @return array Export result
     */
    public function exportInvoice(Invoice $invoice, array $options = []): array
    {
        // For finalized invoices, use snapshot data (read-only)
        if ($invoice->isFinalized() && $invoice->snapshot) {
            $formattedData = $this->snapshotFormatter->formatForDisplay($invoice->snapshot);
        } else {
            // For drafts, use live data
            $formattedData = $this->formatDraftInvoice($invoice);
        }

        return $this->convertToAccountingFormat($formattedData, $options);
    }

    /**
     * Format draft invoice data.
     *
     * @param  Invoice  $invoice  Draft invoice
     * @return array Formatted data
     */
    protected function formatDraftInvoice(Invoice $invoice): array
    {
        $invoice->loadMissing(['company', 'client', 'invoiceItems']);

        return [
            'invoice_number' => $invoice->invoice_number,
            'issue_date' => $invoice->issue_date?->format('Y-m-d'),
            'due_date' => $invoice->due_date?->format('Y-m-d'),
            'company' => [
                'name' => $invoice->company->name,
                'kra_pin' => $invoice->company->kra_pin,
            ],
            'client' => $invoice->client ? [
                'name' => $invoice->client->name,
                'kra_pin' => $invoice->client->kra_pin,
            ] : null,
            'items' => $invoice->invoiceItems->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total_price,
                ];
            })->toArray(),
            'totals' => [
                'subtotal' => $invoice->subtotal,
                'vat_amount' => $invoice->vat_amount,
                'grand_total' => $invoice->grand_total,
            ],
        ];
    }

    /**
     * Convert formatted invoice data to accounting software format.
     * Must be implemented by each integration.
     *
     * @param  array  $formattedData  Formatted invoice data
     * @param  array  $options  Export options
     * @return array Export result
     */
    abstract protected function convertToAccountingFormat(array $formattedData, array $options): array;
}
