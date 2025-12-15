<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CompanyResource
 *
 * API resource for Company model.
 * Exposes only safe company data for API responses.
 */
class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'kra_pin' => $this->kra_pin,
            'registration_number' => $this->registration_number,
            'currency' => $this->currency ?? 'KES',
            'payment_terms' => $this->payment_terms,

            // Rate configuration
            'default_vat_rate' => (float) ($this->default_vat_rate ?? 16.00),
            'vat_enabled' => (bool) ($this->vat_enabled ?? true),
            'platform_fee_rate' => (float) ($this->platform_fee_rate ?? 0.03),
            'platform_fee_enabled' => (bool) ($this->platform_fee_enabled ?? true),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
