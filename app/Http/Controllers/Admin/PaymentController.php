<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of all payments (admin view).
     */
    public function index(Request $request)
    {
        $payments = Payment::with(['invoice.client', 'invoice.user'])
            ->latest()
            ->paginate(15)
            ->through(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'invoice_id' => $payment->invoice_id,
                    'invoice' => [
                        'id' => $payment->invoice->id ?? null,
                        'invoice_number' => $payment->invoice->invoice_number ?? null,
                        'client' => [
                            'name' => $payment->invoice->client->name ?? 'Unknown',
                        ],
                        'user' => [
                            'name' => $payment->invoice->user->name ?? 'Unknown',
                        ],
                    ],
                ];
            });

        return view('admin.payments.index', [
            'payments' => $payments,
        ]);
    }

    /**
     * Display the specified payment.
     */
    public function show($id)
    {
        $payment = Payment::with(['invoice.client', 'invoice.user', 'invoice.invoiceItems'])
            ->findOrFail($id);

        return view('admin.payments.show', [
            'payment' => $payment,
        ]);
    }
}
