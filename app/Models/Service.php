<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'default_price',
        'usage_count',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'usage_count' => 'integer',
    ];

    /**
     * The company this service belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
