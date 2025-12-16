<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAccessToken extends Model
{
    protected $fillable = [
        'invoice_id',
        'client_id',
        'token',
        'expires_at',
        'used_at',
        'access_count',
        'last_ip_address',
        'last_user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'access_count' => 'integer',
    ];

    /**
     * The invoice this token provides access to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The client this token is for.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Check if token is valid.
     */
    public function isValid(): bool
    {
        if ($this->used_at) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
