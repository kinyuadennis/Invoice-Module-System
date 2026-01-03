<?php

namespace App\Providers;

use App\Services\CurrentCompanyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom route model binding for payment status routes
        // Accepts both Payment and PaymentAttempt (per blueprint)
        \Illuminate\Support\Facades\Route::bind('paymentOrAttempt', function ($value) {
            // Try Payment first
            $payment = \App\Models\Payment::find($value);
            if ($payment) {
                return $payment;
            }

            // Try PaymentAttempt
            $paymentAttempt = \App\Models\PaymentAttempt::find($value);
            if ($paymentAttempt) {
                return $paymentAttempt;
            }

            // If neither found, try by gateway_transaction_id
            $payment = \App\Models\Payment::where('gateway_transaction_id', $value)->first();
            if ($payment) {
                return $payment;
            }

            $paymentAttempt = \App\Models\PaymentAttempt::where('gateway_transaction_id', $value)->first();
            if ($paymentAttempt) {
                return $paymentAttempt;
            }

            abort(404, 'Payment or payment attempt not found');
        });
        // Register PaymentConfirmed event listener for subscription invoice creation
        \Illuminate\Support\Facades\Event::listen(
            \App\Payments\Events\PaymentConfirmed::class,
            \App\Listeners\Payments\Listeners\CreateInvoiceOnPaymentConfirmed::class
        );

        // Register Subscription observer to sync payment records
        \App\Models\Subscription::observe(\App\Observers\SubscriptionObserver::class);

        // Share active company with all views that use layouts.user
        // This eliminates DB queries from Blade templates
        // OPTIMIZED: Only run queries if user is authenticated and has active company
        View::composer('layouts.user', function ($view) {
            // Early return if user is not authenticated
            if (! auth()->check()) {
                $view->with([
                    'activeCompany' => null,
                    'companies' => null,
                ]);

                return;
            }

            // Use CurrentCompanyService which has request-level caching
            // This ensures we only query once per request, even if called multiple times
            $activeCompany = CurrentCompanyService::get();

            // Only load companies list if we actually have an active company
            // This prevents unnecessary queries on error pages or when no company is set
            $companies = null;
            if ($activeCompany) {
                $user = auth()->user();
                // Cache companies list in view data to avoid repeated queries
                // Only load if we have an active company (optimization)
                $companies = $user->ownedCompanies()->get();
            }

            $view->with([
                'activeCompany' => $activeCompany,
                'companies' => $companies,
            ]);
        });

        // Only log slow queries in development or when explicitly enabled
        // This reduces overhead in production
        if (config('app.debug') || config('app.log_slow_queries')) {
            DB::listen(function ($query) {
                // Log queries slower than 50ms (more aggressive than Telescope's 100ms)
                if ($query->time > 50) {
                    Log::warning('Slow Query Detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time.'ms',
                        'company_id' => session('active_company_id'),
                    ]);
                }
            });
        }
    }
}
