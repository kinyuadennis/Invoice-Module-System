<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    /** @use HasFactory<\Database\Factories\AlertFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'type',
        'title',
        'message',
        'link',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * Get the user this alert belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company this alert belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Mark alert as read.
     */
    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
