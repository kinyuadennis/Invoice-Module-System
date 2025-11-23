<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
            'customer_id',
            'user_id',
            'subtotal',
            'tax',
            'discount',
            'total',
            'status'
        ];
    
        public function customer()
        {
            return $this->belongsTo(Customer::class);
        }
    
        public function items()
        {
            return $this->hasMany(OrderItem::class);
        }
    
        public function invoice()
        {
            return $this->hasOne(Invoice::class);
        }
}
