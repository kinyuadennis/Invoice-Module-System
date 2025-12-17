<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    /** @use HasFactory<\Database\Factories\FeedbackFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'type',
        'message',
        'anonymous',
        'status',
    ];

    protected $casts = [
        'anonymous' => 'boolean',
    ];

    /**
     * Get the user who submitted the feedback (if not anonymous).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company associated with the feedback (if any).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
