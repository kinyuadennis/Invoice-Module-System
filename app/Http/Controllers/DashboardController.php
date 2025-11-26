<?php

namespace App\Http\Controllers;

use App\Http\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboardService)
    {
        $data = $dashboardService->getDashboardData();

        return Inertia::render('Dashboard', $data);
    }
}
