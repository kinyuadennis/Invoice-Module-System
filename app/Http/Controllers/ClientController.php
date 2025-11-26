<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClientService;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Inertia\Inertia;
use App\Models\Client;

class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index()
    {
        $clients = Client::withCount('invoices')
            ->latest()
            ->paginate(15)
            ->through(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                    'invoices_count' => $client->invoices_count,
                ];
            });

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Edit');
    }

    public function store(StoreClientRequest $request)
    {
        $client = $this->clientService->createClient($request->validated());
        
        return redirect()->route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function show($id)
    {
        $client = Client::with('invoices')->findOrFail($id);
        
        return Inertia::render('Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
            ],
        ]);
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        
        return Inertia::render('Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
            ],
        ]);
    }

    public function update(UpdateClientRequest $request, $id)
    {
        $client = $this->clientService->updateClient($id, $request->validated());
        
        return redirect()->route('clients.index')
            ->with('success', 'Client updated successfully.');
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        
        // Check if client has invoices
        if ($client->invoices()->count() > 0) {
            return back()->withErrors([
                'message' => 'Cannot delete client with existing invoices.',
            ]);
        }

        $this->clientService->deleteClient($id);
        
        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
