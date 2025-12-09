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

        // Allow company setup and management routes
        if ($request->routeIs('company.setup', 'company.store', 'user.companies.*', 'user.company.switch')) {
            return $next($request);
        }

        // Redirect to company setup if user doesn't have any companies
        if ($user && $user->ownedCompanies()->count() === 0) {
            return redirect()->route('company.setup')
                ->with('error', 'Please set up your company first.');
        }

        // Ensure user has an active company set
        if ($user && ! $user->active_company_id) {
            $firstCompany = $user->ownedCompanies()->first();
            if ($firstCompany) {
                $user->update(['active_company_id' => $firstCompany->id]);
            } else {
                return redirect()->route('company.setup')
                    ->with('error', 'Please set up your company first.');
            }
        }

        return $next($request);
    }
}
