<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboardService)
    {
        // Scope dashboard data to current user
        $data = $dashboardService->getDashboardData(Auth::id());

        return view('user.dashboard.index', $data);
    }
}
