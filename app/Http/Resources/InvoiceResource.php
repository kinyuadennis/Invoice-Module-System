<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * InvoiceResource
 *
 * API resource for Invoice model.
 * Transforms invoice data for API responses.
 * Always includes company scoping - never exposes other companies' data.
 */
class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'full_number' => $this->full_number,
            'po_number' => $this->po_number,
            'status' => $this->status,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'currency' => $this->currency ?? 'KES',
            'notes' => $this->notes,
            'terms_and_conditions' => $this->terms_and_conditions,
            'payment_method' => $this->payment_method,
            'payment_details' => $this->payment_details,

            // Financial totals
            'subtotal' => (float) ($this->subtotal ?? 0),
            'discount' => (float) ($this->discount ?? 0),
            'discount_type' => $this->discount_type,
            'vat_amount' => (float) ($this->vat_amount ?? 0),
            'platform_fee' => (float) ($this->platform_fee ?? 0),
            'grand_total' => (float) ($this->grand_total ?? 0),

            // VAT registration status
            'vat_registered' => (bool) ($this->vat_registered ?? false),

            // Relationships (only include if loaded)
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                    'email' => $this->client->email,
                    'phone' => $this->client->phone,
                ];
            }),

            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),

            'items' => $this->whenLoaded('invoiceItems', function () {
                return $this->invoiceItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'description' => $item->description,
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'total_price' => (float) $item->total_price,
                        'vat_rate' => (float) ($item->vat_rate ?? 0),
                        'vat_amount' => (float) ($item->vat_amount ?? 0),
                    ];
                });
            }),

            // Snapshot indicator (for finalized invoices)
            'has_snapshot' => $this->when(isset($this->snapshot), function () {
                return $this->relationLoaded('snapshot') && $this->snapshot !== null;
            }),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
