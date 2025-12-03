<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        if (! $user || ! $user->company_id) {
            return false;
        }

        $company = \App\Models\Company::find($user->company_id);

        return $company && $company->owner_user_id === $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'kra_pin' => 'nullable|string|max:20',
            'invoice_prefix' => 'nullable|string|max:20|regex:/^[A-Za-z0-9\-_]+$/',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'settings' => 'nullable|array',
        ];
    }
}
