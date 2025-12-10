<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class InvoicePreviewService
{
    /**
     * Generate preview HTML for an invoice template with branding and advanced styling.
     *
     * @param  array<string, mixed>  $branding
     * @param  array<string, mixed>  $advancedStyling
     */
    public function generatePreview(?int $templateId, Company $company, array $branding = [], array $advancedStyling = []): string
    {
        // Get template (use provided ID or company's default)
        $template = $templateId
            ? \App\Models\InvoiceTemplate::find($templateId)
            : $company->getActiveInvoiceTemplate();

        if (! $template) {
            throw new \RuntimeException('Invoice template not found');
        }

        // Map template slug to preview template name
        $previewTemplate = $this->getPreviewTemplateName($template->slug);

        // Check if view exists
        if (! View::exists($previewTemplate)) {
            throw new \RuntimeException("Preview template view not found: {$previewTemplate}");
        }

        // Generate sample invoice data
        $invoiceData = $this->getSampleInvoiceData($company);

        // Apply branding defaults if not provided
        $branding = array_merge($company->getBrandingSettings(), $branding);

        // Apply advanced styling defaults if not provided
        $advancedStyling = array_merge($company->getAdvancedStylingSettings(), $advancedStyling);

        // Render the preview template
        return View::make($previewTemplate, [
            'invoice' => $invoiceData,
            'company' => $company,
            'branding' => $branding,
            'advancedStyling' => $advancedStyling,
        ])->render();
    }

    /**
     * Get sample invoice data for preview.
     *
     * @return array<string, mixed>
     */
    public function getSampleInvoiceData(Company $company): array
    {
        // Convert logo to web URL for browser preview
        $logoUrl = null;
        if ($company->logo) {
            $logoUrl = Storage::url($company->logo);
        }

        $template = $company->getActiveInvoiceTemplate();

        return [
            'id' => 999,
            'invoice_number' => $template->prefix.'-0001',
            'status' => 'sent',
            'issue_date' => now()->toDateString(),
            'date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 10000.00,
            'tax' => 1600.00,
            'vat_amount' => 1600.00,
            'platform_fee' => 300.00,
            'grand_total' => 11900.00,
            'total' => 11900.00,
            'company' => [
                'id' => $company->id,
                'name' => $company->name ?? 'Your Company Name',
                'logo' => $logoUrl,
                'logo_path' => $company->logo,
                'email' => $company->email ?? 'info@yourcompany.com',
                'phone' => $company->phone ?? '+254 700 000 000',
                'address' => $company->address ?? 'Nairobi, Kenya',
                'kra_pin' => $company->kra_pin ?? 'P000000000A',
            ],
            'client' => [
                'id' => 1,
                'name' => 'Sample Client Company',
                'email' => 'client@example.com',
                'phone' => '+254 700 111 111',
                'address' => '123 Client Street, Nairobi, Kenya',
            ],
            'items' => [
                [
                    'id' => 1,
                    'description' => 'Web Development Services',
                    'quantity' => 10,
                    'unit_price' => 1000.00,
                    'total_price' => 10000.00,
                    'total' => 10000.00,
                ],
                [
                    'id' => 2,
                    'description' => 'UI/UX Design Consultation',
                    'quantity' => 5,
                    'unit_price' => 2000.00,
                    'total_price' => 10000.00,
                    'total' => 10000.00,
                ],
            ],
            'notes' => 'Thank you for your business! Payment is due within 30 days.',
            'is_preview' => true,
        ];
    }

    /**
     * Map template slug to preview template view name.
     */
    protected function getPreviewTemplateName(string $slug): string
    {
        $templateMap = [
            'modern-clean' => 'invoices.previews.modern-clean-preview',
            'classic-professional' => 'invoices.previews.classic-professional-preview',
            'minimalist' => 'invoices.previews.minimalist-preview',
            'minimalist-neutral' => 'invoices.previews.minimalist-preview',
            'bold-modern' => 'invoices.previews.bold-modern-preview',
            'accent-header' => 'invoices.previews.bold-modern-preview',
        ];

        return $templateMap[$slug] ?? 'invoices.previews.modern-clean-preview';
    }

    /**
     * Apply branding CSS variables to HTML.
     *
     * @param  array<string, mixed>  $branding
     */
    public function applyBranding(string $html, array $branding): string
    {
        // Branding is already applied via CSS variables in the template
        // This method can be used for additional processing if needed
        return $html;
    }

    /**
     * Apply advanced styling options to HTML.
     *
     * @param  array<string, mixed>  $advancedStyling
     */
    public function applyAdvancedStyling(string $html, array $advancedStyling): string
    {
        if (! ($advancedStyling['enabled'] ?? false)) {
            return $html;
        }

        // Inject custom CSS if provided
        if (! empty($advancedStyling['custom_css'])) {
            $customCss = '<style>'.$advancedStyling['custom_css'].'</style>';
            $html = str_replace('</head>', $customCss.'</head>', $html);
        }

        return $html;
    }
}
