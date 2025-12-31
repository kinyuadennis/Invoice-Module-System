<?php

namespace App\Http\Services;

use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Models\Payment;
use App\Services\CurrentCompanyService;
use Illuminate\Support\Facades\DB;

class BankReconciliationService
{
    /**
     * Create a new bank reconciliation.
     *
     * @param  array<string, mixed>  $data
     */
    public function createReconciliation(array $data): BankReconciliation
    {
        $companyId = CurrentCompanyService::requireId();

        return BankReconciliation::create([
            'company_id' => $companyId,
            'user_id' => auth()->id(),
            'reconciliation_date' => $data['reconciliation_date'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'opening_balance' => $data['opening_balance'],
            'closing_balance' => $data['closing_balance'],
            'calculated_balance' => $data['calculated_balance'] ?? $data['opening_balance'],
            'difference' => $data['difference'] ?? 0,
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Import bank transactions from CSV/array.
     *
     * @param  array<int, array<string, mixed>>  $transactions
     */
    public function importTransactions(array $transactions): int
    {
        $companyId = CurrentCompanyService::requireId();
        $imported = 0;

        DB::transaction(function () use ($transactions, $companyId, &$imported) {
            foreach ($transactions as $transactionData) {
                // Skip if transaction already exists with same reference
                if (! empty($transactionData['reference_number'])) {
                    $exists = BankTransaction::where('company_id', $companyId)
                        ->where('reference_number', $transactionData['reference_number'])
                        ->exists();

                    if ($exists) {
                        continue;
                    }
                }

                BankTransaction::create([
                    'company_id' => $companyId,
                    'transaction_date' => $transactionData['transaction_date'],
                    'amount' => abs($transactionData['amount']),
                    'type' => $transactionData['amount'] >= 0 ? 'deposit' : 'withdrawal',
                    'reference_number' => $transactionData['reference_number'] ?? null,
                    'description' => $transactionData['description'] ?? null,
                    'bank_account_name' => $transactionData['bank_account_name'] ?? null,
                    'bank_account_number' => $transactionData['bank_account_number'] ?? null,
                    'status' => 'pending',
                ]);

                $imported++;
            }
        });

        return $imported;
    }

    /**
     * Match a bank transaction to a payment.
     */
    public function matchTransaction(BankTransaction $transaction, Payment $payment): bool
    {
        if ($transaction->payment_id) {
            return false; // Already matched
        }

        $companyId = CurrentCompanyService::requireId();

        // Ensure both belong to same company
        if ($transaction->company_id !== $companyId || $payment->company_id !== $companyId) {
            return false;
        }

        // Check if amount matches (within tolerance of 0.01)
        $amountDiff = abs((float) $transaction->amount - (float) $payment->amount);
        if ($amountDiff > 0.01) {
            return false;
        }

        // Match the transaction
        $transaction->update([
            'payment_id' => $payment->id,
            'status' => 'matched',
        ]);

        return true;
    }

    /**
     * Find suggested payment matches for a transaction.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Payment>
     */
    public function findSuggestedMatches(BankTransaction $transaction): \Illuminate\Database\Eloquent\Collection
    {
        $companyId = CurrentCompanyService::requireId();

        // Find unmatched payments within date range (±7 days) and similar amount
        $startDate = $transaction->transaction_date->copy()->subDays(7);
        $endDate = $transaction->transaction_date->copy()->addDays(7);
        $amountTolerance = 0.01;

        return Payment::where('company_id', $companyId)
            ->whereDoesntHave('bankTransaction')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereRaw('ABS(amount - ?) <= ?', [$transaction->amount, $amountTolerance])
            ->with(['invoice.client'])
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Auto-match transactions to payments based on amount and date.
     */
    public function autoMatchTransactions(int $reconciliationId): array
    {
        $reconciliation = BankReconciliation::findOrFail($reconciliationId);
        $companyId = CurrentCompanyService::requireId();

        if ($reconciliation->company_id !== $companyId) {
            throw new \RuntimeException('Reconciliation does not belong to current company');
        }

        $matched = 0;
        $unmatched = 0;

        // Get all pending transactions for this company in the date range
        $transactions = BankTransaction::where('company_id', $companyId)
            ->where('status', 'pending')
            ->whereBetween('transaction_date', [$reconciliation->start_date, $reconciliation->end_date])
            ->get();

        // Get all unmatched payments in the date range
        $payments = Payment::where('company_id', $companyId)
            ->whereDoesntHave('bankTransaction')
            ->whereBetween('payment_date', [$reconciliation->start_date, $reconciliation->end_date])
            ->with(['invoice'])
            ->get()
            ->keyBy('id');

        foreach ($transactions as $transaction) {
            // Find payment with matching amount and date (±3 days)
            $matchFound = false;
            $startDate = $transaction->transaction_date->copy()->subDays(3);
            $endDate = $transaction->transaction_date->copy()->addDays(3);

            foreach ($payments as $payment) {
                if ($payment->payment_date < $startDate || $payment->payment_date > $endDate) {
                    continue;
                }

                $amountDiff = abs((float) $transaction->amount - (float) $payment->amount);
                if ($amountDiff <= 0.01) {
                    $this->matchTransaction($transaction, $payment);
                    $matched++;
                    $matchFound = true;
                    $payments->forget($payment->id); // Remove from pool
                    break;
                }
            }

            if (! $matchFound) {
                $unmatched++;
            }
        }

        return [
            'matched' => $matched,
            'unmatched' => $unmatched,
        ];
    }

    /**
     * Reconcile transactions (mark as reconciled).
     *
     * @param  array<int>  $transactionIds
     */
    public function reconcileTransactions(int $reconciliationId, array $transactionIds): void
    {
        $reconciliation = BankReconciliation::findOrFail($reconciliationId);
        $companyId = CurrentCompanyService::requireId();

        if ($reconciliation->company_id !== $companyId) {
            throw new \RuntimeException('Reconciliation does not belong to current company');
        }

        BankTransaction::where('company_id', $companyId)
            ->where('bank_reconciliation_id', $reconciliationId)
            ->whereIn('id', $transactionIds)
            ->update(['status' => 'reconciled']);
    }

    /**
     * Complete a reconciliation.
     */
    public function completeReconciliation(int $reconciliationId): BankReconciliation
    {
        $reconciliation = BankReconciliation::findOrFail($reconciliationId);
        $companyId = CurrentCompanyService::requireId();

        if ($reconciliation->company_id !== $companyId) {
            throw new \RuntimeException('Reconciliation does not belong to current company');
        }

        // Calculate balances
        $transactions = BankTransaction::where('company_id', $companyId)
            ->whereBetween('transaction_date', [$reconciliation->start_date, $reconciliation->end_date])
            ->get();

        $deposits = $transactions->where('type', 'deposit')->sum('amount');
        $withdrawals = $transactions->where('type', 'withdrawal')->sum('amount');
        $calculatedBalance = (float) $reconciliation->opening_balance + $deposits - $withdrawals;
        $difference = (float) $reconciliation->closing_balance - $calculatedBalance;

        // Update reconciliation
        $reconciliation->update([
            'calculated_balance' => $calculatedBalance,
            'difference' => $difference,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Link all matched transactions to this reconciliation
        BankTransaction::where('company_id', $companyId)
            ->whereBetween('transaction_date', [$reconciliation->start_date, $reconciliation->end_date])
            ->where('status', 'matched')
            ->update(['bank_reconciliation_id' => $reconciliationId]);

        return $reconciliation->fresh();
    }

    /**
     * Get reconciliation summary.
     *
     * @return array<string, mixed>
     */
    public function getReconciliationSummary(BankReconciliation $reconciliation): array
    {
        $transactions = BankTransaction::where('company_id', $reconciliation->company_id)
            ->whereBetween('transaction_date', [$reconciliation->start_date, $reconciliation->end_date])
            ->get();

        $deposits = $transactions->where('type', 'deposit')->sum('amount');
        $withdrawals = $transactions->where('type', 'withdrawal')->sum('amount');
        $matched = $transactions->where('status', 'matched')->count();
        $unmatched = $transactions->where('status', 'pending')->count();
        $reconciled = $transactions->where('status', 'reconciled')->count();

        return [
            'total_transactions' => $transactions->count(),
            'deposits' => (float) $deposits,
            'withdrawals' => (float) $withdrawals,
            'matched_count' => $matched,
            'unmatched_count' => $unmatched,
            'reconciled_count' => $reconciled,
            'calculated_balance' => (float) $reconciliation->opening_balance + $deposits - $withdrawals,
            'difference' => (float) $reconciliation->closing_balance - ((float) $reconciliation->opening_balance + $deposits - $withdrawals),
        ];
    }
}
