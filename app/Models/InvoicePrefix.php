<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePrefix extends Model
{
    protected $fillable = [
        'company_id',
        'prefix',
        'started_at',
        'ended_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * The company this prefix belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The user who created this prefix.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only active prefixes (where ended_at is null).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Scope to get prefixes for a specific company.
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Check if this prefix is currently active.
     */
    public function isActive(): bool
    {
        return $this->ended_at === null;
    }

    /**
     * Mark this prefix as ended.
     */
    public function end(): void
    {
        $this->update(['ended_at' => now()]);
    }
}
