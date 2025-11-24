<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InvoiceService;
use App\Http\Requests\StoreInvoiceRequest;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function store(StoreInvoiceRequest $request)
{
    $invoice = $this->invoiceService->createInvoice($request);
    return response()->json(['invoice' => $invoice], 201);
}

}
