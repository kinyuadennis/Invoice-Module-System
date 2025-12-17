<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceSnapshot extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'invoice_id',
        'status',
        'snapshot_data',
        'triggered_by',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
