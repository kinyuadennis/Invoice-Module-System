<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Requests\ProcessPaymentRequest;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Store a new payment.
     */
    public function store(ProcessPaymentRequest $request)
    {
        $payment = $this->paymentService->processPayment($request);

        return response()->json([
            'payment' => $payment,
            'message' => 'Payment processed successfully.'
        ], 201);
    }
}
