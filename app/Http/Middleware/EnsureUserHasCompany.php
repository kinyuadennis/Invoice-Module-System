<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow company setup routes
        if ($request->routeIs('company.setup', 'company.store')) {
            return $next($request);
        }

        // Redirect to company setup if user doesn't have a company
        if ($user && ! $user->company_id) {
            return redirect()->route('company.setup')
                ->with('error', 'Please set up your company first.');
        }

        return $next($request);
    }
}
