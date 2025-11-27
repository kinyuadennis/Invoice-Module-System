<?php

namespace App\Http\Services;

use App\Models\Invoice;
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
        $data = $request->only(['client_id', 'due_date', 'status']);

        // Automatically set user_id from authenticated user
        $data['user_id'] = $request->user()->id;

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
        $formatted = [
            'id' => $data['id'],
            'invoice_number' => $data['invoice_number'],
            'status' => $data['status'],
            'total' => $data['total'],
            'due_date' => $data['due_date'],
            'issue_date' => $data['date'],
            'client' => [
                'id' => $data['client']['id'],
                'name' => $data['client']['name'],
                'email' => $data['client']['email'],
            ],
        ];

        // Include user data if invoice has user relationship loaded (for admin views)
        if ($invoice->relationLoaded('user') && $invoice->user) {
            $formatted['user'] = [
                'id' => $invoice->user->id,
                'name' => $invoice->user->name,
                'email' => $invoice->user->email,
            ];
        }

        return $formatted;
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
     *
     * @param  int|null  $userId  If provided, scope to this user's invoices
     */
    public function getInvoiceStats(?int $userId = null): array
    {
        $query = Invoice::query();
        if ($userId) {
            $query->where('user_id', $userId);
        }

        return [
            'total' => (clone $query)->count(),
            'paid' => (float) (clone $query)->where('status', 'paid')->sum('total'),
            'outstanding' => (float) (clone $query)->whereIn('status', ['draft', 'sent'])->sum('total'),
            'overdue' => (float) (clone $query)->where('status', 'overdue')->sum('total'),
        ];
    }
}
