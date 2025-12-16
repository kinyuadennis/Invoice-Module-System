<?php

namespace App\Http\Services;

use App\Models\Invoice;
use App\Models\InvoiceAccessToken;
use Illuminate\Support\Str;

class InvoiceAccessTokenService
{
    /**
     * Generate a secure access token for an invoice.
     */
    public function generateToken(Invoice $invoice, int $expiresInDays = 30): InvoiceAccessToken
    {
        // Check if a valid token already exists
        $existingToken = InvoiceAccessToken::where('invoice_id', $invoice->id)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if ($existingToken) {
            return $existingToken;
        }

        // Generate new token
        $token = InvoiceAccessToken::create([
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays($expiresInDays),
        ]);

        return $token;
    }

    /**
     * Validate and retrieve token.
     */
    public function validateToken(string $token): ?InvoiceAccessToken
    {
        $accessToken = InvoiceAccessToken::where('token', $token)->first();

        if (! $accessToken) {
            return null;
        }

        // Check if token is expired
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return null;
        }

        // Update access statistics
        $accessToken->increment('access_count');
        $accessToken->update([
            'last_ip_address' => request()->ip(),
            'last_user_agent' => request()->userAgent(),
        ]);

        return $accessToken;
    }

    /**
     * Revoke a token.
     */
    public function revokeToken(string $token): bool
    {
        $accessToken = InvoiceAccessToken::where('token', $token)->first();

        if (! $accessToken) {
            return false;
        }

        $accessToken->update([
            'used_at' => now(),
        ]);

        return true;
    }

    /**
     * Get invoice access URL.
     */
    public function getAccessUrl(InvoiceAccessToken $token): string
    {
        return route('customer.invoices.show', ['token' => $token->token]);
    }
}
