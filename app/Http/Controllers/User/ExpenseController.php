<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Services\ExpenseService;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService
    ) {}

    public function index(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $query = Expense::where('company_id', $companyId)
            ->with(['category', 'client', 'invoice', 'user'])
            ->latest('expense_date');

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('expense_number', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Date range filter
        if ($request->has('dateRange') && $request->dateRange) {
            $dateRange = $request->dateRange;
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

        $expenses = $query->paginate(15)->through(function (Expense $expense) {
            return $this->expenseService->formatExpenseForList($expense);
        });

        // Get categories for filter
        $categories = ExpenseCategory::where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('user.expenses.index', [
            'expenses' => $expenses,
            'stats' => $this->expenseService->getExpenseStats($companyId, $request->dateRange),
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'category_id', 'dateRange']),
        ]);
    }

    public function create()
    {
        $companyId = CurrentCompanyService::requireId();

        // Get or create default categories
        $this->expenseService->getOrCreateDefaultCategories($companyId);

        $categories = ExpenseCategory::where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $clients = Client::where('company_id', $companyId)
            ->select('id', 'name', 'email')
            ->get();

        return view('user.expenses.create', [
            'categories' => $categories,
            'clients' => $clients,
        ]);
    }

    public function store(StoreExpenseRequest $request)
    {
        $companyId = CurrentCompanyService::requireId();
        $expense = $this->expenseService->createExpense($request);

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense created successfully.',
                'expense_id' => $expense->id,
                'redirect' => route('user.expenses.show', $expense->id),
            ]);
        }

        return redirect()->route('user.expenses.show', $expense->id)
            ->with('success', 'Expense created successfully.');
    }

    public function show($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $expense = Expense::where('company_id', $companyId)
            ->with(['category', 'client', 'invoice', 'user'])
            ->findOrFail($id);

        return view('user.expenses.show', [
            'expense' => $this->expenseService->formatExpenseForShow($expense),
        ]);
    }

    public function edit($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $expense = Expense::where('company_id', $companyId)
            ->with(['category', 'client', 'invoice'])
            ->findOrFail($id);

        $categories = ExpenseCategory::where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $clients = Client::where('company_id', $companyId)
            ->select('id', 'name', 'email')
            ->get();

        return view('user.expenses.edit', [
            'expense' => $this->expenseService->formatExpenseForEdit($expense),
            'categories' => $categories,
            'clients' => $clients,
        ]);
    }

    public function update(UpdateExpenseRequest $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $expense = Expense::where('company_id', $companyId)
            ->findOrFail($id);

        $this->expenseService->updateExpense($expense, $request);

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return redirect()->route('user.expenses.show', $expense->id)
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $expense = Expense::where('company_id', $companyId)
            ->findOrFail($id);

        // Delete receipt if exists
        if ($expense->receipt_path) {
            \Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        // Clear dashboard cache
        Cache::forget("dashboard_data_{$companyId}");

        return redirect()->route('user.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
