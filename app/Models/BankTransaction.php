<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'company_id',
        'transaction_date',
        'amount',
        'type',
        'reference_number',
        'description',
        'bank_account_name',
        'bank_account_number',
        'status',
        'payment_id',
        'bank_reconciliation_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * The company this transaction belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The payment matched to this transaction.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * The reconciliation this transaction belongs to.
     */
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class, 'bank_reconciliation_id');
    }

    /**
     * Check if transaction is matched.
     */
    public function isMatched(): bool
    {
        return $this->status === 'matched' && $this->payment_id !== null;
    }

    /**
     * Check if transaction is reconciled.
     */
    public function isReconciled(): bool
    {
        return $this->status === 'reconciled';
    }
}
