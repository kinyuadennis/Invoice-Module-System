<?php

namespace App\Http\Services;

use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoicePrefixService;
use Illuminate\Http\Request;

class CreditNoteService
{
    protected InvoicePrefixService $prefixService;

    public function __construct(InvoicePrefixService $prefixService)
    {
        $this->prefixService = $prefixService;
    }

    /**
     * Create a new credit note from an invoice
     */
    public function createCreditNoteFromInvoice(Invoice $invoice, Request $request): CreditNote
    {
        $user = $request->user();
        $companyId = $invoice->company_id;

        // Validate invoice can have credit note
        if ($invoice->status === 'cancelled') {
            throw new \RuntimeException('Cannot create credit note for cancelled invoice.');
        }

        $data = $request->only([
            'reason',
            'reason_details',
            'notes',
            'terms_and_conditions',
        ]);

        $data['company_id'] = $companyId;
        $data['user_id'] = $user->id;
        $data['invoice_id'] = $invoice->id;
        $data['client_id'] = $invoice->client_id;
        $data['template_id'] = $invoice->template_id;
        $data['status'] = 'draft';
        $data['issue_date'] = now()->toDateString();
        $data['reason'] = $data['reason'] ?? 'other';

        // Get company for numbering
        $company = Company::findOrFail($companyId);

        // Generate credit note number
        $prefix = $this->prefixService->getActivePrefix($company);
        $serialNumber = $this->prefixService->generateNextSerialNumber($company, $prefix);
        $fullNumber = 'CN-'.$this->prefixService->generateFullNumber($company, $prefix, $serialNumber);

        $data['prefix_used'] = $prefix->prefix;
        $data['serial_number'] = $serialNumber;
        $data['full_number'] = $fullNumber;
        $data['credit_note_reference'] = $fullNumber;

        // Get items to credit (from request or all invoice items)
        $itemsToCredit = [];
        $requestItems = $request->input('items', []);

        // Filter items that are included (checked)
        foreach ($requestItems as $itemData) {
            if (isset($itemData['include']) && $itemData['include'] == '1') {
                $itemsToCredit[] = $itemData;
            }
        }

        // If no items specified, credit all items
        if (empty($itemsToCredit)) {
            foreach ($invoice->invoiceItems as $invoiceItem) {
                $itemsToCredit[] = [
                    'invoice_item_id' => $invoiceItem->id,
                    'quantity' => $invoiceItem->quantity,
                    'credit_reason' => 'other',
                ];
            }
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($itemsToCredit as $itemData) {
            $invoiceItem = InvoiceItem::find($itemData['invoice_item_id'] ?? null);
            if ($invoiceItem && $invoiceItem->invoice_id === $invoice->id) {
                $quantity = $itemData['quantity'] ?? $invoiceItem->quantity;
                $itemTotal = $invoiceItem->unit_price * $quantity;
                $subtotal += $itemTotal;
            }
        }

        $vatAmount = $subtotal * 0.16; // 16% VAT
        $totalCredit = $subtotal + $vatAmount;
        $remainingCredit = $totalCredit;

        $data['subtotal'] = $subtotal;
        $data['vat_amount'] = $vatAmount;
        $data['total_credit'] = $totalCredit;
        $data['remaining_credit'] = $remainingCredit;

        // Set eTIMS reversal reference if original invoice has eTIMS number
        if ($invoice->etims_control_number) {
            $data['etims_reversal_reference'] = $invoice->etims_control_number;
            $data['etims_status'] = 'pending';
        }

        // Create credit note
        $creditNote = CreditNote::create($data);

        // Create credit note items
        foreach ($itemsToCredit as $itemData) {
            $invoiceItem = InvoiceItem::find($itemData['invoice_item_id'] ?? null);
            if ($invoiceItem && $invoiceItem->invoice_id === $invoice->id) {
                $quantity = $itemData['quantity'] ?? $invoiceItem->quantity;
                $itemTotal = $invoiceItem->unit_price * $quantity;

                $creditNote->items()->create([
                    'company_id' => $companyId,
                    'invoice_item_id' => $invoiceItem->id,
                    'item_id' => $invoiceItem->item_id,
                    'description' => $invoiceItem->description,
                    'quantity' => $quantity,
                    'unit_price' => $invoiceItem->unit_price,
                    'vat_included' => $invoiceItem->vat_included,
                    'vat_rate' => $invoiceItem->vat_rate,
                    'total_price' => $itemTotal,
                    'credit_reason' => $itemData['credit_reason'] ?? 'other',
                    'credit_reason_details' => $itemData['credit_reason_details'] ?? null,
                ]);
            }
        }

        // Reload items and update totals
        $creditNote->load('items');
        $this->updateTotals($creditNote);

        return $creditNote;
    }

    /**
     * Update credit note totals
     */
    public function updateTotals(CreditNote $creditNote): void
    {
        $subtotal = $creditNote->items->sum(function ($item) {
            return $item->total_price;
        });

        $vatAmount = $subtotal * 0.16; // 16% VAT
        $totalCredit = $subtotal + $vatAmount;
        $remainingCredit = $totalCredit - ($creditNote->applied_amount ?? 0);

        $creditNote->subtotal = $subtotal;
        $creditNote->vat_amount = $vatAmount;
        $creditNote->total_credit = $totalCredit;
        $creditNote->remaining_credit = max(0, $remainingCredit);
        $creditNote->save();
    }

    /**
     * Apply credit note to an invoice
     */
    public function applyToInvoice(CreditNote $creditNote, Invoice $invoice): void
    {
        if (! $creditNote->canApplyToInvoice()) {
            throw new \RuntimeException('Credit note cannot be applied. It must be issued and have remaining credit.');
        }

        if ($creditNote->invoice_id === $invoice->id) {
            throw new \RuntimeException('Cannot apply credit note to the same invoice it was created from.');
        }

        if ($creditNote->client_id !== $invoice->client_id) {
            throw new \RuntimeException('Credit note and invoice must belong to the same client.');
        }

        $amountToApply = min($creditNote->remaining_credit, $invoice->grand_total - $invoice->payments->sum('amount'));

        if ($amountToApply <= 0) {
            throw new \RuntimeException('No amount available to apply. Invoice may already be fully paid.');
        }

        // Update credit note
        $creditNote->applied_to_invoice_id = $invoice->id;
        $creditNote->applied_amount = ($creditNote->applied_amount ?? 0) + $amountToApply;
        $creditNote->remaining_credit = $creditNote->remaining_credit - $amountToApply;
        $creditNote->applied_date = now()->toDateString();

        if ($creditNote->remaining_credit <= 0) {
            $creditNote->status = 'applied';
        }

        $creditNote->save();

        // Record payment on invoice (credit note application)
        $invoice->payments()->create([
            'company_id' => $invoice->company_id,
            'amount' => $amountToApply,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'credit_note',
            'reference' => $creditNote->credit_note_number ?? $creditNote->full_number,
        ]);

        // Refresh invoice to get latest payments (like PaymentService does)
        $invoice->refresh();
        $invoice->load('payments');

        // Update invoice status if fully paid
        $totalPaid = $invoice->payments->sum('amount');
        if ($totalPaid >= $invoice->grand_total) {
            $invoice->update(['status' => 'paid']);
        }
    }

    /**
     * Submit credit note to eTIMS for reversal
     */
    public function submitToEtims(CreditNote $creditNote): bool
    {
        if ($creditNote->etims_status === 'approved') {
            return true; // Already submitted and approved
        }

        // TODO: Implement actual eTIMS API submission
        // For now, simulate submission
        $creditNote->etims_control_number = 'CN-'.strtoupper(uniqid());
        $creditNote->etims_status = 'submitted';
        $creditNote->etims_submitted_at = now();
        $creditNote->etims_metadata = [
            'reversal_type' => 'credit_note',
            'original_invoice' => $creditNote->invoice->etims_control_number,
            'submitted_at' => now()->toIso8601String(),
        ];
        $creditNote->save();

        return true;
    }

    /**
     * Format credit note for list display
     */
    public function formatCreditNoteForList(CreditNote $creditNote): array
    {
        return [
            'id' => $creditNote->id,
            'credit_note_number' => $creditNote->full_number ?? $creditNote->credit_note_reference ?? "CN-{$creditNote->id}",
            'status' => $creditNote->status,
            'total_credit' => (float) $creditNote->total_credit,
            'remaining_credit' => (float) $creditNote->remaining_credit,
            'applied_amount' => (float) $creditNote->applied_amount,
            'issue_date' => $creditNote->issue_date->toDateString(),
            'reason' => $creditNote->reason,
            'invoice' => [
                'id' => $creditNote->invoice->id,
                'invoice_number' => $creditNote->invoice->full_number ?? $creditNote->invoice->invoice_reference,
            ],
            'client' => [
                'id' => $creditNote->client?->id,
                'name' => $creditNote->client?->name ?? 'N/A',
            ],
            'etims_status' => $creditNote->etims_status,
        ];
    }

    /**
     * Format credit note with full details for show view
     */
    public function formatCreditNoteForShow(CreditNote $creditNote): array
    {
        $data = $this->formatCreditNoteForList($creditNote);

        $data['notes'] = $creditNote->notes;
        $data['reason_details'] = $creditNote->reason_details;
        $data['subtotal'] = (float) $creditNote->subtotal;
        $data['vat_amount'] = (float) $creditNote->vat_amount;
        $data['platform_fee'] = (float) $creditNote->platform_fee;
        $data['applied_date'] = $creditNote->applied_date?->toDateString();
        $data['etims_control_number'] = $creditNote->etims_control_number;
        $data['etims_qr_code'] = $creditNote->etims_qr_code;
        $data['etims_reversal_reference'] = $creditNote->etims_reversal_reference;
        $data['items'] = $creditNote->items->map(function ($item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
                'credit_reason' => $item->credit_reason,
                'credit_reason_details' => $item->credit_reason_details,
            ];
        });
        $data['applied_to_invoice'] = $creditNote->appliedToInvoice ? [
            'id' => $creditNote->appliedToInvoice->id,
            'invoice_number' => $creditNote->appliedToInvoice->full_number ?? $creditNote->appliedToInvoice->invoice_reference,
        ] : null;
        $data['is_applied'] = $creditNote->isApplied();
        $data['has_remaining_credit'] = $creditNote->hasRemainingCredit();
        $data['can_apply_to_invoice'] = $creditNote->canApplyToInvoice();

        return $data;
    }

    /**
     * Format credit note for edit view
     */
    public function formatCreditNoteForEdit(CreditNote $creditNote): array
    {
        return $this->formatCreditNoteForShow($creditNote);
    }

    /**
     * Get credit note statistics scoped by company
     */
    public function getCreditNoteStats(int $companyId): array
    {
        $query = CreditNote::where('company_id', $companyId);

        return [
            'total' => (clone $query)->count(),
            'issued' => (clone $query)->where('status', 'issued')->count(),
            'applied' => (clone $query)->where('status', 'applied')->count(),
            'total_credit' => (float) (clone $query)->sum('total_credit'),
            'remaining_credit' => (float) (clone $query)->sum('remaining_credit'),
            'applied_credit' => (float) (clone $query)->sum('applied_amount'),
        ];
    }
}
