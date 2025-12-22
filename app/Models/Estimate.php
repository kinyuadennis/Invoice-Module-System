<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Estimate extends Model
{
    protected $fillable = [
        'uuid',
        'company_id',
        'template_id',
        'client_id',
        'user_id',
        'converted_to_invoice_id',
        'estimate_reference',
        'prefix_used',
        'serial_number',
        'client_sequence',
        'estimate_number',
        'full_number',
        'status',
        'issue_date',
        'expiry_date',
        'accepted_at',
        'rejected_at',
        'po_number',
        'notes',
        'terms_and_conditions',
        'vat_registered',
        'subtotal',
        'discount',
        'discount_type',
        'vat_amount',
        'platform_fee',
        'grand_total',
    ];

    protected function casts(): array
    {
        return [
            'vat_registered' => 'boolean',
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'accepted_at' => 'date',
            'rejected_at' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($estimate) {
            if (empty($estimate->uuid)) {
                $estimate->uuid = (string) Str::uuid();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
        return $this->hasMany(EstimateItem::class);
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_to_invoice_id');
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted' && $this->converted_to_invoice_id !== null;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast() && $this->status !== 'converted';
    }
}
