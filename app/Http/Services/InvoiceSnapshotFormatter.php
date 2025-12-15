<?php

namespace App\Http\Services;

use App\Models\InvoiceSnapshot;

/**
 * InvoiceSnapshotFormatter Service
 *
 * Formats snapshot data for PDF rendering.
 * This service converts snapshot JSON structure to the format expected by PDF templates.
 */
class InvoiceSnapshotFormatter
{
    /**
     * Format snapshot data for PDF rendering.
     * Converts snapshot structure to match formatInvoiceForShow() output format.
     *
     * @param  InvoiceSnapshot  $snapshot  Invoice snapshot
     * @return array Formatted invoice data for PDF
     */
    public function formatForPdf(InvoiceSnapshot $snapshot): array
    {
        $data = $snapshot->snapshot_data;
        $totalDiscount = (float) ($data['totals']['discount'] ?? 0);
        $discountType = $data['totals']['discount_type'] ?? null;

        // Build formatted structure matching formatInvoiceForShow() output
        $formatted = [
            'id' => $snapshot->invoice_id,
            'invoice_number' => $data['invoice']['invoice_number'] ?? null,
            'invoice_reference' => $data['invoice']['invoice_reference'] ?? null,
            'po_number' => $data['invoice']['po_number'] ?? null,
            'uuid' => $data['invoice']['uuid'] ?? null,
            'issue_date' => $data['invoice']['issue_date'] ?? null,
            'date' => $data['invoice']['issue_date'] ?? null, // Alias for backward compatibility
            'due_date' => $data['invoice']['due_date'] ?? null,
            'status' => $data['invoice']['status'] ?? 'finalized',
            'notes' => $data['invoice']['notes'] ?? null,
            'terms_and_conditions' => $data['invoice']['terms_and_conditions'] ?? null,
            'payment_method' => $data['invoice']['payment_method'] ?? null,
            'payment_details' => $data['invoice']['payment_details'] ?? null,
            'vat_registered' => $data['configuration']['vat_registered'] ?? false,
            'subtotal' => $data['totals']['subtotal'] ?? 0,
            'discount' => $data['totals']['discount'] ?? 0,
            'discount_type' => $data['totals']['discount_type'] ?? null,
            'tax' => $data['totals']['tax'] ?? $data['totals']['vat_amount'] ?? 0,
            'vat_amount' => $data['totals']['vat_amount'] ?? 0,
            'platform_fee' => $data['totals']['platform_fee'] ?? 0,
            'total' => $data['totals']['total'] ?? 0,
            'grand_total' => $data['totals']['grand_total'] ?? 0,
            'tax_rate' => $this->calculateTaxRate($data['totals']),
            'company' => $this->formatCompanyData($data['company'] ?? [], $data['branding'] ?? []),
            'client' => $this->formatClientData($data['client'] ?? []),
            'items' => $this->formatItemsData($data['items'] ?? [], $totalDiscount, $discountType),
            'payments' => [], // Payments not in snapshot (can be added later if needed)
            'amount_paid' => 0.00,
            'amount_due' => $data['totals']['grand_total'] ?? 0,
            'amount_in_words' => null, // Can be calculated if needed
        ];

        return $formatted;
    }

    /**
     * Calculate tax rate from totals (for display).
     */
    protected function calculateTaxRate(array $totals): float
    {
        $subtotal = (float) ($totals['subtotal'] ?? 0);
        $vatAmount = (float) ($totals['vat_amount'] ?? 0);

        if ($subtotal > 0) {
            return round(($vatAmount / $subtotal) * 100, 2);
        }

        return 0.00;
    }

    /**
     * Format company data from snapshot.
     */
    protected function formatCompanyData(array $companyData, array $brandingData): array
    {
        return [
            'id' => $companyData['id'] ?? null,
            'name' => $companyData['name'] ?? 'InvoiceHub',
            'email' => $companyData['email'] ?? null,
            'phone' => $companyData['phone'] ?? null,
            'address' => $companyData['address'] ?? null,
            'kra_pin' => $companyData['kra_pin'] ?? null,
            'registration_number' => $companyData['registration_number'] ?? null,
            'logo' => $companyData['logo'] ?? null,
            'logo_path' => $brandingData['logo_path'] ?? null,
            'currency' => $companyData['currency'] ?? 'KES',
            'payment_terms' => $companyData['payment_terms'] ?? null,
            'invoice_prefix' => $companyData['invoice_prefix'] ?? null,
            // PDF settings pre-resolved
            'pdf_settings' => $brandingData['pdf_settings'] ?? [],
            'show_software_credit' => $brandingData['show_software_credit'] ?? true,
        ];
    }

    /**
     * Format client data from snapshot.
     */
    protected function formatClientData(array $clientData): array
    {
        return [
            'id' => $clientData['id'] ?? null,
            'name' => $clientData['name'] ?? null,
            'email' => $clientData['email'] ?? null,
            'phone' => $clientData['phone'] ?? null,
            'address' => $clientData['address'] ?? null,
            'kra_pin' => $clientData['kra_pin'] ?? null,
        ];
    }

    /**
     * Format items data from snapshot.
     * Pre-calculates item-level discounts to avoid calculations in views.
     */
    protected function formatItemsData(array $itemsData, float $totalDiscount = 0, ?string $discountType = null): array
    {
        $formatted = [];

        foreach ($itemsData as $item) {
            $itemTotal = (float) ($item['total_price'] ?? 0);
            $itemDiscount = 0; // Default: no per-item discount

            // Calculate item discount if total discount exists
            // This is pre-calculated here to avoid calculations in views
            if ($totalDiscount > 0 && $discountType) {
                if ($discountType === 'percentage') {
                    $itemDiscount = $itemTotal * ($totalDiscount / 100);
                } else {
                    // Fixed discount: distribute evenly across items
                    $itemCount = count($itemsData);
                    $itemDiscount = $itemCount > 0 ? ($totalDiscount / $itemCount) : 0;
                }
            }

            $formatted[] = [
                'id' => null, // Items don't have IDs in snapshot
                'description' => $item['description'] ?? 'Item',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'total_price' => (float) ($item['total_price'] ?? 0),
                'total' => (float) ($item['total_price'] ?? 0), // Alias for backward compatibility
                'vat_included' => (bool) ($item['vat_included'] ?? false),
                'vat_rate' => (float) ($item['vat_rate'] ?? 16.00),
                'vat_amount' => (float) ($item['vat_amount'] ?? 0),
                'discount' => round($itemDiscount, 2), // Pre-calculated discount per item
            ];
        }

        return $formatted;
    }
}
