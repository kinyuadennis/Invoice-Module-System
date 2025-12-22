<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'payment_id' => 'nullable|exists:payments,id',
            'refund_date' => 'required|date',
            'refund_method' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'process_immediately' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Refund amount is required.',
            'amount.numeric' => 'Refund amount must be a valid number.',
            'amount.min' => 'Refund amount must be at least 0.01.',
            'payment_id.exists' => 'Selected payment does not exist.',
            'refund_date.required' => 'Refund date is required.',
            'refund_date.date' => 'Refund date must be a valid date.',
        ];
    }
}
