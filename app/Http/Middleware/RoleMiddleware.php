<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Retrieve roles from route middleware parameters
        $roles = is_array($request->route()->parameter('roles'))
            ? $request->route()->parameter('roles')
            : explode(',', $request->route()->parameter('roles'));

        if (!$user || !in_array($user->role, $roles)) {
            abort(403, 'Unauthorized');
        }
        return $next($request);
}
}