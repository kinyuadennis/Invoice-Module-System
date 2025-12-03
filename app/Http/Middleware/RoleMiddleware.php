<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        // If user is not authenticated, redirect to login
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Check if user has a role
        if (empty($user->role)) {
            abort(403, 'User role not set. Please contact an administrator.');
        }

        // If user doesn't have required role, return 403 with helpful message
        if (! in_array($user->role, $roles)) {
            abort(403, "Unauthorized. Required role: " . implode(', ', $roles) . ". Your role: " . ($user->role ?? 'none'));
        }

        return $next($request);
    }
}
