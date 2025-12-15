<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InvoiceSnapshot Model
 *
 * This model is PASSIVE ONLY - no business logic, no calculations, no mutators.
 * It exists solely to store and retrieve immutable invoice financial truth.
 *
 * Rules:
 * - No update methods (snapshots are immutable)
 * - No mutators that change meaning
 * - No calculations
 * - No business logic
 */
class InvoiceSnapshot extends Model
{
    protected $fillable = [
        'invoice_id',
        'snapshot_taken_by',
        'snapshot_data',
        'snapshot_taken_at',
        'legacy_snapshot',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'snapshot_taken_at' => 'datetime',
        'legacy_snapshot' => 'boolean',
    ];

    /**
     * The invoice this snapshot belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The user who created this snapshot.
     */
    public function takenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'snapshot_taken_by');
    }

    /**
     * Prevent updates to snapshots (they are immutable).
     */
    protected static function boot(): void
    {
        parent::boot();

        static::updating(function ($snapshot) {
            throw new \DomainException('Invoice snapshots are immutable and cannot be updated.');
        });
    }
}
