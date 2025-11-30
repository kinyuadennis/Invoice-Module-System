<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Traits\FormatsInvoiceData;
use Illuminate\Http\Request;

class InvoiceService
{
    use FormatsInvoiceData;

    protected PlatformFeeService $platformFeeService;

    public function __construct(PlatformFeeService $platformFeeService)
    {
        $this->platformFeeService = $platformFeeService;
    }

    /**
     * Create a new invoice
     */
    public function createInvoice(Request $request): Invoice
    {
        // Validate and prepare data for invoice creation
        $data = $request->only([
            'client_id',
            'issue_date',
            'due_date',
            'status',
            'invoice_reference',
            'payment_method',
            'payment_details',
            'notes',
        ]);

        // Automatically set user_id from authenticated user
        $data['user_id'] = $request->user()->id;

        // Generate invoice reference if not provided
        if (empty($data['invoice_reference'])) {
            $data['invoice_reference'] = $this->generateInvoiceReference();
        }

        // Set issue_date to today if not provided
        if (empty($data['issue_date'])) {
            $data['issue_date'] = now()->toDateString();
        }

        // Calculate totals BEFORE creating invoice (required fields)
        $items = $request->input('items', []);
        $subtotal = 0;
        
        foreach ($items as $item) {
            $itemTotal = $item['total_price'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['rate'] ?? 0));
            $subtotal += $itemTotal;
        }
        
        $tax = $subtotal * 0.16; // 16% VAT (Kenyan standard)
        $total = $subtotal + $tax;
        
        // Add calculated totals to invoice data
        $data['subtotal'] = $subtotal;
        $data['tax'] = $tax;
        $data['total'] = $total;

        // Start creating invoice entry with all required fields
        $invoice = Invoice::create($data);

        // Add invoice items
        foreach ($items as $item) {
            $invoice->invoiceItems()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'] ?? $item['rate'] ?? 0,
                'total_price' => $item['total_price'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['rate'] ?? 0)),
            ]);
        }

        // Refresh invoice to ensure items are loaded, then update totals (in case of any discrepancies)
        $invoice->refresh();
        $this->updateTotals($invoice);

        // Auto-generate platform fee
        $this->platformFeeService->generateFeeForInvoice($invoice);

        return $invoice;
    }

    /**
     * Update invoice totals based on items
     */
    public function updateTotals(Invoice $invoice): void
    {
        $subtotal = $invoice->invoiceItems->sum(function ($item) {
            return $item->total_price;
        });

        $tax = $subtotal * 0.16; // 16% VAT (Kenyan standard)

        $invoice->subtotal = $subtotal;
        $invoice->tax = $tax;
        $invoice->total = $subtotal + $tax;
        $invoice->save();

        // Update platform fee if invoice already has one
        if ($invoice->platformFees()->exists()) {
            $this->platformFeeService->generateFeeForInvoice($invoice);
        }
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

    /**
     * Generate unique invoice reference
     */
    private function generateInvoiceReference(): string
    {
        $year = date('Y');
        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->whereNotNull('invoice_reference')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice && preg_match('/INV-(\d{4})-(\d+)/', $lastInvoice->invoice_reference, $matches)) {
            $sequence = (int) $matches[2] + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('INV-%s-%04d', $year, $sequence);
    }
}
