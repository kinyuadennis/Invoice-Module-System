<?php

namespace App\Http\Services;

/**
 * KraPinValidator Service
 *
 * Validates and sanitizes KRA PINs for ETIMS compliance.
 * Kenyan KRA PIN format:
 * - Individual: P-XXXXX-XXXX-X (e.g., P051234567A = 11 characters)
 * - Company: A-XXXXX-XXXX-X (e.g., A051234567A = 11 characters)
 * - Format: [Letter]-[5 digits]-[4 digits]-[1 alphanumeric]
 * - When sanitized (hyphens removed): [Letter][9 digits][1 alphanumeric] = 11 characters total
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

        // KRA PIN pattern: Letter + 9 digits + 1 alphanumeric = 11 characters total
        // Examples: P051234567A (P + 9 digits + A), A051234567A (A + 9 digits + A)
        $pattern = '/^[A-Z]\d{9}[A-Z0-9]$/';

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
            $errors[] = 'KRA PIN format is invalid. Expected format: P-XXXXX-XXXX-X or A-XXXXX-XXXX-X (e.g., P051234567A = 11 characters when sanitized).';
        }

        return [
            'valid' => $isValid,
            'sanitized' => $sanitized,
            'errors' => $errors,
        ];
    }
}
