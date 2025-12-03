<?php

namespace App\Services;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberService
{
    /**
     * Normalize phone number to E.164 format (e.g., +254712345678)
     */
    public function normalize(string $phoneNumber, string $defaultCountry = 'KE'): ?string
    {
        if (empty($phoneNumber)) {
            return null;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $parsedNumber = $phoneUtil->parse($phoneNumber, $defaultCountry);

            if ($phoneUtil->isValidNumber($parsedNumber)) {
                return $phoneUtil->format($parsedNumber, PhoneNumberFormat::E164);
            }
        } catch (NumberParseException $e) {
            // If parsing fails, return original (validation should catch this)
            return $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Format phone number for display (national format)
     */
    public function formatForDisplay(string $phoneNumber, string $defaultCountry = 'KE'): ?string
    {
        if (empty($phoneNumber)) {
            return null;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $parsedNumber = $phoneUtil->parse($phoneNumber, $defaultCountry);

            if ($phoneUtil->isValidNumber($parsedNumber)) {
                return $phoneUtil->format($parsedNumber, PhoneNumberFormat::NATIONAL);
            }
        } catch (NumberParseException $e) {
            return $phoneNumber;
        }

        return $phoneNumber;
    }
}
