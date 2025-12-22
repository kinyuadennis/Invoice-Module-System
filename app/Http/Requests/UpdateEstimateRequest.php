<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstimateRequest extends FormRequest
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
        $estimateId = $this->route('id') ?? $this->route('estimate');

        return [
            'client_id' => [
                'nullable',
                Rule::exists('clients', 'id')->where('company_id', $companyId),
            ],
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'status' => 'nullable|in:draft,sent,accepted,rejected,expired,converted',
            'po_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'terms_and_conditions' => 'nullable|string|max:2000',
            'items' => 'nullable|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.vat_included' => 'nullable|boolean',
            'items.*.vat_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
