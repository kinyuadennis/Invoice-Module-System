<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Services\ClientService;
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

        return view('admin.clients.index', [
            'clients' => $clients,
        ]);
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(StoreClientRequest $request)
    {
        $client = $this->clientService->createClient($request->validated());

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function show($id)
    {
        $client = Client::with('invoices')->findOrFail($id);

        return view('admin.clients.show', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'invoices' => $client->invoices,
            ],
        ]);
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);

        return view('admin.clients.edit', [
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

        return redirect()->route('admin.clients.index')
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

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
