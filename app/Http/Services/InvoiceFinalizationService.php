<?php

namespace App\Http\Services;

use App\Http\Services\InvoiceAuditService;
use App\Models\Invoice;
use App\Models\InvoiceSnapshot;
use Illuminate\Support\Facades\DB;

/**
 * InvoiceFinalizationService
 *
 * Handles atomic finalization of invoices with snapshot creation.
 * This ensures there is never a finalized invoice without a snapshot.
 */
class InvoiceFinalizationService
{
    protected InvoiceSnapshotBuilder $snapshotBuilder;

    protected InvoiceCalculationService $calculationService;

    protected InvoiceAuditService $auditService;

    public function __construct(
        InvoiceSnapshotBuilder $snapshotBuilder,
        InvoiceCalculationService $calculationService,
        InvoiceAuditService $auditService
    ) {
        $this->snapshotBuilder = $snapshotBuilder;
        $this->calculationService = $calculationService;
        $this->auditService = $auditService;
    }

    /**
     * Finalize an invoice and create its snapshot atomically.
     *
     * Rules:
     * - If snapshot creation fails → finalization fails
     * - There must never be a finalized invoice without a snapshot (except legacy)
     * - This is a hard invariant
     *
     * @param  Invoice  $invoice  Invoice to finalize
     * @return Invoice Finalized invoice
     *
     * @throws \DomainException If finalization fails
     * @throws \Exception If snapshot creation fails
     */
    public function finalizeInvoice(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            // Step 1: Finalize the invoice (changes status to 'finalized')
            $invoice->finalize();

            // Step 2: Build snapshot payload
            $snapshotData = $this->snapshotBuilder->build($invoice);

            // Step 3: Persist snapshot
            InvoiceSnapshot::create([
                'invoice_id' => $invoice->id,
                'snapshot_taken_by' => auth()->id(),
                'snapshot_data' => $snapshotData,
                'snapshot_taken_at' => now(),
                'legacy_snapshot' => false,
            ]);

            // Step 4: Log finalization
            $this->auditService->logFinalize($invoice->fresh());

            // Step 5: Return finalized invoice
            return $invoice->fresh();
        });
    }
}
