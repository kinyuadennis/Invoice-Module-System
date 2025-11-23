<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Invoice;
use App\Services\InvoiceService;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    // Generate invoice from order
    public function generate(Order $order)
    {
        // If invoice already exists, redirect to view
        if ($order->invoice) {
            return redirect()->route('invoices.show', $order->invoice->id);
        }

        $invoice = $this->invoiceService->createInvoice($order);

        return redirect()->route('invoices.show', $invoice->id);
    }

    // Show invoice
    public function show(Invoice $invoice)
    {
        return view('invoices.show', compact('invoice'));
    }
}
