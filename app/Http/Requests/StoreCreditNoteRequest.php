<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCreditNoteRequest extends FormRequest
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
            'invoice_id' => [
                'required',
                Rule::exists('invoices', 'id')->where('company_id', $companyId),
            ],
            'reason' => 'required|in:refund,adjustment,error,cancellation,other',
            'reason_details' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'terms_and_conditions' => 'nullable|string|max:2000',
            'items' => 'nullable|array',
            'items.*.invoice_item_id' => [
                'required',
                Rule::exists('invoice_items', 'id'),
            ],
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.credit_reason' => 'nullable|in:returned,damaged,wrong_item,adjustment,other',
            'items.*.credit_reason_details' => 'nullable|string|max:500',
        ];
    }
}
