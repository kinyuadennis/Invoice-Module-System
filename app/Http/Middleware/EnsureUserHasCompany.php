<?php

namespace App\Http\Middleware;

use App\Services\CurrentCompanyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasCompany
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

        // Early return for non-authenticated users
        if (! $user) {
            return redirect()->route('login');
        }

        // Allow company setup, management, and onboarding routes
        if ($request->routeIs('company.setup', 'company.store', 'user.companies.*', 'user.company.switch', 'user.onboarding.*')) {
            return $next($request);
        }

        // Redirect to company setup if user doesn't have any companies
        // Use count() only once, cache the result
        if ($user->ownedCompanies()->count() === 0) {
            return redirect()->route('user.onboarding.index')
                ->with('message', 'Welcome! Let\'s set up your account.');
        }

        // Use CurrentCompanyService which has request-level caching
        // This avoids multiple DB queries that were happening before
        $company = CurrentCompanyService::get();

        if (! $company) {
            return redirect()->route('company.setup')
                ->with('error', 'Please set up your company first.');
        }

        // Store validated company in request attributes for use in controllers
        $request->attributes->set('active_company', $company);

        return $next($request);
    }
}
