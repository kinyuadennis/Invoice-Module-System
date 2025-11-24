<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceService
{
    public function createInvoice(Request $request)
    {
        // Validate and prepare data for invoice creation
        $data = $request->only(['client_id', 'user_id', 'due_date', 'status']);

        // Start creating invoice entry
        $invoice = Invoice::create($data);

        // Add invoice items
        foreach ($request->input('items') as $item) {
            $invoice->invoiceItems()->create($item);
        }

        // Calculate totals and update invoice
        $this->updateTotals($invoice);

        return $invoice;
    }

    public function updateTotals(Invoice $invoice)
    {
        $subtotal = $invoice->invoiceItems->sum(function ($item) {
            return $item->total_price;
        });

        $tax = $subtotal * 0.1; // Example 10% tax

        $invoice->subtotal = $subtotal;
        $invoice->tax = $tax;
        $invoice->total = $subtotal + $tax;
        $invoice->save();
    }

    // Add more methods for update, send, cancel, etc.
}
