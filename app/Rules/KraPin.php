<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KraPin implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Allow nullable
        }

        // KRA PIN format: Letter + 9 digits + Letter (e.g., A000000000B)
        // Pattern: ^[A-Za-z]\d{9}[A-Za-z]$
        if (! preg_match('/^[A-Za-z]\d{9}[A-Za-z]$/', $value)) {
            $fail('The :attribute must be a valid KRA PIN in the format: Letter + 9 digits + Letter (e.g., A012345678B)');
        }
    }
}
