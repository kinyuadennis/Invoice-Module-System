<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CreditNote extends Model
{
    protected $fillable = [
        'uuid',
        'company_id',
        'template_id',
        'invoice_id',
        'client_id',
        'user_id',
        'credit_note_reference',
        'prefix_used',
        'serial_number',
        'credit_note_number',
        'full_number',
        'status',
        'reason',
        'reason_details',
        'issue_date',
        'applied_date',
        'subtotal',
        'vat_amount',
        'platform_fee',
        'total_credit',
        'etims_control_number',
        'etims_qr_code',
        'etims_submitted_at',
        'etims_metadata',
        'etims_reversal_reference',
        'etims_status',
        'applied_to_invoice_id',
        'applied_amount',
        'remaining_credit',
        'notes',
        'terms_and_conditions',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'applied_date' => 'date',
            'subtotal' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'total_credit' => 'decimal:2',
            'applied_amount' => 'decimal:2',
            'remaining_credit' => 'decimal:2',
            'etims_submitted_at' => 'datetime',
            'etims_metadata' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($creditNote) {
            if (empty($creditNote->uuid)) {
                $creditNote->uuid = (string) Str::uuid();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    public function appliedToInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'applied_to_invoice_id');
    }

    public function isApplied(): bool
    {
        return $this->status === 'applied' && $this->applied_to_invoice_id !== null;
    }

    public function hasRemainingCredit(): bool
    {
        return $this->remaining_credit > 0;
    }

    public function canApplyToInvoice(): bool
    {
        return $this->status === 'issued' && $this->hasRemainingCredit();
    }
}
