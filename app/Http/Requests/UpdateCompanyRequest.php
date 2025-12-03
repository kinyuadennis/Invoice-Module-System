<?php

namespace App\Http\Requests;

use App\Rules\KraPin;
use App\Rules\PhoneNumber;
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
            'phone' => ['nullable', new PhoneNumber, 'max:20'],
            'address' => 'nullable|string|max:500',
            'kra_pin' => ['nullable', new KraPin, 'max:11'],
            'invoice_prefix' => ['nullable', 'string', 'max:50', function ($attribute, $value, $fail) {
                if ($value && ! preg_match('/^[A-Za-z0-9\-_%]{1,50}$/', $value)) {
                    $fail('The prefix must be alphanumeric with optional hyphens, underscores, and placeholders (like %YYYY%), max 50 characters.');
                }
            }],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'settings' => 'nullable|array',
        ];
    }
}
