<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'logo',
        'email',
        'phone',
        'address',
        'kra_pin',
        'payment_terms',
        'registration_number',
        'currency',
        'timezone',
        'invoice_prefix',
        'invoice_suffix',
        'invoice_padding',
        'invoice_format',
        'invoice_template',
        'invoice_template_id',
        'default_invoice_template_id',
        'next_invoice_sequence',
        'client_invoice_format',
        'use_client_specific_numbering',
        'settings',
        'email_template_invoice_sent_subject',
        'email_template_invoice_sent_body',
        'email_template_payment_reminder_subject',
        'email_template_payment_reminder_body',
        'use_custom_email_templates',
        'reminder_days_before_due',
        'reminder_enable_email',
        'reminder_enable_sms',
        'reminder_frequency_days',
        'overdue_reminder_frequency_days',
        'reminder_send_time',
    ];

    protected $casts = [
        'settings' => 'array',
        'use_client_specific_numbering' => 'boolean',
        'use_custom_email_templates' => 'boolean',
        'reminder_enable_email' => 'boolean',
        'reminder_enable_sms' => 'boolean',
        'reminder_days_before_due' => 'integer',
        'reminder_frequency_days' => 'integer',
        'overdue_reminder_frequency_days' => 'integer',
        'reminder_send_time' => 'datetime',
    ];

    /**
     * The user who owns this company.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * All users belonging to this company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * All clients belonging to this company.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * All invoices belonging to this company.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * All payments belonging to this company.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * All platform fees belonging to this company.
     */
    public function platformFees(): HasMany
    {
        return $this->hasMany(PlatformFee::class);
    }

    /**
     * All services belonging to this company.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * All reusable items belonging to this company.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * The subscriptions for this company.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(CompanySubscription::class);
    }

    /**
     * Get the active subscription.
     */
    public function activeSubscription(): ?CompanySubscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();
    }

    /**
     * Billing history for this company.
     */
    public function billingHistory(): HasMany
    {
        return $this->hasMany(BillingHistory::class);
    }

    /**
     * All invoice prefixes for this company.
     */
    public function invoicePrefixes(): HasMany
    {
        return $this->hasMany(InvoicePrefix::class);
    }

    /**
     * Get the currently active invoice prefix for this company.
     */
    public function activeInvoicePrefix(): ?InvoicePrefix
    {
        return $this->invoicePrefixes()
            ->active()
            ->latest('started_at')
            ->first();
    }

    /**
     * All payment methods for this company.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(CompanyPaymentMethod::class);
    }

    /**
     * Get enabled payment methods for this company.
     */
    public function enabledPaymentMethods()
    {
        return $this->paymentMethods()->enabled()->ordered();
    }

    /**
     * The invoice template this company uses.
     */
    public function invoiceTemplate(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class);
    }

    /**
     * Get the active invoice template for this company.
     */
    public function getActiveInvoiceTemplate(): InvoiceTemplate
    {
        // Use default_invoice_template_id if set, otherwise fallback to invoice_template_id
        $templateId = $this->default_invoice_template_id ?? $this->invoice_template_id;

        if ($templateId && $this->invoiceTemplate) {
            return $this->invoiceTemplate;
        }

        return InvoiceTemplate::getDefault();
    }

    /**
     * Get the invoice prefix from the active template.
     */
    public function getInvoicePrefixFromTemplate(): string
    {
        $template = $this->getActiveInvoiceTemplate();

        return $template->prefix ?? 'INV';
    }

    /**
     * Get branding settings from the settings JSON column.
     *
     * @return array<string, mixed>
     */
    public function getBrandingSettings(): array
    {
        $settings = $this->settings ?? [];
        $branding = $settings['branding'] ?? [];

        return [
            'primary_color' => $branding['primary_color'] ?? '#2B6EF6',
            'secondary_color' => $branding['secondary_color'] ?? null,
            'font_family' => $branding['font_family'] ?? 'Inter',
        ];
    }

    /**
     * Get advanced styling settings from the settings JSON column.
     *
     * @return array<string, mixed>
     */
    public function getAdvancedStylingSettings(): array
    {
        $settings = $this->settings ?? [];
        $advancedStyling = $settings['advanced_styling'] ?? [];

        return [
            'enabled' => $advancedStyling['enabled'] ?? false,
            'column_widths' => $advancedStyling['column_widths'] ?? [
                'description' => 40,
                'quantity' => 15,
                'price' => 20,
                'total' => 25,
            ],
            'table_borders' => $advancedStyling['table_borders'] ?? 'thin',
            'spacing' => $advancedStyling['spacing'] ?? [
                'padding' => 16,
                'margin' => 24,
            ],
            'header_text' => $advancedStyling['header_text'] ?? null,
            'footer_text' => $advancedStyling['footer_text'] ?? null,
            'watermark_enabled' => $advancedStyling['watermark_enabled'] ?? false,
            'custom_css' => $advancedStyling['custom_css'] ?? '',
        ];
    }

    /**
     * Set a branding setting in the settings JSON column.
     */
    public function setBrandingSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        $settings['branding'] = $settings['branding'] ?? [];
        $settings['branding'][$key] = $value;
        $this->settings = $settings;
    }

    /**
     * Set an advanced styling setting in the settings JSON column.
     */
    public function setAdvancedStylingSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        $settings['advanced_styling'] = $settings['advanced_styling'] ?? [];
        $settings['advanced_styling'][$key] = $value;
        $this->settings = $settings;
    }

    /**
     * Update multiple branding settings at once.
     *
     * @param  array<string, mixed>  $brandingData
     */
    public function updateBrandingSettings(array $brandingData): void
    {
        $settings = $this->settings ?? [];
        $settings['branding'] = array_merge($settings['branding'] ?? [], $brandingData);
        $this->settings = $settings;
    }

    /**
     * Update multiple advanced styling settings at once.
     *
     * @param  array<string, mixed>  $stylingData
     */
    public function updateAdvancedStylingSettings(array $stylingData): void
    {
        $settings = $this->settings ?? [];
        $settings['advanced_styling'] = array_merge($settings['advanced_styling'] ?? [], $stylingData);
        $this->settings = $settings;
    }

    /**
     * Get PDF settings from the settings JSON column.
     *
     * @return array<string, mixed>
     */
    public function getPdfSettings(): array
    {
        $settings = $this->settings ?? [];
        $pdf = $settings['pdf'] ?? [];

        return [
            'show_software_credit' => $pdf['show_software_credit'] ?? true,
        ];
    }
}
