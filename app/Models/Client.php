<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'kra_pin',
        'invoice_sequence_start',
        'next_invoice_sequence',
    ];

    protected $casts = [
        'invoice_sequence_start' => 'integer',
        'next_invoice_sequence' => 'integer',
    ];

    /**
     * The company this client belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Linked user account (optional).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All invoices for this client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Notes for this client.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ClientNote::class)->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * Tags for this client.
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ClientTag::class, 'client_tag_client');
    }

    /**
     * Activity logs for this client.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ClientActivity::class)->latest();
    }
}
