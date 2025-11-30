<?php

namespace App\Traits;

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
            'invoice_number' => $this->formatInvoiceNumber($invoice->id),
            'status' => $invoice->status,
            'total' => (float) $invoice->total,
            'subtotal' => (float) $invoice->subtotal,
            'tax' => (float) $invoice->tax,
            'due_date' => $invoice->due_date
                ? Carbon::parse($invoice->due_date)->toDateString()
                : null,
            'issue_date' => $invoice->issue_date
                ? Carbon::parse($invoice->issue_date)->toDateString()
                : $invoice->created_at->toDateString(),
            'date' => $invoice->issue_date
                ? Carbon::parse($invoice->issue_date)->toDateString()
                : $invoice->created_at->toDateString(),
            'invoice_reference' => $invoice->invoice_reference ?? null,
            'payment_method' => $invoice->payment_method ?? null,
            'notes' => $invoice->notes ?? null,
            'client_id' => $invoice->client_id,
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

        $data['items'] = $invoice->invoiceItems->map(function ($item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total' => (float) $item->total_price,
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
