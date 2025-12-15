<?php

namespace App\Http\Services;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Service;
use App\Services\CurrentCompanyService;
use App\Services\InvoicePrefixService;
use App\Traits\FormatsInvoiceData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    use FormatsInvoiceData;

    protected PlatformFeeService $platformFeeService;

    protected InvoicePrefixService $prefixService;

    protected InvoiceCalculationService $calculationService;

    public function __construct(
        PlatformFeeService $platformFeeService,
        InvoicePrefixService $prefixService,
        InvoiceCalculationService $calculationService
    ) {
        $this->platformFeeService = $platformFeeService;
        $this->prefixService = $prefixService;
        $this->calculationService = $calculationService;
    }

    /**
     * Create a new invoice
     */
    public function createInvoice(Request $request): Invoice
    {
        $user = $request->user();
        $companyId = CurrentCompanyService::requireId();

        // Validate client belongs to same company (if provided)
        // Client is optional for drafts but required for final invoices
        $clientId = $request->input('client_id');
        if ($clientId) {
            $client = Client::where('id', $clientId)
                ->where('company_id', $companyId)
                ->firstOrFail();
        }

        // Validate and prepare data for invoice creation
        $data = $request->only([
            'client_id',
            'issue_date',
            'due_date',
            'status',
            'invoice_reference',
            'po_number',
            'payment_method',
            'payment_details',
            'notes',
            'terms_and_conditions',
            'vat_registered',
            'discount',
            'discount_type',
        ]);

        // Automatically set company_id and user_id from authenticated user
        $data['company_id'] = $companyId;
        $data['user_id'] = $user->id;

        // Get company for invoice prefix and template
        // No need to refresh - findOrFail already gets fresh data from DB
        $company = Company::findOrFail($companyId);

        // Store the template_id that was selected at invoice creation time
        $template = $company->getActiveInvoiceTemplate();
        $data['template_id'] = $template->id;

        // Generate invoice number - ALWAYS use client-specific if enabled
        // Only skip if invoice_reference is explicitly provided and we want to preserve it
        // But if client-specific numbering is enabled, we MUST regenerate per client
        $useClientSpecific = (bool) $company->use_client_specific_numbering;

        // Get client_id - check both data and request, handle null/empty properly
        // When JSON is sent, null values are included in $data, so we need to check explicitly
        $clientId = null;

        // First check if client_id exists in $data and is not null/empty
        if (isset($data['client_id']) && $data['client_id'] !== null && $data['client_id'] !== '') {
            $clientId = (int) $data['client_id'];
        } elseif ($request->has('client_id') && $request->input('client_id') !== null && $request->input('client_id') !== '') {
            // Check request directly (for JSON requests)
            $clientId = (int) $request->input('client_id');
        }

        // Ensure client_id is set in data if we have it
        if ($clientId) {
            $data['client_id'] = $clientId;
        }

        // If client-specific numbering is enabled, ALWAYS generate (ignore existing invoice_reference)
        if ($useClientSpecific) {
            // Client-specific numbering is enabled
            $isDraft = ($data['status'] ?? 'draft') === 'draft';

            if (! $clientId && ! $isDraft) {
                throw new \RuntimeException('Client ID is required when client-specific invoice numbering is enabled.');
            }

            // If we have a client_id, generate client-specific number
            if ($clientId) {
                // Get and validate client belongs to company
                $client = Client::where('id', $clientId)
                    ->where('company_id', $companyId)
                    ->firstOrFail();

                // Generate client-specific invoice number with row locking
                $numberingData = $this->prefixService->generateClientInvoiceNumber($company, $client);

                $data['client_sequence'] = $numberingData['client_sequence'];
                $data['invoice_number'] = $numberingData['invoice_number'];
                $data['invoice_reference'] = $numberingData['invoice_number']; // Override any existing value

                // Also set prefix_used and serial_number for backward compatibility
                $prefix = $this->prefixService->getActivePrefix($company);
                $data['prefix_used'] = $prefix->prefix;
                $data['serial_number'] = $numberingData['client_sequence'];
                $data['full_number'] = $numberingData['invoice_number'];
            } else {
                // Draft without client - will be generated when client is added later
                $data['invoice_number'] = null;
                $data['client_sequence'] = null;
            }
        } elseif (empty($data['invoice_reference'])) {
            // Global/company-wide numbering (only if client-specific is disabled AND no invoice_reference provided)
            $prefix = $this->prefixService->getActivePrefix($company);
            $serialNumber = $this->prefixService->generateNextSerialNumber($company, $prefix);
            $fullNumber = $this->prefixService->generateFullNumber($company, $prefix, $serialNumber);

            $data['prefix_used'] = $prefix->prefix;
            $data['serial_number'] = $serialNumber;
            $data['full_number'] = $fullNumber;
            $data['invoice_reference'] = $fullNumber; // Keep for backward compatibility
        }

        // Set issue_date to today if not provided
        if (empty($data['issue_date'])) {
            $data['issue_date'] = now()->toDateString();
        }

        // Calculate totals using calculation service (authoritative source)
        $items = $request->input('items', []);
        $vatRegistered = $request->input('vat_registered', false);

        // Prepare items for calculation service
        $calculationItems = [];
        foreach ($items as $item) {
            $calculationItems[] = [
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? $item['rate'] ?? 0,
                'vat_included' => $item['vat_included'] ?? false,
                'vat_rate' => $item['vat_rate'] ?? 16.00,
            ];
        }

        // Get company configuration (company-specific rates)
        $company = Company::findOrFail($companyId);
        $vatEnabled = $company->isVatEnabled();
        $vatRate = $company->getVatRateDecimal();
        $platformFeeEnabled = $company->isPlatformFeeEnabled();
        $platformFeeRate = $company->getPlatformFeeRateDecimal();

        // Calculate using authoritative service
        $calculationResult = $this->calculationService->calculate($calculationItems, [
            'vat_enabled' => $vatEnabled,
            'vat_rate' => $vatRate,
            'vat_registered' => $vatRegistered,
            'platform_fee_enabled' => $platformFeeEnabled,
            'platform_fee_rate' => $platformFeeRate,
            'discount' => $request->input('discount', 0),
            'discount_type' => $request->input('discount_type', 'fixed'),
        ]);

        // Add calculated totals to invoice data
        $data['subtotal'] = $calculationResult['subtotal'];
        $data['discount'] = $calculationResult['discount'];
        $data['tax'] = $calculationResult['vat_amount']; // Keep for backward compatibility
        $data['vat_amount'] = $calculationResult['vat_amount'];
        $data['platform_fee'] = $calculationResult['platform_fee'];
        $data['total'] = $calculationResult['total']; // Keep for backward compatibility
        $data['grand_total'] = $calculationResult['grand_total'];

        // Start creating invoice entry with all required fields
        $invoice = Invoice::create($data);

        // Add invoice items with company_id and track services
        foreach ($items as $item) {
            $description = $item['description'];
            $unitPrice = $item['unit_price'] ?? $item['rate'] ?? 0;

            // Auto-save item to items table if it doesn't exist
            $itemModel = Item::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'name' => $description,
                ],
                [
                    'unit_price' => $unitPrice,
                ]
            );

            // Update unit_price if it's different (to keep items table up to date)
            if ($itemModel->unit_price != $unitPrice) {
                $itemModel->update(['unit_price' => $unitPrice]);
            }

            // Calculate total_price = quantity * unit_price
            $quantity = (float) ($item['quantity'] ?? 1);
            $calculatedTotalPrice = $quantity * $unitPrice;

            $invoice->invoiceItems()->create([
                'company_id' => $companyId,
                'item_id' => $itemModel->id, // Link to reusable item
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'vat_included' => $item['vat_included'] ?? false,
                'vat_rate' => $item['vat_rate'] ?? 16.00,
                'total_price' => $calculatedTotalPrice, // Always calculate: quantity * unit_price
            ]);

            // Track service usage
            $this->trackServiceUsage($companyId, $description, $unitPrice);
        }

        // Reload invoice items relationship to ensure fresh data, then update totals
        $invoice->load('invoiceItems');
        $this->updateTotals($invoice);

        // Auto-generate platform fee (with company_id)
        $this->platformFeeService->generateFeeForInvoice($invoice);

        return $invoice;
    }

    /**
     * Update an existing invoice
     */
    public function updateInvoice(Invoice $invoice, Request $request): Invoice
    {
        $companyId = $invoice->company_id;
        // No need to refresh - findOrFail already gets fresh data from DB
        $company = Company::findOrFail($companyId);

        // Validate client belongs to same company
        if ($request->has('client_id')) {
            $client = Client::where('id', $request->input('client_id'))
                ->where('company_id', $companyId)
                ->firstOrFail();
        }

        // Update invoice data (prefix fields are immutable and must never be updated)
        $data = $request->only([
            'client_id',
            'issue_date',
            'due_date',
            'status',
            'payment_method',
            'payment_details',
            'notes',
        ]);

        // If client-specific numbering is enabled and client_id is being set/changed,
        // and invoice doesn't have a client_sequence yet, generate it
        $useClientSpecific = (bool) $company->use_client_specific_numbering;

        // Get new client_id - properly handle null/empty values
        $newClientId = null;
        if (! empty($data['client_id'])) {
            $newClientId = (int) $data['client_id'];
        } elseif (! empty($request->input('client_id'))) {
            $newClientId = (int) $request->input('client_id');
        }

        $hasClientSequence = $invoice->client_sequence !== null;
        $currentClientId = $invoice->client_id;

        // If client-specific numbering is enabled and:
        // 1. Client is being added/changed, AND
        // 2. Invoice doesn't have a client_sequence yet, OR
        // 3. Client changed to a different client
        if ($useClientSpecific && $newClientId && ($newClientId != $currentClientId || ! $hasClientSequence) && empty($invoice->invoice_number)) {
            // Generate client-specific invoice number for this invoice
            $client = Client::where('id', $newClientId)
                ->where('company_id', $companyId)
                ->firstOrFail();

            $numberingData = $this->prefixService->generateClientInvoiceNumber($company, $client);

            // Set client-specific fields (only if not already set)
            $data['client_sequence'] = $numberingData['client_sequence'];
            $data['invoice_number'] = $numberingData['invoice_number'];
            $data['invoice_reference'] = $numberingData['invoice_number'];

            $prefix = $this->prefixService->getActivePrefix($company);
            $data['prefix_used'] = $prefix->prefix;
            $data['serial_number'] = $numberingData['client_sequence'];
            $data['full_number'] = $numberingData['invoice_number'];
        }

        // Explicitly prevent any prefix field updates if invoice_number is already set (immutable)
        // This allows migrating from global to client-specific numbering for drafts/invoices without invoice_number
        // But prevents changing invoice numbers once they're set
        if (! empty($invoice->invoice_number)) {
            // Invoice number is already set - make all numbering fields immutable
            unset($data['prefix_used'], $data['serial_number'], $data['full_number'], $data['invoice_reference'], $data['client_sequence'], $data['invoice_number']);
        } elseif ($invoice->client_sequence !== null) {
            // Client sequence is set but invoice_number might not be - still prevent changes to sequence-related fields
            unset($data['client_sequence'], $data['invoice_number']);
        }

        // Update items if provided
        if ($request->has('items')) {
            // Delete existing items
            $invoice->invoiceItems()->delete();

            // Calculate totals using calculation service (authoritative source)
            $items = $request->input('items', []);

            // Prepare items for calculation service
            $calculationItems = [];
            foreach ($items as $item) {
                $calculationItems[] = [
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? $item['rate'] ?? 0,
                    'vat_included' => $item['vat_included'] ?? false,
                    'vat_rate' => $item['vat_rate'] ?? 16.00,
                ];
            }

            // Get company configuration (company-specific rates)
            $company = $invoice->company;
            $vatEnabled = $company->isVatEnabled();
            $vatRate = $company->getVatRateDecimal();
            $platformFeeEnabled = $company->isPlatformFeeEnabled();
            $platformFeeRate = $company->getPlatformFeeRateDecimal();

            // Calculate using authoritative service
            $calculationResult = $this->calculationService->calculate($calculationItems, [
                'vat_enabled' => $vatEnabled,
                'vat_rate' => $vatRate,
                'vat_registered' => $invoice->vat_registered ?? false,
                'platform_fee_enabled' => $platformFeeEnabled,
                'platform_fee_rate' => $platformFeeRate,
                'discount' => $invoice->discount ?? 0,
                'discount_type' => $invoice->discount_type ?? 'fixed',
            ]);

            $data['subtotal'] = $calculationResult['subtotal'];
            $data['tax'] = $calculationResult['vat_amount'];
            $data['vat_amount'] = $calculationResult['vat_amount'];
            $data['platform_fee'] = $calculationResult['platform_fee'];
            $data['total'] = $calculationResult['total'];
            $data['grand_total'] = $calculationResult['grand_total'];

            // Create new items and track services
            foreach ($items as $item) {
                $description = $item['description'];
                $unitPrice = $item['unit_price'] ?? $item['rate'] ?? 0;

                // Auto-save item to items table if it doesn't exist
                $itemModel = Item::firstOrCreate(
                    [
                        'company_id' => $companyId,
                        'name' => $description,
                    ],
                    [
                        'unit_price' => $unitPrice,
                    ]
                );

                // Update unit_price if it's different
                if ($itemModel->unit_price != $unitPrice) {
                    $itemModel->update(['unit_price' => $unitPrice]);
                }

                // Calculate total_price = quantity * unit_price
                $quantity = (float) ($item['quantity'] ?? 1);
                $calculatedTotalPrice = $quantity * $unitPrice;

                $invoice->invoiceItems()->create([
                    'company_id' => $companyId,
                    'item_id' => $itemModel->id, // Link to reusable item
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'vat_included' => $item['vat_included'] ?? false,
                    'vat_rate' => $item['vat_rate'] ?? 16.00,
                    'total_price' => $calculatedTotalPrice, // Always calculate: quantity * unit_price
                ]);

                // Track service usage
                $this->trackServiceUsage($companyId, $description, $unitPrice);
            }
        }

        $invoice->update($data);

        // Update totals (only if draft - enforced in updateTotals method)
        $this->updateTotals($invoice);

        // Update platform fee
        if ($invoice->platformFees()->exists()) {
            $this->platformFeeService->generateFeeForInvoice($invoice);
        }

        return $invoice->fresh();
    }

    /**
     * Calculate preview totals (for draft/preview scenarios).
     * Uses authoritative calculation service.
     *
     * @param  array  $items  Line items
     * @param  array  $options  Options (vat_registered, discount, discount_type)
     * @return array Calculation results
     */
    public function calculatePreviewTotals(array $items, array $options = []): array
    {
        // Prepare items for calculation service
        $calculationItems = [];
        foreach ($items as $item) {
            $calculationItems[] = [
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'vat_included' => $item['vat_included'] ?? false,
                'vat_rate' => $item['vat_rate'] ?? 16.00,
            ];
        }

        // Get company configuration (company-specific rates)
        // Note: For preview, we need company from request context
        // This is a limitation - preview doesn't have invoice yet
        // For now, use defaults; in production, get from session/request
        $companyId = \App\Services\CurrentCompanyService::getId();
        if ($companyId) {
            $company = Company::find($companyId);
            if ($company) {
                $vatEnabled = $company->isVatEnabled();
                $vatRate = $company->getVatRateDecimal();
                $platformFeeEnabled = $company->isPlatformFeeEnabled();
                $platformFeeRate = $company->getPlatformFeeRateDecimal();
            } else {
                // Fallback to defaults
                $vatEnabled = true;
                $vatRate = 16.00;
                $platformFeeEnabled = true;
                $platformFeeRate = 0.03;
            }
        } else {
            // Fallback to defaults
            $vatEnabled = true;
            $vatRate = 16.00;
            $platformFeeEnabled = true;
            $platformFeeRate = 0.03;
        }

        // Calculate using authoritative service
        return $this->calculationService->calculate($calculationItems, [
            'vat_enabled' => $vatEnabled,
            'vat_rate' => $vatRate,
            'vat_registered' => $options['vat_registered'] ?? false,
            'platform_fee_enabled' => $platformFeeEnabled,
            'platform_fee_rate' => $platformFeeRate,
            'discount' => $options['discount'] ?? 0,
            'discount_type' => $options['discount_type'] ?? 'fixed',
        ]);
    }

    /**
     * Update invoice totals based on items.
     * Cannot be called on finalized invoices (calculations are frozen).
     */
    public function updateTotals(Invoice $invoice): void
    {
        // Prevent recalculation on finalized invoices (calculations are frozen)
        if ($invoice->isFinalized()) {
            $invoiceNumber = $invoice->invoice_number ?? $invoice->id;
            throw new \DomainException(
                "Cannot recalculate totals for finalized invoice #{$invoiceNumber}. Calculations are frozen at finalization."
            );
        }

        // Prepare items for calculation service
        $calculationItems = [];
        foreach ($invoice->invoiceItems as $item) {
            $calculationItems[] = [
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'vat_included' => $item->vat_included ?? false,
                'vat_rate' => $item->vat_rate ?? 16.00,
            ];
        }

        // Get company configuration (company-specific rates)
        $company = $invoice->company;
        $vatEnabled = $company->isVatEnabled();
        $vatRate = $company->getVatRateDecimal();
        $platformFeeEnabled = $company->isPlatformFeeEnabled();
        $platformFeeRate = $company->getPlatformFeeRateDecimal();

        // Calculate using authoritative service
        $calculationResult = $this->calculationService->calculate($calculationItems, [
            'vat_enabled' => $vatEnabled,
            'vat_rate' => $vatRate,
            'vat_registered' => $invoice->vat_registered ?? false,
            'platform_fee_enabled' => $platformFeeEnabled,
            'platform_fee_rate' => $platformFeeRate,
            'discount' => $invoice->discount ?? 0,
            'discount_type' => $invoice->discount_type ?? 'fixed',
        ]);

        $invoice->subtotal = $calculationResult['subtotal'];
        $invoice->tax = $calculationResult['vat_amount']; // Keep for backward compatibility
        $invoice->vat_amount = $calculationResult['vat_amount'];
        $invoice->platform_fee = $calculationResult['platform_fee'];
        $invoice->total = $calculationResult['total']; // Keep for backward compatibility
        $invoice->grand_total = $calculationResult['grand_total'];
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
                'total_price' => (float) $item->total_price,
                'total' => (float) $item->total_price, // Keep for backward compatibility
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
     * Generate unique invoice reference using company format settings
     */
    private function generateInvoiceReference(Company $company): string
    {
        // Get format settings with defaults
        $prefix = $company->invoice_prefix ?? 'INV';
        $suffix = $company->invoice_suffix ?? '';
        $padding = $company->invoice_padding ?? 4;
        $format = $company->invoice_format ?? '{PREFIX}-{NUMBER}';

        // Get last invoice number
        $lastInvoice = Invoice::where('company_id', $company->id)
            ->whereNotNull('invoice_reference')
            ->orderBy('id', 'desc')
            ->first();

        // Extract sequence number from last invoice
        $sequence = 1;
        if ($lastInvoice && $lastInvoice->invoice_reference) {
            // Try to extract number from various formats
            if (preg_match('/(\d+)/', $lastInvoice->invoice_reference, $matches)) {
                $sequence = (int) $matches[1] + 1;
            }
        }

        // Pad the number
        $paddedNumber = str_pad($sequence, $padding, '0', STR_PAD_LEFT);

        // Build reference based on format pattern
        $reference = $format;
        $reference = str_replace('{PREFIX}', $prefix, $reference);
        $reference = str_replace('{NUMBER}', $paddedNumber, $reference);
        $reference = str_replace('{YEAR}', date('Y'), $reference);
        $reference = str_replace('{SUFFIX}', $suffix, $reference);

        return $reference;
    }

    /**
     * Track service usage from invoice items
     */
    private function trackServiceUsage(int $companyId, string $description, float $unitPrice): void
    {
        $service = Service::firstOrCreate(
            [
                'company_id' => $companyId,
                'name' => $description,
            ],
            [
                'default_price' => $unitPrice,
                'usage_count' => 0,
            ]
        );

        // Update usage count and average price
        $service->increment('usage_count');

        // Calculate average price from all invoice items with this description
        $averagePrice = DB::table('invoice_items')
            ->where('company_id', $companyId)
            ->where('description', $description)
            ->avg('unit_price');

        if ($averagePrice) {
            $service->update(['default_price' => round((float) $averagePrice, 2)]);
        }
    }

    /**
     * Get service library for a company (services used in invoices)
     */
    public function getServiceLibrary(int $companyId): array
    {
        // Get services from database (tracked services)
        $trackedServices = Service::where('company_id', $companyId)
            ->orderBy('usage_count', 'desc')
            ->orderBy('name', 'asc')
            ->get()
            ->mapWithKeys(function ($service) {
                return [$service->name => (float) $service->default_price];
            })
            ->toArray();

        // If no tracked services, extract from invoice items
        if (empty($trackedServices)) {
            $trackedServices = $this->extractServicesFromInvoiceItems($companyId);
        }

        return $trackedServices;
    }

    /**
     * Extract services from existing invoice items
     */
    private function extractServicesFromInvoiceItems(int $companyId): array
    {
        $services = DB::table('invoice_items')
            ->where('company_id', $companyId)
            ->select('description', DB::raw('AVG(unit_price) as avg_price'), DB::raw('COUNT(*) as usage_count'))
            ->groupBy('description')
            ->orderBy('usage_count', 'desc')
            ->orderBy('description', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->description => round((float) $item->avg_price, 2)];
            })
            ->toArray();

        // Store extracted services in database for future use
        foreach ($services as $name => $price) {
            Service::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'name' => $name,
                ],
                [
                    'default_price' => $price,
                    'usage_count' => DB::table('invoice_items')
                        ->where('company_id', $companyId)
                        ->where('description', $name)
                        ->count(),
                ]
            );
        }

        return $services;
    }
}
