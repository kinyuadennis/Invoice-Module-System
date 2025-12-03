<?php

namespace App\Http\Requests;

use App\Rules\KraPin;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
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
        $clientId = $this->route('id') ?? $this->route('client');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('clients', 'email')
                    ->where('company_id', $companyId)
                    ->ignore($clientId),
            ],
            'phone' => ['nullable', new PhoneNumber, 'max:20'],
            'address' => 'nullable|string|max:500',
            'kra_pin' => ['nullable', new KraPin, 'max:11'],
        ];
    }
}
