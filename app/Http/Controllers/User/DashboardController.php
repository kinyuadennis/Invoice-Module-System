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
        $data = $dashboardService->getDashboardData($companyId);

        return view('user.dashboard.index', $data);
    }
}
