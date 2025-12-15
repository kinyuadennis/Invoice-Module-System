<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\InvoiceSnapshot;
use Illuminate\Support\Facades\DB;

/**
 * LegacyInvoiceSnapshotService
 *
 * Handles retroactive snapshot creation for existing invoices.
 * This is scaffolding, not migration - enables safety without rewriting history.
 */
class LegacyInvoiceSnapshotService
{
    protected InvoiceSnapshotBuilder $snapshotBuilder;

    public function __construct(InvoiceSnapshotBuilder $snapshotBuilder)
    {
        $this->snapshotBuilder = $snapshotBuilder;
    }

    /**
     * Create a snapshot for an existing invoice (legacy bridge).
     *
     * This method is for one-time snapshot generation for invoices that
     * were finalized before the snapshot system existed.
     *
     * @param  Invoice  $invoice  Existing invoice to snapshot
     * @return InvoiceSnapshot Created snapshot
     *
     * @throws \Exception If snapshot already exists
     */
    public function createLegacySnapshot(Invoice $invoice): InvoiceSnapshot
    {
        // Check if snapshot already exists
        if ($invoice->snapshot) {
            throw new \DomainException(
                "Invoice #{$invoice->invoice_number} already has a snapshot. Cannot create legacy snapshot."
            );
        }

        // Only create snapshots for invoices that are finalized (sent, paid, overdue)
        if (! $invoice->isFinalized()) {
            throw new \DomainException(
                "Invoice #{$invoice->invoice_number} is not finalized. Only finalized invoices can have legacy snapshots."
            );
        }

        return DB::transaction(function () use ($invoice) {
            // Build snapshot using current stored values (not recomputed)
            $snapshotData = $this->snapshotBuilder->build($invoice);

            // Mark as legacy snapshot
            $snapshotData['metadata']['legacy_snapshot'] = true;

            // Create snapshot
            return InvoiceSnapshot::create([
                'invoice_id' => $invoice->id,
                'snapshot_taken_by' => null, // Unknown for legacy
                'snapshot_data' => $snapshotData,
                'snapshot_taken_at' => $invoice->updated_at ?? $invoice->created_at,
                'legacy_snapshot' => true,
            ]);
        });
    }

    /**
     * Create snapshots for multiple legacy invoices.
     *
     * @param  array  $invoiceIds  Array of invoice IDs to snapshot
     * @return array Results with success/failure for each invoice
     */
    public function createLegacySnapshots(array $invoiceIds): array
    {
        $results = [];

        foreach ($invoiceIds as $invoiceId) {
            try {
                $invoice = Invoice::findOrFail($invoiceId);
                $this->createLegacySnapshot($invoice);
                $results[$invoiceId] = [
                    'success' => true,
                    'message' => "Snapshot created for invoice #{$invoice->invoice_number}",
                ];
            } catch (\Exception $e) {
                $results[$invoiceId] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
