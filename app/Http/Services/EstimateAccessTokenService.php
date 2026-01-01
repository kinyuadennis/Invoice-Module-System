<?php

namespace App\Http\Services;

use App\Models\Estimate;
use App\Models\EstimateAccessToken;
use Illuminate\Support\Str;

class EstimateAccessTokenService
{
    /**
     * Generate a secure access token for an estimate.
     */
    public function generateToken(Estimate $estimate, int $expiresInDays = 30): EstimateAccessToken
    {
        // Check if a valid token already exists
        $existingToken = EstimateAccessToken::where('estimate_id', $estimate->id)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if ($existingToken) {
            return $existingToken;
        }

        // Generate new token
        $token = EstimateAccessToken::create([
            'estimate_id' => $estimate->id,
            'client_id' => $estimate->client_id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays($expiresInDays),
        ]);

        return $token;
    }

    /**
     * Validate and retrieve token.
     */
    public function validateToken(string $token): ?EstimateAccessToken
    {
        $accessToken = EstimateAccessToken::where('token', $token)->first();

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
        $accessToken = EstimateAccessToken::where('token', $token)->first();

        if (! $accessToken) {
            return false;
        }

        $accessToken->update([
            'used_at' => now(),
        ]);

        return true;
    }

    /**
     * Get estimate access URL.
     */
    public function getAccessUrl(EstimateAccessToken $token): string
    {
        return route('customer.estimates.show', $token->token);
    }
}
