<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyPaymentMethodController extends Controller
{
    /**
     * Store a new payment method
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->company_id) {
            return response()->json(['success' => false, 'error' => 'You must belong to a company.'], 403);
        }

        $company = Company::findOrFail($user->company_id);

        if ($company->owner_user_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Only the company owner can manage payment methods.'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:bank_transfer,mpesa,paypal,stripe,mobile_money,cash',
            'name' => 'nullable|string|max:100',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer|min:0',
            'bank_name' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:20',
            'branch_code' => 'nullable|string|max:20',
            'bank_instructions' => 'nullable|string|max:500',
            'mpesa_paybill' => 'nullable|string|max:20',
            'mpesa_account_number' => 'nullable|string|max:50',
            'mpesa_instructions' => 'nullable|string|max:500',
            'payment_link' => 'nullable|url|max:255',
            'merchant_id' => 'nullable|string|max:100',
            'online_instructions' => 'nullable|string|max:500',
            'mobile_money_provider' => 'nullable|string|max:50',
            'mobile_money_number' => 'nullable|string|max:50',
            'mobile_money_instructions' => 'nullable|string|max:500',
            'cash_instructions' => 'nullable|string|max:500',
            'clearing_days' => 'integer|min:0|max:30',
        ]);

        $validated['company_id'] = $company->id;
        $validated['is_enabled'] = $validated['is_enabled'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['clearing_days'] = $validated['clearing_days'] ?? 0;

        $paymentMethod = CompanyPaymentMethod::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully.',
            'payment_method' => $paymentMethod,
        ]);
    }

    /**
     * Update a payment method
     */
    public function update(Request $request, CompanyPaymentMethod $paymentMethod)
    {
        $user = Auth::user();

        if (! $user->company_id || $paymentMethod->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $company = Company::findOrFail($user->company_id);

        if ($company->owner_user_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Only the company owner can manage payment methods.'], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|in:bank_transfer,mpesa,paypal,stripe,mobile_money,cash',
            'name' => 'nullable|string|max:100',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer|min:0',
            'bank_name' => 'nullable|string|max:100',
            'account_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:20',
            'branch_code' => 'nullable|string|max:20',
            'bank_instructions' => 'nullable|string|max:500',
            'mpesa_paybill' => 'nullable|string|max:20',
            'mpesa_account_number' => 'nullable|string|max:50',
            'mpesa_instructions' => 'nullable|string|max:500',
            'payment_link' => 'nullable|url|max:255',
            'merchant_id' => 'nullable|string|max:100',
            'online_instructions' => 'nullable|string|max:500',
            'mobile_money_provider' => 'nullable|string|max:50',
            'mobile_money_number' => 'nullable|string|max:50',
            'mobile_money_instructions' => 'nullable|string|max:500',
            'cash_instructions' => 'nullable|string|max:500',
            'clearing_days' => 'integer|min:0|max:30',
        ]);

        $paymentMethod->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully.',
            'payment_method' => $paymentMethod->fresh(),
        ]);
    }

    /**
     * Delete a payment method
     */
    public function destroy(CompanyPaymentMethod $paymentMethod)
    {
        $user = Auth::user();

        if (! $user->company_id || $paymentMethod->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $company = Company::findOrFail($user->company_id);

        if ($company->owner_user_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Only the company owner can manage payment methods.'], 403);
        }

        $paymentMethod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully.',
        ]);
    }

    /**
     * Reorder payment methods
     */
    public function reorder(Request $request)
    {
        $user = Auth::user();

        if (! $user->company_id) {
            return response()->json(['success' => false, 'error' => 'You must belong to a company.'], 403);
        }

        $company = Company::findOrFail($user->company_id);

        if ($company->owner_user_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Only the company owner can manage payment methods.'], 403);
        }

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:company_payment_methods,id',
        ]);

        DB::transaction(function () use ($validated, $company) {
            foreach ($validated['order'] as $index => $paymentMethodId) {
                CompanyPaymentMethod::where('id', $paymentMethodId)
                    ->where('company_id', $company->id)
                    ->update(['sort_order' => $index]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment methods reordered successfully.',
        ]);
    }
}
