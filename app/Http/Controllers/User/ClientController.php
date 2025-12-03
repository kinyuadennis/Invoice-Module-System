<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Store a newly created client via AJAX
     */
    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'You must belong to a company to create clients.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'kra_pin' => 'nullable|string|max:20',
        ]);

        $validated['company_id'] = $companyId;
        $validated['user_id'] = Auth::id();

        $client = Client::create($validated);

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'kra_pin' => $client->kra_pin,
            ],
        ]);
    }

    /**
     * Search clients for autocomplete
     */
    public function search(Request $request)
    {
        $companyId = Auth::user()->company_id;

        if (! $companyId) {
            return response()->json(['success' => false, 'error' => 'You must belong to a company.'], 403);
        }

        $query = $request->input('q', '');

        $clientsQuery = Client::where('company_id', $companyId);

        // If query is provided, filter; otherwise return all clients (limited)
        if (! empty($query) && strlen($query) >= 1) {
            $clientsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            });
        }

        $clients = $clientsQuery
            ->orderBy('name', 'asc')
            ->limit(20)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                    'kra_pin' => $client->kra_pin,
                ];
            });

        return response()->json([
            'success' => true,
            'clients' => $clients,
        ]);
    }
}
