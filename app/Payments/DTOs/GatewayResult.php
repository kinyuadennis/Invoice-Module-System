<?php

namespace App\Payments\DTOs;

/**
 * Gateway Result Data Transfer Object
 *
 * Result from gateway operation (e.g., cancellation).
 * Immutable value object for type safety.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
readonly class GatewayResult
{
    /**
     * @param  bool  $success  Whether the operation was successful
     * @param  string|null  $errorMessage  Error message if operation failed
     * @param  array<string, mixed>  $metadata  Gateway-specific metadata (e.g., cancellation date)
     */
    public function __construct(
        public bool $success,
        public ?string $errorMessage = null,
        public array $metadata = [],
    ) {}
}
