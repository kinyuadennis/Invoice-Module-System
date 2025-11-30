<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
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
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:mpesa,bank_transfer,cash',
            'payment_date' => 'required|date',
            'mpesa_reference' => 'nullable|string|max:255',
        ];
    }
}
