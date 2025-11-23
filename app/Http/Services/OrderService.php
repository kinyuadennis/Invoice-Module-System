<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder($request)
    {
        return DB::transaction(function () use ($request) {

            // 1. Create the order
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'subtotal'    => 0,
                'tax'         => 0,
                'discount'    => 0,
                'total'       => 0,
                'status'      => 'pending'
            ]);

            $subtotal = 0;

            // 2. Add each product item
            foreach ($request->items as $item) {

                $product = Product::find($item['product_id']);
                $quantity = $item['quantity'];
                $unit_price = $product->price;
                $total_price = $unit_price * $quantity;

                // Create the order item
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $product->id,
                    'quantity'    => $quantity,
                    'unit_price'  => $unit_price,
                    'total_price' => $total_price,
                ]);

                // Update subtotal
                $subtotal += $total_price;

                // Deduct stock
                $product->stock -= $quantity;
                $product->save();
            }

            // Taxes/discounts calculation (modify later)
            $tax = $subtotal * 0.16; // 16% VAT example
            $total = $subtotal + $tax;

            // 3. Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'tax'      => $tax,
                'total'    => $total,
            ]);

            return $order;
        });
    }
}
