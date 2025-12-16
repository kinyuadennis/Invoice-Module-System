<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingHistory;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * Display subscription plans.
     */
    public function plans()
    {
        $plans = SubscriptionPlan::active()->ordered()->get();

        return view('admin.billing.plans', [
            'plans' => $plans,
        ]);
    }

    /**
     * Display all company subscriptions.
     */
    public function subscriptions(Request $request)
    {
        $query = CompanySubscription::with(['company', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $subscriptions = $query->paginate(20);
        $companies = Company::orderBy('name')->get(['id', 'name']);

        return view('admin.billing.subscriptions', [
            'subscriptions' => $subscriptions,
            'companies' => $companies,
            'filters' => $request->only(['status', 'company_id']),
        ]);
    }

    /**
     * Display billing history.
     */
    public function history(Request $request)
    {
        $query = BillingHistory::with(['company', 'plan', 'subscription'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $billingHistory = $query->paginate(50);
        $companies = Company::orderBy('name')->get(['id', 'name']);

        return view('admin.billing.history', [
            'billingHistory' => $billingHistory,
            'companies' => $companies,
            'filters' => $request->only(['status', 'company_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show subscription details.
     */
    public function showSubscription($id)
    {
        $subscription = CompanySubscription::with(['company', 'plan', 'billingHistory'])->findOrFail($id);

        return view('admin.billing.subscription-show', [
            'subscription' => $subscription,
        ]);
    }
}
