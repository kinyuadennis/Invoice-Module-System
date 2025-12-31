<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankReconciliation extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'reconciliation_date',
        'start_date',
        'end_date',
        'opening_balance',
        'closing_balance',
        'calculated_balance',
        'difference',
        'status',
        'notes',
        'metadata',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'reconciliation_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'calculated_balance' => 'decimal:2',
            'difference' => 'decimal:2',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * The company this reconciliation belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The user who created this reconciliation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The transactions in this reconciliation.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    /**
     * Check if reconciliation is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if reconciliation is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
