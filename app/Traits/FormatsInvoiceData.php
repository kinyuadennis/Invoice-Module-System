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
        return [
            'id' => $invoice->id,
            'invoice_number' => $this->formatInvoiceNumber($invoice->id),
            'status' => $invoice->status,
            'total' => (float) $invoice->total,
            'subtotal' => (float) $invoice->subtotal,
            'tax' => (float) $invoice->tax,
            'due_date' => $invoice->due_date 
                ? Carbon::parse($invoice->due_date)->toDateString() 
                : null,
            'date' => $invoice->created_at->toDateString(),
            'client_id' => $invoice->client_id,
            'client' => [
                'id' => optional($invoice->client)->id ?? null,
                'name' => optional($invoice->client)->name ?? 'N/A',
                'email' => optional($invoice->client)->email ?? null,
                'phone' => optional($invoice->client)->phone ?? null,
                'address' => optional($invoice->client)->address ?? null,
            ],
        ];
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

