<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['user_id', 'name', 'email', 'phone', 'address'];

    // Linked user account (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A client can have many invoices
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
