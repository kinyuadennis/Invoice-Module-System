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
    ];

    protected $casts = [
        'settings' => 'array',
        'use_client_specific_numbering' => 'boolean',
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
}
