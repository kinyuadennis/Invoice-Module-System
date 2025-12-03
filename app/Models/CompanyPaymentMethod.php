<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPaymentMethod extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'name',
        'is_enabled',
        'sort_order',
        'bank_name',
        'account_name',
        'account_number',
        'swift_code',
        'branch_code',
        'bank_instructions',
        'mpesa_paybill',
        'mpesa_account_number',
        'mpesa_instructions',
        'payment_link',
        'merchant_id',
        'online_instructions',
        'mobile_money_provider',
        'mobile_money_number',
        'mobile_money_instructions',
        'cash_instructions',
        'clearing_days',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
        'clearing_days' => 'integer',
    ];

    /**
     * The company this payment method belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the display name for the payment method.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? match ($this->type) {
            'bank_transfer' => 'Bank Transfer',
            'mpesa' => 'MPesa',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'mobile_money' => 'Mobile Money',
            'cash' => 'Cash',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get the account number or identifier for copy-to-clipboard.
     */
    public function getAccountIdentifierAttribute(): ?string
    {
        return match ($this->type) {
            'bank_transfer' => $this->account_number,
            'mpesa' => $this->mpesa_paybill,
            'mobile_money' => $this->mobile_money_number,
            'paypal', 'stripe' => $this->payment_link,
            default => null,
        };
    }

    /**
     * Get expected clearing time description.
     */
    public function getClearingTimeDescriptionAttribute(): string
    {
        if ($this->clearing_days === 0) {
            return 'Instant';
        }

        if ($this->clearing_days === 1) {
            return '1 business day';
        }

        return "{$this->clearing_days} business days";
    }

    /**
     * Scope to get only enabled payment methods.
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
