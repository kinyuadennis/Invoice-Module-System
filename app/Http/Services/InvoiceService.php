<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Str;

class InvoiceService
{
    public function createInvoice(Order $order)
    {
        $invoiceNumber = $this->generateInvoiceNumber();

        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'order_id'       => $order->id,
            'customer_id'    => $order->customer_id,
            'subtotal'       => $order->subtotal,
            'tax'            => $order->tax,
            'discount'       => $order->discount ?? 0,
            'total'          => $order->total,
            'status'         => 'unpaid',
        ]);
    }

    public function generateInvoiceNumber()
    {
        $prefix = "INV-";
        $random = strtoupper(Str::random(6));
        $timestamp = now()->format('Ymd');

        return $prefix . $timestamp . "-" . $random;
    }
}
