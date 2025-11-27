<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\PlatformFeeService;
use App\Models\PlatformFee;
use Illuminate\Http\Request;

class PlatformFeeController extends Controller
{
    protected $platformFeeService;

    public function __construct(PlatformFeeService $platformFeeService)
    {
        $this->platformFeeService = $platformFeeService;
    }

    /**
     * List all platform fees.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $fees = PlatformFee::with(['invoice.client', 'invoice.user'])
            ->when($status, function ($query, $status) {
                return $query->where('fee_status', $status);
            })
            ->latest()
            ->paginate(15)
            ->through(function ($fee) {
                return [
                    'id' => $fee->id,
                    'invoice_id' => $fee->invoice_id,
                    'invoice' => [
                        'invoice_number' => $fee->invoice->invoice_number ?? 'N/A',
                        'client' => ['name' => $fee->invoice->client->name ?? 'Unknown'],
                        'user' => ['name' => $fee->invoice->user->name ?? 'Unknown'],
                    ],
                    'fee_amount' => $fee->fee_amount,
                    'fee_status' => $fee->fee_status,
                    'created_at' => $fee->created_at,
                ];
            });

        $stats = [
            'total_collected' => PlatformFee::where('fee_status', 'paid')->sum('fee_amount'),
            'pending' => PlatformFee::where('fee_status', 'pending')->count(),
            'paid' => PlatformFee::where('fee_status', 'paid')->count(),
        ];

        return view('admin.platform-fees.index', [
            'fees' => $fees,
            'stats' => $stats,
        ]);
    }
}
