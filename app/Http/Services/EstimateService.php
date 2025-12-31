<?php

namespace App\Http\Services;

use App\Models\Client;
use App\Models\Company;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Service;
use App\Services\CurrentCompanyService;
use App\Services\InvoicePrefixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstimateService
{
    protected PlatformFeeService $platformFeeService;

    protected InvoicePrefixService $prefixService;

    protected InvoiceService $invoiceService;

    public function __construct(PlatformFeeService $platformFeeService, InvoicePrefixService $prefixService, InvoiceService $invoiceService)
    {
        $this->platformFeeService = $platformFeeService;
        $this->prefixService = $prefixService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Create a new estimate
     */
    public function createEstimate(Request $request): Estimate
    {
        $user = $request->user();
        $companyId = CurrentCompanyService::requireId();

        // Validate client belongs to same company (if provided)
        $clientId = $request->input('client_id');
        if ($clientId) {
            $client = Client::where('id', $clientId)
                ->where('company_id', $companyId)
                ->firstOrFail();
        }

        // Validate and prepare data for estimate creation
        $data = $request->only([
            'client_id',
            'issue_date',
            'expiry_date',
            'status',
            'estimate_reference',
            'po_number',
            'notes',
            'terms_and_conditions',
            'vat_registered',
            'discount',
            'discount_type',
        ]);

        // Automatically set company_id and user_id
        $data['company_id'] = $companyId;
        $data['user_id'] = $user->id;
        $data['status'] = $data['status'] ?? 'draft';

        // Get company for estimate prefix and template
        $company = Company::findOrFail($companyId);

        // Store the template_id that was selected
        $template = $company->getActiveInvoiceTemplate();
        $data['template_id'] = $template->id;

        // Generate estimate number (similar to invoice numbering)
        $useClientSpecific = (bool) $company->use_client_specific_numbering;

        $clientId = null;
        if (isset($data['client_id']) && $data['client_id'] !== null && $data['client_id'] !== '') {
            $clientId = (int) $data['client_id'];
        } elseif ($request->has('client_id') && $request->input('client_id') !== null && $request->input('client_id') !== '') {
            $clientId = (int) $request->input('client_id');
        }

        if ($clientId) {
            $data['client_id'] = $clientId;
        }

        // Generate estimate number using estimate-specific numbering
        if ($useClientSpecific && $clientId) {
            $client = Client::where('id', $clientId)
                ->where('company_id', $companyId)
                ->firstOrFail();

            // Use estimate-specific numbering (separate from invoices)
            $numberingData = $this->prefixService->generateClientEstimateNumber($company, $client);

            $data['client_sequence'] = $numberingData['client_sequence'];
            $data['estimate_number'] = $numberingData['estimate_number'];
            $data['estimate_reference'] = $numberingData['estimate_number'];

            $prefix = $this->prefixService->getActivePrefix($company);
            $data['prefix_used'] = $prefix->prefix;
            $data['serial_number'] = $numberingData['client_sequence'];
            $data['full_number'] = $numberingData['estimate_number'];
        } elseif (empty($data['estimate_reference'])) {
            // Global numbering using estimate-specific sequence
            $prefix = $this->prefixService->getActivePrefix($company);
            $serialNumber = $this->prefixService->generateNextEstimateSerialNumber($company, $prefix);
            $fullNumber = $this->prefixService->generateEstimateFullNumber($company, $prefix, $serialNumber);

            $data['prefix_used'] = $prefix->prefix;
            $data['serial_number'] = $serialNumber;
            $data['full_number'] = $fullNumber;
            $data['estimate_reference'] = $fullNumber;
        }

        // Set issue_date to today if not provided
        if (empty($data['issue_date'])) {
            $data['issue_date'] = now()->toDateString();
        }

        // Calculate totals
        $items = $request->input('items', []);
        $subtotal = 0;

        foreach ($items as $item) {
            $itemTotal = $item['total_price'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['rate'] ?? 0));
            $subtotal += $itemTotal;
        }

        // Apply discount
        $discount = $request->input('discount', 0);
        $discountType = $request->input('discount_type', 'fixed');
        $discountAmount = 0;
        if ($discount > 0) {
            if ($discountType === 'percentage') {
                $discountAmount = $subtotal * ($discount / 100);
            } else {
                $discountAmount = $discount;
            }
        }
        $subtotalAfterDiscount = max(0, $subtotal - $discountAmount);

        // Calculate VAT only if VAT registered
        $vatRegistered = $request->input('vat_registered', false);
        $vatAmount = 0;
        if ($vatRegistered) {
            $vatAmount = $subtotalAfterDiscount * 0.16; // 16% VAT
        }

        $totalBeforeFee = $subtotalAfterDiscount + $vatAmount;
        $platformFee = $totalBeforeFee * 0.03; // 3% platform fee
        $grandTotal = $totalBeforeFee + $platformFee;

        // Add calculated totals
        $data['subtotal'] = $subtotal;
        $data['discount'] = $discountAmount;
        $data['vat_amount'] = $vatAmount;
        $data['platform_fee'] = $platformFee;
        $data['grand_total'] = $grandTotal;

        // Create estimate
        $estimate = Estimate::create($data);

        // Add estimate items
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

            if ($itemModel->unit_price != $unitPrice) {
                $itemModel->update(['unit_price' => $unitPrice]);
            }

            $quantity = (float) ($item['quantity'] ?? 1);
            $calculatedTotalPrice = $quantity * $unitPrice;

            $estimate->items()->create([
                'company_id' => $companyId,
                'item_id' => $itemModel->id,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'vat_included' => $item['vat_included'] ?? false,
                'vat_rate' => $item['vat_rate'] ?? 16.00,
                'total_price' => $calculatedTotalPrice,
            ]);

            // Track service usage
            $this->trackServiceUsage($companyId, $description, $unitPrice);
        }

        // Reload items and update totals
        $estimate->load('items');
        $this->updateTotals($estimate);

        // Log client activity if client exists
        if ($estimate->client_id) {
            $client = $estimate->client;
            $activityService = app(\App\Http\Services\ClientActivityService::class);
            $estimateNumber = $estimate->full_number ?? $estimate->estimate_number ?? $estimate->estimate_reference ?? "EST-{$estimate->id}";
            $activityService->logEstimateCreated($client, $estimate->id, $estimateNumber, $user->id);
        }

        return $estimate;
    }

    /**
     * Update an existing estimate
     */
    public function updateEstimate(Estimate $estimate, Request $request): Estimate
    {
        $companyId = $estimate->company_id;
        $company = Company::findOrFail($companyId);

        // Validate client belongs to same company
        if ($request->has('client_id')) {
            $client = Client::where('id', $request->input('client_id'))
                ->where('company_id', $companyId)
                ->firstOrFail();
        }

        // Update estimate data (prefix fields are immutable)
        $data = $request->only([
            'client_id',
            'issue_date',
            'expiry_date',
            'status',
            'po_number',
            'notes',
            'terms_and_conditions',
        ]);

        // Prevent updating prefix fields if estimate_number is already set
        if (! empty($estimate->estimate_number)) {
            unset($data['prefix_used'], $data['serial_number'], $data['full_number'], $data['estimate_reference'], $data['client_sequence'], $data['estimate_number']);
        }

        // Update items if provided
        if ($request->has('items')) {
            // Delete existing items
            $estimate->items()->delete();

            // Calculate totals
            $items = $request->input('items', []);
            $subtotal = 0;

            foreach ($items as $item) {
                $itemTotal = $item['total_price'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? $item['rate'] ?? 0));
                $subtotal += $itemTotal;
            }

            // Apply discount if present
            $discount = $request->input('discount', $estimate->discount ?? 0);
            $discountType = $request->input('discount_type', $estimate->discount_type ?? 'fixed');
            $discountAmount = 0;

            if ($discount > 0) {
                if ($discountType === 'percentage') {
                    $discountAmount = $subtotal * ($discount / 100);
                } else {
                    $discountAmount = $discount;
                }
            }

            $subtotalAfterDiscount = max(0, $subtotal - $discountAmount);

            // Calculate VAT only if VAT registered
            $vatRegistered = $request->input('vat_registered', $estimate->vat_registered ?? false);
            $vatAmount = 0;
            if ($vatRegistered) {
                $vatAmount = $subtotalAfterDiscount * 0.16;
            }

            $totalBeforeFee = $subtotalAfterDiscount + $vatAmount;
            $platformFee = $totalBeforeFee * 0.03;
            $grandTotal = $totalBeforeFee + $platformFee;

            $data['subtotal'] = $subtotal;
            $data['discount'] = $discountAmount;
            $data['vat_amount'] = $vatAmount;
            $data['platform_fee'] = $platformFee;
            $data['grand_total'] = $grandTotal;

            // Create new items
            foreach ($items as $item) {
                $description = $item['description'];
                $unitPrice = $item['unit_price'] ?? $item['rate'] ?? 0;

                $itemModel = Item::firstOrCreate(
                    [
                        'company_id' => $companyId,
                        'name' => $description,
                    ],
                    [
                        'unit_price' => $unitPrice,
                    ]
                );

                $quantity = (float) ($item['quantity'] ?? 1);
                $calculatedTotalPrice = $quantity * $unitPrice;

                $estimate->items()->create([
                    'company_id' => $companyId,
                    'item_id' => $itemModel->id,
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'vat_included' => $item['vat_included'] ?? false,
                    'vat_rate' => $item['vat_rate'] ?? 16.00,
                    'total_price' => $calculatedTotalPrice,
                ]);

                $this->trackServiceUsage($companyId, $description, $unitPrice);
            }
        }

        $estimate->update($data);

        // Reload items and update totals
        $estimate->load('items');
        $this->updateTotals($estimate);

        return $estimate;
    }

    /**
     * Convert estimate to invoice
     */
    public function convertToInvoice(Estimate $estimate): Invoice
    {
        if ($estimate->isConverted()) {
            throw new \RuntimeException('Estimate has already been converted to an invoice.');
        }

        $user = auth()->user();

        // Prepare items in the format expected by InvoiceService
        $items = $estimate->items->map(function ($estimateItem) {
            return [
                'description' => $estimateItem->description,
                'quantity' => $estimateItem->quantity,
                'unit_price' => $estimateItem->unit_price,
                'total_price' => $estimateItem->total_price,
                'vat_included' => $estimateItem->vat_included,
                'vat_rate' => $estimateItem->vat_rate,
            ];
        })->toArray();

        // Create a Request object with estimate data to use InvoiceService.createInvoice()
        // This ensures proper invoice number generation based on company's numbering configuration
        $request = Request::create('/app/invoices', 'POST', [
            'client_id' => $estimate->client_id,
            'issue_date' => now()->toDateString(),
            'due_date' => $estimate->expiry_date ?? now()->addDays(30)->toDateString(),
            'status' => 'draft',
            'po_number' => $estimate->po_number,
            'notes' => $estimate->notes,
            'terms_and_conditions' => $estimate->terms_and_conditions,
            'vat_registered' => $estimate->vat_registered,
            'discount' => $estimate->discount ?? 0,
            'discount_type' => $estimate->discount_type ?? 'fixed',
            'items' => $items,
        ]);

        // Set the authenticated user on the request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Use InvoiceService to create invoice with proper numbering
        $invoice = $this->invoiceService->createInvoice($request);

        // Update estimate to mark as converted
        $estimate->update([
            'status' => 'converted',
            'converted_to_invoice_id' => $invoice->id,
        ]);

        // Log client activity if client exists
        if ($estimate->client_id) {
            $client = $estimate->client;
            $activityService = app(\App\Http\Services\ClientActivityService::class);
            $estimateNumber = $estimate->full_number ?? $estimate->estimate_number ?? $estimate->estimate_reference ?? "EST-{$estimate->id}";
            $invoiceNumber = $invoice->full_number ?? $invoice->invoice_number ?? $invoice->invoice_reference ?? "INV-{$invoice->id}";
            $activityService->logEstimateConverted($client, $estimate->id, $estimateNumber, $invoice->id, $invoiceNumber, $user->id);
        }

        return $invoice;
    }

    /**
     * Update estimate totals based on items
     */
    public function updateTotals(Estimate $estimate): void
    {
        $subtotal = $estimate->items->sum(function ($item) {
            return $item->total_price;
        });

        // Apply discount if present
        $discount = $estimate->discount ?? 0;
        $discountType = $estimate->discount_type ?? 'fixed';
        $discountAmount = 0;

        if ($discount > 0) {
            if ($discountType === 'percentage') {
                $discountAmount = $subtotal * ($discount / 100);
            } else {
                $discountAmount = $discount;
            }
        }

        $subtotalAfterDiscount = max(0, $subtotal - $discountAmount);

        // Calculate VAT only if VAT registered
        $vatAmount = 0;
        if ($estimate->vat_registered) {
            $vatAmount = $subtotalAfterDiscount * 0.16; // 16% VAT
        }

        $totalBeforeFee = $subtotalAfterDiscount + $vatAmount;
        $platformFee = $totalBeforeFee * 0.03; // 3% platform fee
        $grandTotal = $totalBeforeFee + $platformFee;

        $estimate->subtotal = $subtotal;
        $estimate->discount = $discountAmount;
        $estimate->vat_amount = $vatAmount;
        $estimate->platform_fee = $platformFee;
        $estimate->grand_total = $grandTotal;
        $estimate->save();
    }

    /**
     * Format estimate for list display
     */
    public function formatEstimateForList(Estimate $estimate): array
    {
        return [
            'id' => $estimate->id,
            'estimate_number' => $estimate->full_number ?? $estimate->estimate_reference ?? "EST-{$estimate->id}",
            'status' => $estimate->status,
            'grand_total' => (float) $estimate->grand_total,
            'expiry_date' => $estimate->expiry_date?->toDateString(),
            'issue_date' => $estimate->issue_date->toDateString(),
            'client' => [
                'id' => $estimate->client?->id,
                'name' => $estimate->client?->name ?? 'N/A',
                'email' => $estimate->client?->email ?? null,
            ],
        ];
    }

    /**
     * Format estimate with full details for show view
     */
    public function formatEstimateForShow(Estimate $estimate): array
    {
        $data = [
            'id' => $estimate->id,
            'estimate_number' => $estimate->full_number ?? $estimate->estimate_reference ?? "EST-{$estimate->id}",
            'status' => $estimate->status,
            'subtotal' => (float) $estimate->subtotal,
            'vat_amount' => (float) $estimate->vat_amount,
            'platform_fee' => (float) $estimate->platform_fee,
            'grand_total' => (float) $estimate->grand_total,
            'issue_date' => $estimate->issue_date->toDateString(),
            'expiry_date' => $estimate->expiry_date?->toDateString(),
            'po_number' => $estimate->po_number,
            'notes' => $estimate->notes,
            'terms_and_conditions' => $estimate->terms_and_conditions,
            'client' => [
                'id' => $estimate->client?->id,
                'name' => $estimate->client?->name ?? 'N/A',
                'email' => $estimate->client?->email ?? null,
                'phone' => $estimate->client?->phone ?? null,
                'address' => $estimate->client?->address ?? null,
            ],
            'company' => [
                'id' => $estimate->company->id,
                'name' => $estimate->company->name,
                'logo' => $estimate->company->logo,
                'email' => $estimate->company->email,
                'phone' => $estimate->company->phone,
                'address' => $estimate->company->address,
                'kra_pin' => $estimate->company->kra_pin,
            ],
            'items' => $estimate->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                ];
            }),
            'is_converted' => $estimate->isConverted(),
            'converted_invoice_id' => $estimate->converted_to_invoice_id,
            'is_expired' => $estimate->isExpired(),
        ];

        return $data;
    }

    /**
     * Format estimate for edit view
     */
    public function formatEstimateForEdit(Estimate $estimate): array
    {
        $data = $this->formatEstimateForShow($estimate);

        return $data;
    }

    /**
     * Get estimate statistics scoped by company
     */
    public function getEstimateStats(int $companyId): array
    {
        $query = Estimate::where('company_id', $companyId);

        return [
            'total' => (clone $query)->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'sent' => (clone $query)->where('status', 'sent')->count(),
            'accepted' => (clone $query)->where('status', 'accepted')->count(),
            'converted' => (clone $query)->where('status', 'converted')->count(),
            'total_value' => (float) (clone $query)->sum('grand_total'),
        ];
    }

    /**
     * Get service library for a company
     */
    public function getServiceLibrary(int $companyId): array
    {
        $trackedServices = Service::where('company_id', $companyId)
            ->orderBy('usage_count', 'desc')
            ->orderBy('name', 'asc')
            ->get()
            ->mapWithKeys(function ($service) {
                return [$service->name => (float) $service->default_price];
            })
            ->toArray();

        if (empty($trackedServices)) {
            // Extract from estimate items if no tracked services
            $estimateItems = EstimateItem::where('company_id', $companyId)
                ->select('description', DB::raw('AVG(unit_price) as avg_price'))
                ->groupBy('description')
                ->orderBy('description')
                ->get();

            foreach ($estimateItems as $item) {
                $trackedServices[$item->description] = (float) $item->avg_price;
            }
        }

        return $trackedServices;
    }

    /**
     * Track service usage from estimate items
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

        $service->increment('usage_count');

        $averagePrice = DB::table('estimate_items')
            ->where('company_id', $companyId)
            ->where('description', $description)
            ->avg('unit_price');

        if ($averagePrice) {
            $service->update(['default_price' => round((float) $averagePrice, 2)]);
        }
    }
}
