<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCreditNoteRequest extends FormRequest
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
            'reason' => 'nullable|in:refund,adjustment,error,cancellation,other',
            'reason_details' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'terms_and_conditions' => 'nullable|string|max:2000',
            'status' => 'nullable|in:draft,issued,applied,cancelled',
        ];
    }
}
