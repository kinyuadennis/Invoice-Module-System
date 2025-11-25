<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    Public function index(){
        $Invoices=Invoice::take(5)->get();
       
         return Inertia::render('Dashboard', [
            'Invoices' => $Invoices
         ]);
    }
    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->invoiceService->createInvoice($request);
    return response()->json(['invoice' => $invoice], 201);
}

}
