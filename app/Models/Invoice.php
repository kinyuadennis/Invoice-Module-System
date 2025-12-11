<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $fillable = [
        'company_id',
        'template_id',
        'client_id',
        'user_id',
        'status',
        'invoice_reference',
        'prefix_used',
        'serial_number',
        'client_sequence',
        'invoice_number',
        'full_number',
        'po_number',
        'issue_date',
        'due_date',
        'payment_method',
        'payment_details',
        'notes',
        'terms_and_conditions',
        'vat_registered',
        'subtotal',
        'discount',
        'discount_type',
        'tax',
        'vat_amount',
        'platform_fee',
        'total',
        'grand_total',
        'uuid',
    ];

    /**
     * Prefix fields that should never be updated after creation.
     */
    protected $immutablePrefixFields = ['prefix_used', 'serial_number', 'full_number'];

    protected function casts(): array
    {
        return [
            'vat_registered' => 'boolean',
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Generate UUID for new invoices only
        static::creating(function ($invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = (string) Str::uuid();
            }
        });

        // Prevent updating prefix fields after invoice is created
        static::updating(function ($invoice) {
            if ($invoice->exists) {
                foreach ($invoice->immutablePrefixFields as $field) {
                    if ($invoice->isDirty($field)) {
                        // Restore original value - prefix fields are immutable
                        $invoice->{$field} = $invoice->getOriginal($field);
                    }
                }
            }
        });
    }

    /**
     * The company this invoice belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The client this invoice belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The user who created this invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The invoice items/line items belonging to this invoice.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * The payments made for this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The platform fees charged on this invoice.
     */
    public function platformFees(): HasMany
    {
        return $this->hasMany(PlatformFee::class);
    }

    /**
     * The invoice template used for this invoice.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'template_id');
    }

    /**
     * Get the template to use for this invoice (invoice's template or company's default).
     */
    public function getInvoiceTemplate(): InvoiceTemplate
    {
        if ($this->template_id && $this->template) {
            return $this->template;
        }

        // Fallback to company's selected template
        if ($this->company) {
            return $this->company->getActiveInvoiceTemplate();
        }

        // Final fallback to system default
        return InvoiceTemplate::getDefault();
    }

    /**
     * Scope for filtering invoices by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering invoices by company.
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
