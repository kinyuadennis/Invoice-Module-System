<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Traits\FormatsInvoiceData;
use Illuminate\Http\Request;

class InvoiceService
{
    use FormatsInvoiceData;

    /**
     * Create a new invoice
     */
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

    /**
     * Update invoice totals based on items
     */
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

    /**
     * Format invoice for list display
     */
    public function formatInvoiceForList(Invoice $invoice): array
    {
        $data = $this->formatInvoiceForDisplay($invoice);
        // Return only fields needed for list view
        return [
            'id' => $data['id'],
            'invoice_number' => $data['invoice_number'],
            'status' => $data['status'],
            'total' => $data['total'],
            'due_date' => $data['due_date'],
            'date' => $data['date'],
            'client' => [
                'id' => $data['client']['id'],
                'name' => $data['client']['name'],
                'email' => $data['client']['email'],
            ],
        ];
    }

    /**
     * Format invoice with full details for show view
     */
    public function formatInvoiceForShow(Invoice $invoice): array
    {
        return $this->formatInvoiceWithDetails($invoice);
    }

    /**
     * Format invoice for edit view
     */
    public function formatInvoiceForEdit(Invoice $invoice): array
    {
        $data = $this->formatInvoiceForDisplay($invoice);
        
        $data['items'] = $invoice->invoiceItems->map(function ($item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total' => (float) $item->total_price,
            ];
        });

        $data['tax_rate'] = $data['subtotal'] > 0 
            ? round(($data['tax'] / $data['subtotal']) * 100, 2) 
            : 0;

        $data['notes'] = null; // Add notes field if needed

        return $data;
    }

    /**
     * Get invoice statistics
     */
    public function getInvoiceStats(): array
    {
        return [
            'total' => Invoice::count(),
            'paid' => (float) Invoice::where('status', 'paid')->sum('total'),
            'outstanding' => (float) Invoice::whereIn('status', ['draft', 'sent'])->sum('total'),
            'overdue' => (float) Invoice::where('status', 'overdue')->sum('total'),
        ];
    }
}
