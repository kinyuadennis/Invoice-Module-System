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
        $query = Payment::with(['invoice.client', 'invoice.user', 'invoice.company', 'company']);

        // Company filter
        if ($request->has('company_id') && $request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        $payments = $query->latest()
            ->paginate(15)
            ->through(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'invoice_id' => $payment->invoice_id,
                    'invoice' => $payment->invoice ? [
                        'id' => $payment->invoice->id,
                        'invoice_reference' => $payment->invoice->invoice_reference ?? null,
                        'invoice_number' => $payment->invoice->invoice_number ?? null,
                        'client' => $payment->invoice->client ? [
                            'name' => $payment->invoice->client->name,
                        ] : ['name' => 'Unknown'],
                        'user' => $payment->invoice->user ? [
                            'name' => $payment->invoice->user->name,
                        ] : ['name' => 'Unknown'],
                        'company' => $payment->invoice->company ? [
                            'id' => $payment->invoice->company->id,
                            'name' => $payment->invoice->company->name,
                        ] : null,
                    ] : null,
                ];
            });

        $companies = \App\Models\Company::orderBy('name')->get(['id', 'name']);

        return view('admin.payments.index', [
            'payments' => $payments,
            'companies' => $companies,
            'filters' => $request->only(['company_id']),
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
