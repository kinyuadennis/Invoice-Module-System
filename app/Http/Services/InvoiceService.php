<?php

namespace App\Http\Services;

use App\Models\Client;
use App\Models\Company;
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
        $user = $request->user();
        $companyId = $user->company_id;

        if (! $companyId) {
            throw new \RuntimeException('User must belong to a company to create invoices.');
        }

        // Validate client belongs to same company
        $client = Client::where('id', $request->input('client_id'))
            ->where('company_id', $companyId)
            ->firstOrFail();

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

        // Automatically set company_id and user_id from authenticated user
        $data['company_id'] = $companyId;
        $data['user_id'] = $user->id;

        // Get company for invoice prefix
        $company = Company::findOrFail($companyId);

        // Generate invoice reference if not provided
        if (empty($data['invoice_reference'])) {
            $data['invoice_reference'] = $this->generateInvoiceReference($company);
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

        $vatAmount = $subtotal * 0.16; // 16% VAT (Kenyan standard)
        $totalBeforeFee = $subtotal + $vatAmount;
        $platformFee = $totalBeforeFee * 0.008; // 0.8% platform fee
        $grandTotal = $totalBeforeFee + $platformFee;

        // Add calculated totals to invoice data
        $data['subtotal'] = $subtotal;
        $data['tax'] = $vatAmount; // Keep for backward compatibility
        $data['vat_amount'] = $vatAmount;
        $data['platform_fee'] = $platformFee;
        $data['total'] = $totalBeforeFee; // Keep for backward compatibility
        $data['grand_total'] = $grandTotal;

        // Start creating invoice entry with all required fields
        $invoice = Invoice::create($data);

        // Add invoice items with company_id
        foreach ($items as $item) {
            $invoice->invoiceItems()->create([
                'company_id' => $companyId,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'] ?? $item['rate'] ?? 0,
                'vat_included' => $item['vat_included'] ?? false,
                'vat_rate' => $item['vat_rate'] ?? 16.00,
                'total_price' => $item['total_price'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['rate'] ?? 0)),
            ]);
        }

        // Refresh invoice to ensure items are loaded, then update totals (in case of any discrepancies)
        $invoice->refresh();
        $this->updateTotals($invoice);

        // Auto-generate platform fee (with company_id)
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

        $vatAmount = $subtotal * 0.16; // 16% VAT (Kenyan standard)
        $totalBeforeFee = $subtotal + $vatAmount;
        $platformFee = $totalBeforeFee * 0.008; // 0.8% platform fee
        $grandTotal = $totalBeforeFee + $platformFee;

        $invoice->subtotal = $subtotal;
        $invoice->tax = $vatAmount; // Keep for backward compatibility
        $invoice->vat_amount = $vatAmount;
        $invoice->platform_fee = $platformFee;
        $invoice->total = $totalBeforeFee; // Keep for backward compatibility
        $invoice->grand_total = $grandTotal;
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
                'vat_included' => (bool) $item->vat_included,
                'vat_rate' => (float) $item->vat_rate,
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
     * Get invoice statistics scoped by company
     *
     * @param  int  $companyId  Company ID to scope statistics
     */
    public function getInvoiceStats(int $companyId): array
    {
        $query = Invoice::where('company_id', $companyId);

        return [
            'total' => (clone $query)->count(),
            'paid' => (float) (clone $query)->where('status', 'paid')->sum('grand_total'),
            'outstanding' => (float) (clone $query)->whereIn('status', ['draft', 'sent'])->sum('grand_total'),
            'overdue' => (float) (clone $query)->where('status', 'overdue')->sum('grand_total'),
        ];
    }

    /**
     * Generate unique invoice reference using company prefix
     */
    private function generateInvoiceReference(Company $company): string
    {
        $prefix = $company->invoice_prefix ?? 'INV';
        $lastInvoice = Invoice::where('company_id', $company->id)
            ->whereNotNull('invoice_reference')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice && preg_match('/'.preg_quote($prefix, '/').'-(\d+)/', $lastInvoice->invoice_reference, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%04d', $prefix, $sequence);
    }
}
