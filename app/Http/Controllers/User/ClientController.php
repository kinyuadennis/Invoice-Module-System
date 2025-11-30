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
}
