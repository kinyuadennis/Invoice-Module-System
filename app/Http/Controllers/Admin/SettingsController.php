<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformFee;

class SettingsController extends Controller
{
    public function index()
    {
        $platformFeeRate = config('app.platform_fee_rate', 3);
        $totalCollected = PlatformFee::where('fee_status', 'paid')->sum('fee_amount');
        $pendingFees = PlatformFee::where('fee_status', 'pending')->sum('fee_amount');
        $totalFees = PlatformFee::sum('fee_amount');

        return view('admin.settings.index', [
            'platformFeeSettings' => [
                'rate' => $platformFeeRate,
                'total_collected' => (float) $totalCollected,
                'pending' => (float) $pendingFees,
                'total' => (float) $totalFees,
            ],
        ]);
    }
}
