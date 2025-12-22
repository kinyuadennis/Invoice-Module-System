<?php

namespace App\Http\Services;

use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;

class ExpenseService
{
    /**
     * Create a new expense
     */
    public function createExpense(Request $request): Expense
    {
        $user = $request->user();
        $companyId = CurrentCompanyService::requireId();

        $data = $request->only([
            'category_id',
            'client_id',
            'invoice_id',
            'amount',
            'expense_date',
            'description',
            'notes',
            'payment_method',
            'status',
            'tax_deductible',
            'reference_number',
            'vendor_name',
        ]);

        $data['company_id'] = $companyId;
        $data['user_id'] = $user->id;

        // Set default status if not provided
        $data['status'] = $data['status'] ?? 'pending';

        // Set expense_date to today if not provided
        if (empty($data['expense_date'])) {
            $data['expense_date'] = now()->toDateString();
        }

        // Calculate tax if tax_deductible
        if ($data['tax_deductible'] ?? false) {
            // For Kenyan businesses, VAT is typically 16% on expenses
            $data['tax_amount'] = ($data['amount'] ?? 0) * 0.16;
        } else {
            $data['tax_amount'] = 0;
        }

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $receipt = $request->file('receipt');
            $path = $receipt->store('expenses/receipts', 'public');
            $data['receipt_path'] = $path;
        }

        return Expense::create($data);
    }

    /**
     * Update an existing expense
     */
    public function updateExpense(Expense $expense, Request $request): Expense
    {
        $companyId = $expense->company_id;

        $data = $request->only([
            'category_id',
            'client_id',
            'invoice_id',
            'amount',
            'expense_date',
            'description',
            'notes',
            'payment_method',
            'status',
            'tax_deductible',
            'reference_number',
            'vendor_name',
        ]);

        // Recalculate tax if amount or tax_deductible changed
        if (isset($data['amount']) || isset($data['tax_deductible'])) {
            $amount = $data['amount'] ?? $expense->amount;
            $taxDeductible = $data['tax_deductible'] ?? $expense->tax_deductible;

            if ($taxDeductible) {
                $data['tax_amount'] = $amount * 0.16;
            } else {
                $data['tax_amount'] = 0;
            }
        }

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($expense->receipt_path) {
                \Storage::disk('public')->delete($expense->receipt_path);
            }

            $receipt = $request->file('receipt');
            $path = $receipt->store('expenses/receipts', 'public');
            $data['receipt_path'] = $path;
        }

        $expense->update($data);

        return $expense;
    }

    /**
     * Format expense for list display
     */
    public function formatExpenseForList(Expense $expense): array
    {
        return [
            'id' => $expense->id,
            'expense_number' => $expense->expense_number,
            'description' => $expense->description,
            'amount' => (float) $expense->amount,
            'expense_date' => $expense->expense_date->toDateString(),
            'status' => $expense->status,
            'category' => [
                'id' => $expense->category?->id,
                'name' => $expense->category?->name ?? 'Uncategorized',
                'color' => $expense->category?->color ?? '#6B7280',
            ],
            'client' => [
                'id' => $expense->client?->id,
                'name' => $expense->client?->name ?? null,
            ],
            'payment_method' => $expense->payment_method,
            'has_receipt' => ! empty($expense->receipt_path),
        ];
    }

    /**
     * Format expense with full details for show view
     */
    public function formatExpenseForShow(Expense $expense): array
    {
        $data = $this->formatExpenseForList($expense);

        $data['notes'] = $expense->notes;
        $data['tax_deductible'] = $expense->tax_deductible;
        $data['tax_amount'] = (float) $expense->tax_amount;
        $data['reference_number'] = $expense->reference_number;
        $data['vendor_name'] = $expense->vendor_name;
        $data['receipt_path'] = $expense->receipt_path;
        $data['invoice'] = $expense->invoice ? [
            'id' => $expense->invoice->id,
            'invoice_number' => $expense->invoice->full_number ?? $expense->invoice->invoice_reference,
        ] : null;
        $data['user'] = [
            'id' => $expense->user->id,
            'name' => $expense->user->name,
        ];

        return $data;
    }

    /**
     * Format expense for edit view
     */
    public function formatExpenseForEdit(Expense $expense): array
    {
        return $this->formatExpenseForShow($expense);
    }

    /**
     * Get expense statistics scoped by company
     */
    public function getExpenseStats(int $companyId, ?string $dateRange = null): array
    {
        $query = Expense::where('company_id', $companyId);

        // Apply date range filter
        if ($dateRange) {
            $now = now();
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('expense_date', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('expense_date', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('expense_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
                    break;
                case 'quarter':
                    $query->whereBetween('expense_date', [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereBetween('expense_date', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);
                    break;
            }
        }

        return [
            'total' => (clone $query)->count(),
            'total_amount' => (float) (clone $query)->sum('amount'),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'tax_deductible_total' => (float) (clone $query)->where('tax_deductible', true)->sum('amount'),
        ];
    }

    /**
     * Get or create default categories for a company
     */
    public function getOrCreateDefaultCategories(int $companyId): array
    {
        $defaultCategories = [
            ['name' => 'Office Supplies', 'color' => '#3B82F6', 'description' => 'Office equipment and supplies'],
            ['name' => 'Travel', 'color' => '#10B981', 'description' => 'Travel and transportation expenses'],
            ['name' => 'Meals & Entertainment', 'color' => '#F59E0B', 'description' => 'Business meals and entertainment'],
            ['name' => 'Utilities', 'color' => '#EF4444', 'description' => 'Electricity, water, internet, etc.'],
            ['name' => 'Marketing', 'color' => '#8B5CF6', 'description' => 'Marketing and advertising expenses'],
            ['name' => 'Professional Services', 'color' => '#06B6D4', 'description' => 'Legal, accounting, consulting fees'],
            ['name' => 'Rent', 'color' => '#EC4899', 'description' => 'Office rent and lease payments'],
            ['name' => 'Other', 'color' => '#6B7280', 'description' => 'Other expenses'],
        ];

        $categories = [];
        foreach ($defaultCategories as $default) {
            $category = ExpenseCategory::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'name' => $default['name'],
                ],
                [
                    'description' => $default['description'],
                    'color' => $default['color'],
                    'is_default' => true,
                    'sort_order' => 0,
                ]
            );
            $categories[] = $category;
        }

        return $categories;
    }
}
