<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->company_id !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'category_id' => [
                'nullable',
                Rule::exists('expense_categories', 'id')->where('company_id', $companyId),
            ],
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')->where('company_id', $companyId),
            ],
            'invoice_id' => [
                'nullable',
                Rule::exists('invoices', 'id')->where('company_id', $companyId),
            ],
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
            'payment_method' => 'nullable|in:cash,mpesa,bank_transfer,card,other',
            'status' => 'nullable|in:pending,approved,rejected,paid',
            'tax_deductible' => 'nullable|boolean',
            'reference_number' => 'nullable|string|max:100',
            'vendor_name' => 'nullable|string|max:255',
        ];
    }
}
