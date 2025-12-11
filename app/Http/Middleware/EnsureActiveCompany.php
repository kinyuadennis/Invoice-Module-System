<?php

namespace App\Http\Middleware;

use App\Services\CurrentCompanyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveCompany
{
    /**
     * Handle an incoming request.
     *
     * Optimized to use CurrentCompanyService which has request-level caching.
     * This prevents multiple DB queries per request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Allow company setup and management routes
        if ($request->routeIs('company.setup', 'company.store', 'user.companies.*', 'user.company.switch')) {
            return $next($request);
        }

        // Use CurrentCompanyService which has request-level caching
        // This avoids multiple DB queries
        $company = CurrentCompanyService::get();

        if (! $company) {
            return redirect()->route('user.companies.index')
                ->with('error', 'Please select or create a company first.');
        }

        // Store validated company in request attributes for use in controllers
        $request->attributes->set('active_company', $company);

        return $next($request);
    }
}
