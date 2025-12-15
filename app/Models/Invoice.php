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

    /**
     * Fields that become immutable once invoice is finalized.
     * These represent financial truth and structural integrity.
     */
    protected $immutableWhenFinalized = [
        // Financial fields
        'subtotal',
        'tax',
        'vat_amount',
        'platform_fee',
        'total',
        'grand_total',
        'discount',
        // Structural fields
        'client_id',
        'issue_date',
        'due_date',
        'invoice_number',
        'invoice_reference',
        // Configuration fields
        'vat_registered',
        'payment_method',
        'payment_details',
    ];

    /**
     * Fields that can be updated even when finalized (administrative only).
     */
    protected $mutableWhenFinalized = ['status', 'notes', 'terms_and_conditions'];

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

        // Prevent modifications to finalized invoices (financial truth boundary)
        static::updating(function ($invoice) {
            if ($invoice->exists && $invoice->isFinalized()) {
                // Check if any immutable field is being modified
                foreach ($invoice->immutableWhenFinalized as $field) {
                    if ($invoice->isDirty($field)) {
                        $invoiceNumber = $invoice->invoice_number ?? $invoice->id;
                        throw new \DomainException(
                            "Cannot modify finalized invoice #{$invoiceNumber}. Field '{$field}' is immutable after finalization."
                        );
                    }
                }

                // Validate status transitions (only allow forward progression)
                if ($invoice->isDirty('status')) {
                    $currentStatus = $invoice->getOriginal('status');
                    $newStatus = $invoice->status;

                    // Allow forward transitions: finalized -> sent -> paid
                    $allowedTransitions = [
                        'finalized' => ['sent', 'paid', 'cancelled'],
                        'sent' => ['paid', 'overdue', 'cancelled'],
                        'overdue' => ['paid', 'cancelled'],
                    ];

                    if (! isset($allowedTransitions[$currentStatus]) || ! in_array($newStatus, $allowedTransitions[$currentStatus], true)) {
                        $invoiceNumber = $invoice->invoice_number ?? $invoice->id;
                        throw new \DomainException(
                            "Invalid status transition for finalized invoice #{$invoiceNumber}. Cannot change from '{$currentStatus}' to '{$newStatus}'."
                        );
                    }
                }
            }
        });

        // Prevent deletion of finalized invoices
        static::deleting(function ($invoice) {
            if ($invoice->isFinalized()) {
                $invoiceNumber = $invoice->invoice_number ?? $invoice->id;
                throw new \DomainException(
                    "Cannot delete finalized invoice #{$invoiceNumber}. Finalized invoices are immutable and cannot be deleted."
                );
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
     * The snapshot of this invoice (one-to-one relationship).
     */
    public function snapshot(): HasOne
    {
        return $this->hasOne(InvoiceSnapshot::class);
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

    /**
     * Check if invoice is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if invoice is finalized (finalized, sent, paid, or overdue).
     * Finalized means financial truth is locked - calculations frozen, structure immutable.
     */
    public function isFinalized(): bool
    {
        return in_array($this->status, ['finalized', 'sent', 'paid', 'overdue'], true);
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice can be modified.
     * Only drafts are mutable - once finalized, invoice becomes immutable.
     */
    public function isMutable(): bool
    {
        return $this->isDraft();
    }

    /**
     * Finalize the invoice - lock financial truth and structure.
     * This is the only legal doorway into finalization.
     * Once finalized, invoice becomes immutable (except status transitions and notes).
     *
     * @throws \DomainException if invoice cannot be finalized
     */
    public function finalize(): void
    {
        // Validate invoice can be finalized
        if (! $this->isDraft()) {
            $invoiceNumber = $this->invoice_number ?? $this->id;
            throw new \DomainException(
                "Invoice #{$invoiceNumber} cannot be finalized. Current status: {$this->status}"
            );
        }

        if (empty($this->invoice_number)) {
            throw new \DomainException(
                'Invoice cannot be finalized without an invoice number.'
            );
        }

        if (empty($this->client_id)) {
            throw new \DomainException(
                'Invoice cannot be finalized without a client.'
            );
        }

        // Set status to finalized
        $this->status = 'finalized';
        $this->save();
    }
}
