<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'platformFeeSettings' => [
                'rate' => 2.5, // Should come from config or database
                'total_collected' => 0, // Should be calculated
            ],
        ]);
    }
}
