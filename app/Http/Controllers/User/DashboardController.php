<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboardService)
    {
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return redirect()->route('dashboard')
                ->with('error', 'You must belong to a company to view the dashboard.');
        }

        // Scope dashboard data to current user's company
        $data = $dashboardService->getDashboardData($companyId);

        return view('user.dashboard.index', $data);
    }
}
