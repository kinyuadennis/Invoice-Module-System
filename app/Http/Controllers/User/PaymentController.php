<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments for the current user's company.
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return redirect()->route('dashboard')
                ->with('error', 'You must belong to a company to view payments.');
        }

        // Get payments scoped to company
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
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return redirect()->route('dashboard')
                ->with('error', 'You must belong to a company to view payments.');
        }

        // Ensure payment belongs to user's company
        $payment = Payment::where('company_id', $companyId)
            ->with(['invoice.client', 'invoice.invoiceItems'])
            ->findOrFail($id);

        return view('user.payments.show', [
            'payment' => $payment,
        ]);
    }
}
