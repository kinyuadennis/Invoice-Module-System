<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClientTag extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'color',
    ];

    /**
     * The company this tag belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The clients that have this tag.
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_tag_client');
    }
}
