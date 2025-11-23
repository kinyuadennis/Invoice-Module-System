<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
            'name',
            'sku',
            'price',
            'stock',
            'description',
            'category_id',
        ];
    
        public function category()
        {
            return $this->belongsTo(ProductCategory::class);
        }
    
        public function orderItems()
        {
            return $this->hasMany(OrderItem::class);
        }
}
