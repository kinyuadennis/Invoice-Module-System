<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
            'approved' => 'sometimes|boolean',
            'user_id' => 'nullable|exists:users,id',
            'company_id' => 'nullable|exists:companies,id',
        ];
    }
}
