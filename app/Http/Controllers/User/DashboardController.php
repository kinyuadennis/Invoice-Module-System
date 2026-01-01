<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\DashboardService;
use App\Services\CurrentCompanyService;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboardService)
    {
        $companyId = CurrentCompanyService::requireId();

        // Scope dashboard data to current user's active company
        // statusDistribution is always fetched fresh to show current invoice data
        $data = $dashboardService->getDashboardData($companyId);

        // Add Schema facade for views
        $data['hasEstimatesTable'] = \Illuminate\Support\Facades\Schema::hasTable('estimates');
        $data['hasPaymentsTable'] = \Illuminate\Support\Facades\Schema::hasTable('payments');
        $data['hasExpensesTable'] = \Illuminate\Support\Facades\Schema::hasTable('expenses');
        $data['hasBankReconciliationsTable'] = \Illuminate\Support\Facades\Schema::hasTable('bank_reconciliations');

        // Add user companies for multi-company overview
        $data['userCompanies'] = auth()->user()->ownedCompanies()->get();

        return view('user.dashboard.index', $data);
    }
}
