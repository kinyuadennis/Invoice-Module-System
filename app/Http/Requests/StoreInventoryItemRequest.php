<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemRequest extends FormRequest
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
            'item_id' => [
                'nullable',
                Rule::exists('items', 'id')->where('company_id', $companyId),
            ],
            'supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id')->where('company_id', $companyId),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('inventory_items', 'sku')->where('company_id', $companyId),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'unit_of_measure' => 'nullable|string|max:20',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0',
            'track_stock' => 'nullable|boolean',
            'auto_deduct_on_invoice' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ];
    }
}
