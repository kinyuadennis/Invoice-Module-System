<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
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

        // Use StoreClientRequest for validation
        $storeRequest = new StoreClientRequest;
        $storeRequest->merge($request->all());
        $storeRequest->setUserResolver(fn () => Auth::user());

        $validated = $storeRequest->validated();

        // Normalize phone number to E.164 format
        if (isset($validated['phone']) && ! empty($validated['phone'])) {
            $phoneService = app(\App\Services\PhoneNumberService::class);
            $validated['phone'] = $phoneService->normalize($validated['phone']);
        }

        // Normalize KRA PIN to uppercase
        if (isset($validated['kra_pin']) && ! empty($validated['kra_pin'])) {
            $validated['kra_pin'] = strtoupper($validated['kra_pin']);
        }

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
