<?php

namespace App\Traits;

use App\Helpers\NumberToWords;
use App\Models\Invoice;
use Carbon\Carbon;

trait FormatsInvoiceData
{
    use FormatsInvoiceNumber;

    /**
     * Format invoice data for display
     */
    protected function formatInvoiceForDisplay(Invoice $invoice): array
    {
        $data = [
            'id' => $invoice->id,
            'invoice_number' => $invoice->full_number ?? $invoice->invoice_reference ?? $this->formatInvoiceNumber($invoice->id),
            'status' => $invoice->status,
            'total' => (float) $invoice->total,
            'subtotal' => (float) $invoice->subtotal,
            'tax' => (float) $invoice->tax,
            'vat_amount' => (float) ($invoice->vat_amount ?? $invoice->tax),
            'platform_fee' => (float) ($invoice->platform_fee ?? 0),
            'grand_total' => (float) ($invoice->grand_total ?? $invoice->total),
            'due_date' => $invoice->due_date
                ? Carbon::parse($invoice->due_date)->toDateString()
                : null,
            'issue_date' => $invoice->issue_date
                ? Carbon::parse($invoice->issue_date)->toDateString()
                : $invoice->created_at->toDateString(),
            'date' => $invoice->issue_date
                ? Carbon::parse($invoice->issue_date)->toDateString()
                : $invoice->created_at->toDateString(),
            'invoice_reference' => $invoice->full_number ?? $invoice->invoice_reference ?? null,
            'full_number' => $invoice->full_number ?? null,
            'prefix_used' => $invoice->prefix_used ?? null,
            'serial_number' => $invoice->serial_number ?? null,
            'payment_method' => $invoice->payment_method ?? null,
            'payment_details' => $invoice->payment_details ?? null,
            'notes' => $invoice->notes ?? null,
            'terms_and_conditions' => $invoice->terms_and_conditions ?? null,
            'po_number' => $invoice->po_number ?? null,
            'uuid' => $invoice->uuid ?? null,
            'client_id' => $invoice->client_id,
            'generated_at' => $invoice->created_at->toIso8601String(),
            'generated_timestamp' => $invoice->created_at->timestamp,
            'client' => [
                'id' => optional($invoice->client)->id ?? null,
                'name' => optional($invoice->client)->name ?? 'N/A',
                'email' => optional($invoice->client)->email ?? null,
                'phone' => optional($invoice->client)->phone ?? null,
                'address' => optional($invoice->client)->address ?? null,
            ],
        ];

        // Include user data if relationship is loaded (for admin views)
        if ($invoice->relationLoaded('user') && $invoice->user) {
            $data['user'] = [
                'id' => $invoice->user->id,
                'name' => $invoice->user->name,
                'email' => $invoice->user->email,
            ];
        }

        // Include company data if relationship is loaded
        if ($invoice->relationLoaded('company') && $invoice->company) {
            $data['company'] = [
                'id' => $invoice->company->id,
                'name' => $invoice->company->name,
                'logo' => $invoice->company->logo,
                'email' => $invoice->company->email,
                'phone' => $invoice->company->phone,
                'address' => $invoice->company->address,
                'kra_pin' => $invoice->company->kra_pin,
                'registration_number' => $invoice->company->registration_number ?? null,
                'invoice_prefix' => $invoice->company->invoice_prefix,
                'currency' => $invoice->company->currency ?? 'KES',
                'payment_terms' => $invoice->company->payment_terms ?? null,
            ];
        }

        return $data;
    }

    /**
     * Format invoice with items and payments for detailed view
     */
    protected function formatInvoiceWithDetails(Invoice $invoice): array
    {
        $data = $this->formatInvoiceForDisplay($invoice);

        $data['tax_rate'] = $data['subtotal'] > 0
            ? round(($data['tax'] / $data['subtotal']) * 100, 2)
            : 0;

        // Include user data if relationship is loaded
        if ($invoice->relationLoaded('user') && $invoice->user) {
            $data['user'] = [
                'id' => $invoice->user->id,
                'name' => $invoice->user->name,
                'email' => $invoice->user->email,
            ];
        }

        // Include company data if relationship is loaded
        if ($invoice->relationLoaded('company') && $invoice->company) {
            $data['company'] = [
                'id' => $invoice->company->id,
                'name' => $invoice->company->name,
                'logo' => $invoice->company->logo,
                'email' => $invoice->company->email,
                'phone' => $invoice->company->phone,
                'address' => $invoice->company->address,
                'kra_pin' => $invoice->company->kra_pin,
                'registration_number' => $invoice->company->registration_number ?? null,
                'invoice_prefix' => $invoice->company->invoice_prefix,
                'currency' => $invoice->company->currency ?? 'KES',
                'payment_terms' => $invoice->company->payment_terms ?? null,
            ];
        }

        // Get payment terms - use company default (per-invoice override can be added later via settings JSON column)
        $data['payment_terms'] = $data['company']['payment_terms'] ?? null;

        // Calculate amount in words
        $currency = $data['company']['currency'] ?? 'KES';
        $data['amount_in_words'] = NumberToWords::convert($data['grand_total'], $currency);

        $data['items'] = $invoice->invoiceItems->map(function ($item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
                'total' => (float) $item->total_price, // Keep for backward compatibility
            ];
        });

        $data['payments'] = $invoice->payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'payment_date' => Carbon::parse($payment->payment_date)->toDateString(),
                'payment_method' => $payment->payment_method,
                'reference' => $payment->reference ?? null,
            ];
        });

        $data['amount_paid'] = (float) $invoice->payments->sum('amount');
        $data['amount_due'] = (float) ($invoice->total - $invoice->payments->sum('amount'));

        return $data;
    }
}
