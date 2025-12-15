<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * AuthController
 *
 * Handles API authentication using Laravel Sanctum.
 * Provides token-based authentication for API access.
 */
class AuthController extends Controller
{
    /**
     * Login and issue API token.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Ensure user has a company
        if (! $user->company_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'User account is not associated with a company.',
            ], 403);
        }

        // Set active company for the user
        CurrentCompanyService::setId($user->company_id);

        // Create token with expiration (default: 1 year, configurable)
        $tokenExpiration = config('sanctum.expiration', 525600); // minutes
        $token = $user->createToken($request->device_name ?? 'api-token', ['*'], now()->addMinutes($tokenExpiration));

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'company_id' => $user->company_id,
                ],
            ],
        ]);
    }

    /**
     * Get authenticated user information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = $request->user();
        $companyId = CurrentCompanyService::getId();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'company_id' => $user->company_id,
                    'active_company_id' => $companyId,
                ],
            ],
        ]);
    }

    /**
     * Logout and revoke current token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }
}
