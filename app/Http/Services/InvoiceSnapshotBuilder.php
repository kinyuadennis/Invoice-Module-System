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
 */
class InvoiceSnapshotBuilder
{
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
     */
    protected function extractItemsData($items, Invoice $invoice): array
    {
        $itemsData = [];

        foreach ($items as $item) {
            // Calculate VAT amount for this line item
            // This is explicit, not derived from totals
            $itemSubtotal = (float) $item->total_price;
            $itemVatAmount = 0.00;

            if ($invoice->vat_registered && $item->vat_rate) {
                if ($item->vat_included) {
                    // VAT included in price
                    $itemVatAmount = $itemSubtotal * ($item->vat_rate / (100 + $item->vat_rate));
                } else {
                    // VAT added to price
                    $itemVatAmount = $itemSubtotal * ($item->vat_rate / 100);
                }
            }

            $itemsData[] = [
                'description' => $item->description,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
                'vat_included' => (bool) $item->vat_included,
                'vat_rate' => (float) ($item->vat_rate ?? 16.00),
                'vat_amount' => round($itemVatAmount, 2),
            ];
        }

        return $itemsData;
    }

    /**
     * Extract totals data (explicit values, not formulas).
     */
    protected function extractTotalsData(Invoice $invoice, $platformFee): array
    {
        $subtotal = (float) $invoice->subtotal;
        $discount = (float) ($invoice->discount ?? 0);
        $discountType = $invoice->discount_type ?? null;

        // Calculate subtotal after discount
        $subtotalAfterDiscount = $subtotal;
        if ($discount > 0) {
            if ($discountType === 'percentage') {
                $subtotalAfterDiscount = $subtotal - ($subtotal * ($discount / 100));
            } else {
                $subtotalAfterDiscount = $subtotal - $discount;
            }
        }
        $subtotalAfterDiscount = max(0, $subtotalAfterDiscount);

        $vatAmount = (float) ($invoice->vat_amount ?? $invoice->tax ?? 0);
        $total = (float) ($invoice->total ?? ($subtotalAfterDiscount + $vatAmount));
        $platformFeeAmount = (float) ($platformFee?->fee_amount ?? $invoice->platform_fee ?? 0);
        $grandTotal = (float) ($invoice->grand_total ?? ($total + $platformFeeAmount));

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_type' => $discountType,
            'subtotal_after_discount' => round($subtotalAfterDiscount, 2),
            'vat_amount' => $vatAmount,
            'tax' => $vatAmount, // Alias for backward compatibility
            'platform_fee' => $platformFeeAmount,
            'platform_fee_calculation_base' => $total,
            'total' => $total,
            'grand_total' => $grandTotal,
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
