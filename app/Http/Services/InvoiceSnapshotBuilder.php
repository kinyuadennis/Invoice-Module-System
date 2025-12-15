<?php

namespace App\Http\Services;

use App\Models\Invoice;

/**
 * InvoiceSnapshotBuilder Service
 *
 * This service is a "camera" - it captures financial truth without side effects.
 *
 * Rules:
 * - No DB writes
 * - No status changes
 * - No side effects
 * - Accepts fully-loaded invoice
 * - Returns snapshot payload (array)
 * - Uses calculation service for totals (does not calculate itself)
 */
class InvoiceSnapshotBuilder
{
    protected InvoiceCalculationService $calculationService;

    public function __construct(InvoiceCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Build snapshot payload from a fully-loaded invoice.
     *
     * @param  Invoice  $invoice  Invoice with all relationships loaded
     * @return array Complete snapshot payload
     */
    public function build(Invoice $invoice): array
    {
        // Ensure invoice has all necessary relationships loaded
        $invoice->loadMissing([
            'company',
            'client',
            'invoiceItems',
            'platformFees',
            'template',
        ]);

        $company = $invoice->company;
        $client = $invoice->client;
        $items = $invoice->invoiceItems;
        $platformFee = $invoice->platformFees->first();
        $template = $invoice->getInvoiceTemplate();

        // Build snapshot payload according to financial truth definition
        return [
            'invoice' => $this->extractInvoiceData($invoice),
            'company' => $this->extractCompanyData($company),
            'client' => $this->extractClientData($client),
            'configuration' => $this->extractConfigurationData($invoice, $company, $platformFee),
            'items' => $this->extractItemsData($items, $invoice),
            'totals' => $this->extractTotalsData($invoice, $platformFee),
            'template' => $this->extractTemplateData($template),
            'branding' => $this->extractBrandingData($company),
            'metadata' => [
                'snapshot_taken_at' => now()->toIso8601String(),
                'snapshot_taken_by' => auth()->id(),
                'legacy_snapshot' => false,
            ],
        ];
    }

    /**
     * Extract invoice-level data.
     */
    protected function extractInvoiceData(Invoice $invoice): array
    {
        return [
            'invoice_number' => $invoice->invoice_number,
            'invoice_reference' => $invoice->invoice_reference,
            'po_number' => $invoice->po_number,
            'uuid' => $invoice->uuid,
            'issue_date' => $invoice->issue_date?->toDateString(),
            'due_date' => $invoice->due_date?->toDateString(),
            'status' => $invoice->status,
            'currency' => $invoice->company->currency ?? 'KES',
            'notes' => $invoice->notes,
            'terms_and_conditions' => $invoice->terms_and_conditions,
            'payment_method' => $invoice->payment_method,
            'payment_details' => $invoice->payment_details,
        ];
    }

    /**
     * Extract company data (snapshot at finalization time).
     */
    protected function extractCompanyData($company): array
    {
        if (! $company) {
            return [];
        }

        return [
            'id' => $company->id,
            'name' => $company->name,
            'email' => $company->email,
            'phone' => $company->phone,
            'address' => $company->address,
            'kra_pin' => $company->kra_pin,
            'registration_number' => $company->registration_number,
            'logo' => $company->logo,
        ];
    }

    /**
     * Extract client data (snapshot at finalization time).
     */
    protected function extractClientData($client): array
    {
        if (! $client) {
            return [];
        }

        return [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
            'kra_pin' => $client->kra_pin,
        ];
    }

    /**
     * Extract configuration data (rates and settings used at finalization).
     */
    protected function extractConfigurationData(Invoice $invoice, $company, $platformFee): array
    {
        // Get VAT configuration
        // Note: Currently hardcoded, but capturing what was used
        $vatRate = 16.00; // Will be replaced with company rate in Phase 2
        $vatEnabled = true; // Will be replaced with company setting in Phase 2

        // Get platform fee configuration
        // Note: Currently inconsistent, but capturing what was used
        $platformFeeRate = $platformFee ? ($platformFee->fee_rate / 100) : 0.03; // Will be replaced with company rate in Phase 2

        return [
            'vat_registered' => $invoice->vat_registered ?? false,
            'vat_rate_used' => $vatRate,
            'vat_enabled' => $vatEnabled,
            'platform_fee_rate_used' => $platformFeeRate,
            'platform_fee_enabled' => true,
            'payment_method' => $invoice->payment_method,
            'payment_details' => $invoice->payment_details,
            'payment_terms' => $company->payment_terms ?? null,
        ];
    }

    /**
     * Extract line items data.
     * Uses calculation service to get line-level breakdowns.
     */
    protected function extractItemsData($items, Invoice $invoice): array
    {
        // Prepare items for calculation service
        $calculationItems = [];
        foreach ($items as $item) {
            $calculationItems[] = [
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'vat_included' => $item->vat_included ?? false,
                'vat_rate' => $item->vat_rate ?? 16.00,
            ];
        }

        // Get company configuration (defaults for now)
        $vatEnabled = true;
        $vatRate = 16.00;
        $platformFeeEnabled = true;
        $platformFeeRate = 0.03;

        // Use calculation service to get line-level breakdowns
        $calculationResult = $this->calculationService->calculate($calculationItems, [
            'vat_enabled' => $vatEnabled,
            'vat_rate' => $vatRate,
            'vat_registered' => $invoice->vat_registered ?? false,
            'platform_fee_enabled' => $platformFeeEnabled,
            'platform_fee_rate' => $platformFeeRate,
            'discount' => 0, // Items don't have discount, invoice does
            'discount_type' => null,
        ]);

        // Map calculation service results to snapshot format
        $itemsData = [];
        foreach ($calculationResult['items'] as $index => $calculatedItem) {
            $originalItem = $items[$index];
            $itemsData[] = [
                'description' => $originalItem->description,
                'quantity' => (int) $calculatedItem['quantity'],
                'unit_price' => $calculatedItem['unit_price'],
                'total_price' => $calculatedItem['total_price'],
                'vat_included' => $calculatedItem['vat_included'],
                'vat_rate' => $calculatedItem['vat_rate'],
                'vat_amount' => $calculatedItem['vat_amount'], // From calculation service
            ];
        }

        return $itemsData;
    }

    /**
     * Extract totals data using calculation service.
     * Snapshot builder no longer "figures things out" - it records outcomes.
     */
    protected function extractTotalsData(Invoice $invoice, $platformFee): array
    {
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

        // Get company configuration (defaults for now)
        $vatEnabled = true;
        $vatRate = 16.00;
        $platformFeeEnabled = true;
        $platformFeeRate = 0.03;

        // Use calculation service to get totals (authoritative source)
        $calculationResult = $this->calculationService->calculate($calculationItems, [
            'vat_enabled' => $vatEnabled,
            'vat_rate' => $vatRate,
            'vat_registered' => $invoice->vat_registered ?? false,
            'platform_fee_enabled' => $platformFeeEnabled,
            'platform_fee_rate' => $platformFeeRate,
            'discount' => $invoice->discount ?? 0,
            'discount_type' => $invoice->discount_type ?? 'fixed',
        ]);

        // Return calculation service results (explicit values, not formulas)
        return [
            'subtotal' => $calculationResult['subtotal'],
            'discount' => $calculationResult['discount'],
            'discount_type' => $calculationResult['discount_type'],
            'subtotal_after_discount' => $calculationResult['subtotal_after_discount'],
            'vat_amount' => $calculationResult['vat_amount'],
            'tax' => $calculationResult['vat_amount'], // Alias for backward compatibility
            'platform_fee' => $calculationResult['platform_fee'],
            'platform_fee_calculation_base' => $calculationResult['platform_fee_calculation_base'],
            'total' => $calculationResult['total'],
            'grand_total' => $calculationResult['grand_total'],
        ];
    }

    /**
     * Extract template data.
     */
    protected function extractTemplateData($template): array
    {
        if (! $template) {
            return [
                'id' => null,
                'view_path' => 'invoices.templates.modern-clean',
                'name' => 'Modern Clean',
            ];
        }

        return [
            'id' => $template->id,
            'view_path' => $template->view_path,
            'name' => $template->name,
        ];
    }

    /**
     * Extract branding data (for PDF rendering).
     */
    protected function extractBrandingData($company): array
    {
        if (! $company) {
            return [
                'logo_path' => null,
                'show_software_credit' => true,
                'pdf_settings' => [
                    'show_software_credit' => true,
                ],
            ];
        }

        // Get PDF settings (will be pre-resolved in Phase 2)
        $pdfSettings = $company->getPdfSettings();

        return [
            'logo_path' => $company->logo,
            'show_software_credit' => $pdfSettings['show_software_credit'] ?? true,
            'pdf_settings' => $pdfSettings,
        ];
    }
}
