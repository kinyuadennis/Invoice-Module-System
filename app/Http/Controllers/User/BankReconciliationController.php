<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\BankReconciliationService;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;

class BankReconciliationController extends Controller
{
    public function __construct(
        private BankReconciliationService $reconciliationService
    ) {}

    /**
     * Display a listing of reconciliations.
     */
    public function index(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $reconciliations = BankReconciliation::where('company_id', $companyId)
            ->with(['user'])
            ->latest('reconciliation_date')
            ->paginate(15);

        return view('user.bank-reconciliations.index', [
            'reconciliations' => $reconciliations,
        ]);
    }

    /**
     * Show the form for creating a new reconciliation.
     */
    public function create()
    {
        return view('user.bank-reconciliations.create');
    }

    /**
     * Store a newly created reconciliation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reconciliation_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'opening_balance' => 'required|numeric|min:0',
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $reconciliation = $this->reconciliationService->createReconciliation($validated);

        return redirect()->route('user.bank-reconciliations.show', $reconciliation->id)
            ->with('success', 'Bank reconciliation created successfully.');
    }

    /**
     * Display the specified reconciliation.
     */
    public function show($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $reconciliation = BankReconciliation::where('company_id', $companyId)
            ->with(['user', 'transactions.payment.invoice.client'])
            ->findOrFail($id);

        $summary = $this->reconciliationService->getReconciliationSummary($reconciliation);

        // Get transactions for this reconciliation period
        $transactions = BankTransaction::where('company_id', $companyId)
            ->whereBetween('transaction_date', [$reconciliation->start_date, $reconciliation->end_date])
            ->with(['payment.invoice.client'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        return view('user.bank-reconciliations.show', [
            'reconciliation' => $reconciliation,
            'summary' => $summary,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Import bank transactions.
     */
    public function importTransactions(Request $request)
    {
        $validated = $request->validate([
            'transactions' => 'required|array',
            'transactions.*.transaction_date' => 'required|date',
            'transactions.*.amount' => 'required|numeric',
            'transactions.*.reference_number' => 'nullable|string|max:255',
            'transactions.*.description' => 'nullable|string|max:500',
            'transactions.*.bank_account_name' => 'nullable|string|max:255',
            'transactions.*.bank_account_number' => 'nullable|string|max:255',
        ]);

        $imported = $this->reconciliationService->importTransactions($validated['transactions']);

        return response()->json([
            'success' => true,
            'message' => "Successfully imported {$imported} transaction(s).",
            'imported_count' => $imported,
        ]);
    }

    /**
     * Find suggested payment matches for a transaction.
     */
    public function findMatches($transactionId)
    {
        $companyId = CurrentCompanyService::requireId();

        $transaction = BankTransaction::where('company_id', $companyId)
            ->findOrFail($transactionId);

        $suggested = $this->reconciliationService->findSuggestedMatches($transaction);

        return response()->json([
            'transaction' => $transaction,
            'suggested_matches' => $suggested->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'invoice' => [
                        'id' => $payment->invoice?->id ?? null,
                        'invoice_number' => $payment->invoice?->invoice_number ?? null,
                        'client' => [
                            'name' => $payment->invoice?->client?->name ?? 'Unknown',
                        ],
                    ],
                ];
            }),
        ]);
    }

    /**
     * Match a transaction to a payment.
     */
    public function matchTransaction(Request $request, $transactionId)
    {
        $companyId = CurrentCompanyService::requireId();

        $transaction = BankTransaction::where('company_id', $companyId)
            ->findOrFail($transactionId);

        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
        ]);

        $payment = \App\Models\Payment::where('company_id', $companyId)
            ->findOrFail($validated['payment_id']);

        $matched = $this->reconciliationService->matchTransaction($transaction, $payment);

        if ($matched) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction matched successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to match transaction. It may already be matched or amounts do not match.',
        ], 422);
    }

    /**
     * Auto-match transactions for a reconciliation.
     */
    public function autoMatch($reconciliationId)
    {
        $companyId = CurrentCompanyService::requireId();

        $reconciliation = BankReconciliation::where('company_id', $companyId)
            ->findOrFail($reconciliationId);

        $result = $this->reconciliationService->autoMatchTransactions($reconciliationId);

        return response()->json([
            'success' => true,
            'message' => "Matched {$result['matched']} transaction(s). {$result['unmatched']} unmatched.",
            'matched_count' => $result['matched'],
            'unmatched_count' => $result['unmatched'],
        ]);
    }

    /**
     * Complete a reconciliation.
     */
    public function complete($reconciliationId)
    {
        $companyId = CurrentCompanyService::requireId();

        $reconciliation = BankReconciliation::where('company_id', $companyId)
            ->findOrFail($reconciliationId);

        $completed = $this->reconciliationService->completeReconciliation($reconciliationId);

        return redirect()->route('user.bank-reconciliations.show', $completed->id)
            ->with('success', 'Reconciliation completed successfully.');
    }

    /**
     * Get transactions for reconciliation.
     */
    public function getTransactions(Request $request, $reconciliationId)
    {
        $companyId = CurrentCompanyService::requireId();

        $reconciliation = BankReconciliation::where('company_id', $companyId)
            ->findOrFail($reconciliationId);

        $transactions = BankTransaction::where('company_id', $companyId)
            ->whereBetween('transaction_date', [$reconciliation->start_date, $reconciliation->end_date])
            ->with(['payment.invoice.client'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        return response()->json($transactions);
    }
}
