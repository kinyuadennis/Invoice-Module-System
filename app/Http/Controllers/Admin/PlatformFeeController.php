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

        $query = PlatformFee::with(['invoice.client', 'invoice.user', 'invoice.company', 'company']);

        // Company filter
        if ($request->has('company_id') && $request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        $fees = $query->when($status, function ($query, $status) {
            return $query->where('fee_status', $status);
        })
            ->latest()
            ->paginate(15)
            ->through(function ($fee) {
                return [
                    'id' => $fee->id,
                    'invoice_id' => $fee->invoice_id,
                    'invoice' => [
                        'invoice_reference' => $fee->invoice->invoice_reference ?? null,
                        'invoice_number' => $fee->invoice->invoice_number ?? 'N/A',
                        'client' => ['name' => $fee->invoice->client->name ?? 'Unknown'],
                        'user' => ['name' => $fee->invoice->user->name ?? 'Unknown'],
                        'company' => $fee->invoice->company ? [
                            'id' => $fee->invoice->company->id,
                            'name' => $fee->invoice->company->name,
                        ] : null,
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

        $companies = \App\Models\Company::orderBy('name')->get(['id', 'name']);

        return view('admin.platform-fees.index', [
            'fees' => $fees,
            'stats' => $stats,
            'companies' => $companies,
            'filters' => $request->only(['status', 'company_id']),
        ]);
    }
}
