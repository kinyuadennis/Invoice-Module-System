<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'title',
        'content',
        'rating',
        'approved',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'rating' => 'integer',
    ];

    /**
     * Get the user who wrote the review (if any).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company associated with the review (if any).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    /**
     * Scope a query to only include pending reviews.
     */
    public function scopePending($query)
    {
        return $query->where('approved', false);
    }
}
