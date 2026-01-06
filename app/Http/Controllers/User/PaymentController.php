<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Show the form for creating a new payment.
     */
    public function create()
    {
        return view('user.payments.create');
    }

    /**
     * Display a listing of payments for the current user's active company.
     */
    public function index(Request $request)
    {
        // Use session-based active company
        $companyId = \App\Services\CurrentCompanyService::requireId();

        // Get payments scoped to active company
        $payments = Payment::where('company_id', $companyId)
            ->with(['invoice.client'])
            ->latest()
            ->paginate(15)
            ->through(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'mpesa_reference' => $payment->mpesa_reference,
                    'invoice_id' => $payment->invoice_id,
                    'invoice' => [
                        'id' => $payment->invoice->id ?? null,
                        'invoice_number' => $payment->invoice->invoice_reference ?? null,
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
        // Use session-based active company
        $companyId = \App\Services\CurrentCompanyService::requireId();

        // Ensure payment belongs to user's active company
        $payment = Payment::where('company_id', $companyId)
            ->with(['invoice.client', 'invoice.invoiceItems'])
            ->findOrFail($id);

        return view('user.payments.show', [
            'payment' => $payment,
        ]);
    }
}
