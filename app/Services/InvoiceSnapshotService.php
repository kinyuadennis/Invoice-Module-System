<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceSnapshot;
use Illuminate\Support\Facades\Auth;

class InvoiceSnapshotService
{
    /**
     * Create an immutable snapshot of the invoice.
     */
    public function createSnapshot(Invoice $invoice, string $status): InvoiceSnapshot
    {
        // Eager load necessary relationships to avoid N+1 and ensure data availability
        $invoice->load(['company', 'client', 'invoiceItems', 'user']);

        $snapshotData = [
            'invoice_details' => [
                'id' => $invoice->id,
                'uuid' => $invoice->uuid,
                'invoice_number' => $invoice->invoice_number,
                'full_number' => $invoice->full_number,
                'issue_date' => $invoice->issue_date->format('Y-m-d'),
                'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                'status' => $status, // Use the status passed to the snapshot, which might differ from current DB status
                'currency' => $invoice->company->currency ?? 'KES',
                'notes' => $invoice->notes,
                'terms_and_conditions' => $invoice->terms_and_conditions,
                'payment_details' => $invoice->payment_details,
            ],
            'seller_details' => [
                'name' => $invoice->company->name,
                'address' => $invoice->company->address,
                'email' => $invoice->company->email,
                'phone' => $invoice->company->phone,
                'kra_pin' => $invoice->company->kra_pin,
                'logo' => $this->resolveLogoPath($invoice->company->logo),
                'registration_number' => $invoice->company->registration_number,
                'payment_terms' => $invoice->company->payment_terms,
            ],
            'client_details' => [
                'name' => $invoice->client->name ?? 'N/A',
                'address' => $invoice->client->address ?? '',
                'email' => $invoice->client->email ?? '',
                'phone' => $invoice->client->phone ?? '',
                'kra_pin' => $invoice->client->kra_pin ?? '',
            ],
            'items' => $invoice->invoiceItems->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total_price ?? ($item->quantity * $item->unit_price),
                    // Add other item fields if necessary
                ];
            })->toArray(),
            'totals' => [
                'subtotal' => $invoice->subtotal,
                'tax' => $invoice->tax,
                'vat_amount' => $invoice->vat_amount,
                'discount' => $invoice->discount,
                'platform_fee' => $invoice->platform_fee ?? 0, // Legacy field, will be removed
                'grand_total' => $invoice->grand_total,
            ],
            'compliance' => [
                'etims_control_number' => $invoice->etims_control_number,
                'etims_qr_code' => $invoice->etims_qr_code,
                'etims_submitted_at' => $invoice->etims_submitted_at,
            ],
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'version' => '1.0',
            ],
        ];

        return InvoiceSnapshot::create([
            'invoice_id' => $invoice->id,
            'status' => $status,
            'snapshot_data' => $snapshotData,
            'triggered_by' => Auth::id() ? Auth::user()->name : 'System',
        ]);
    }

    /**
     * Find the latest snapshot for an invoice.
     */
    public function findLatestSnapshot(Invoice $invoice): ?InvoiceSnapshot
    {
        return $invoice->snapshots()->latest()->first();
    }

    /**
     * Resolve the logo path to an absolute file path.
     */
    private function resolveLogoPath(?string $logoPath): ?string
    {
        if (! $logoPath) {
            return null;
        }

        // If it's already a URL, return as is
        if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
            return $logoPath;
        }

        // Check if it exists in storage
        $fullPath = public_path('storage/'.$logoPath);
        if (file_exists($fullPath)) {
            return $fullPath;
        }

        return null;
    }
}
