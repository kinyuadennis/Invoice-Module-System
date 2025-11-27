<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments for the current user.
     */
    public function index(Request $request)
    {
        // Get payments for invoices belonging to the current user
        $payments = Payment::whereHas('invoice', function ($query) {
            $query->where('user_id', Auth::id());
        })
            ->with(['invoice.client'])
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
                    ],
                ];
            });

        return view('user.payments.index', [
            'payments' => $payments,
        ]);
    }

    /**
     * Display the specified payment.
     */
    public function show($id)
    {
        // Ensure user can only view payments for their own invoices
        $payment = Payment::whereHas('invoice', function ($query) {
            $query->where('user_id', Auth::id());
        })
            ->with(['invoice.client', 'invoice.invoiceItems'])
            ->findOrFail($id);

        return view('user.payments.show', [
            'payment' => $payment,
        ]);
    }
}
