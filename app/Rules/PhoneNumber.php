<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class PhoneNumber implements ValidationRule
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

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            // Try to parse the phone number, defaulting to Kenya (KE) if no country code
            $parsedNumber = $phoneUtil->parse($value, 'KE');

            // Check if the number is valid
            if (! $phoneUtil->isValidNumber($parsedNumber)) {
                // Provide more helpful error message
                $fail('The :attribute must be a valid phone number. For Kenya, use format: +254712345678 or 0712345678 (10 digits starting with 0)');
            }
        } catch (NumberParseException $e) {
            $fail('The :attribute must be a valid phone number. Example: +254712345678 or 0712345678');
        }
    }
}
