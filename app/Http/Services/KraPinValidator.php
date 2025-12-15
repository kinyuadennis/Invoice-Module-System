<?php

namespace App\Http\Services;

/**
 * KraPinValidator Service
 *
 * Validates and sanitizes KRA PINs for ETIMS compliance.
 * Kenyan KRA PIN format:
 * - Individual: P-XXXXX-XXXXX-X (e.g., P051234567A)
 * - Company: A-XXXXX-XXXXX-X (e.g., A051234567A)
 * - Format: [Letter]-[5 digits]-[5 digits]-[1 alphanumeric]
 */
class KraPinValidator
{
    /**
     * Validate KRA PIN format.
     *
     * @param  string|null  $kraPin  KRA PIN to validate
     * @return bool True if valid format
     */
    public function validateFormat(?string $kraPin): bool
    {
        if (empty($kraPin)) {
            return false;
        }

        // Sanitize first
        $sanitized = $this->sanitize($kraPin);

        // KRA PIN pattern: Letter-5digits-5digits-1alphanumeric
        // Examples: P051234567A, A051234567A
        $pattern = '/^[A-Z]\d{5}\d{5}[A-Z0-9]$/';

        return (bool) preg_match($pattern, $sanitized);
    }

    /**
     * Sanitize KRA PIN by removing spaces and hyphens, converting to uppercase.
     *
     * @param  string  $kraPin  KRA PIN to sanitize
     * @return string Sanitized KRA PIN
     */
    public function validateAndSanitize(string $kraPin): string
    {
        // Remove spaces, hyphens, and convert to uppercase
        $sanitized = strtoupper(preg_replace('/[\s\-]/', '', trim($kraPin)));

        return $sanitized;
    }

    /**
     * Alias for validateAndSanitize (for consistency).
     *
     * @param  string  $kraPin  KRA PIN to sanitize
     * @return string Sanitized KRA PIN
     */
    public function sanitize(string $kraPin): string
    {
        return $this->validateAndSanitize($kraPin);
    }

    /**
     * Validate KRA PIN and return result with sanitized value.
     *
     * @param  string|null  $kraPin  KRA PIN to validate
     * @return array Validation result with 'valid', 'sanitized', and 'errors'
     */
    public function validate(?string $kraPin): array
    {
        if (empty($kraPin)) {
            return [
                'valid' => false,
                'sanitized' => null,
                'errors' => ['KRA PIN is required for ETIMS export.'],
            ];
        }

        $sanitized = $this->sanitize($kraPin);
        $isValid = $this->validateFormat($sanitized);

        $errors = [];
        if (! $isValid) {
            $errors[] = 'KRA PIN format is invalid. Expected format: P-XXXXX-XXXXX-X or A-XXXXX-XXXXX-X (e.g., P051234567A).';
        }

        return [
            'valid' => $isValid,
            'sanitized' => $sanitized,
            'errors' => $errors,
        ];
    }
}
