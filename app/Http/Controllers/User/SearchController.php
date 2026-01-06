<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $companyId = CurrentCompanyService::requireId();

        if (empty($query)) {
            return view('user.search.index', [
                'query' => null,
                'invoices' => collect(),
                'clients' => collect(),
            ]);
        }

        // Search Invoices
        $invoices = Invoice::where('company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('invoice_number', 'like', "%{$query}%")
                    ->orWhereHas('client', function ($cq) use ($query) {
                        $cq->where('name', 'like', "%{$query}%");
                    });
            })
            ->latest()
            ->limit(10)
            ->get();

        // Search Clients
        $clients = Client::where('company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return view('user.search.index', [
            'query' => $query,
            'invoices' => $invoices,
            'clients' => $clients,
        ]);
    }
}
