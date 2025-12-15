<?php

namespace App\Http\Middleware;

use App\Services\CurrentCompanyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureApiCompanyScope Middleware
 *
 * Ensures all API requests are scoped to the authenticated user's active company.
 * This is non-negotiable for multi-tenant security.
 */
class EnsureApiCompanyScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Ensure user has an active company
        $companyId = CurrentCompanyService::getId();

        if (! $companyId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active company found. Please set an active company.',
            ], 403);
        }

        // Store company ID in request for use in controllers
        $request->merge(['company_id' => $companyId]);

        return $next($request);
    }
}
