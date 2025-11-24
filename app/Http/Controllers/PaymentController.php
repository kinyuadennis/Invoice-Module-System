<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PaymentService;
use App\Http\Requests\StorePaymentRequest;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function store(StorePaymentRequest $request, Invoice $invoice)
    {
        $request->validate([
            'amount'    => 'required|numeric|min:1',
            'method'    => 'required|string',
            'reference' => 'nullable|string'
        ]);

        $this->paymentService->addPayment($invoice, $request->all());

        return back()->with('success', 'Payment added successfully.');
    }
}
