<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Services\ClientService;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(Request $request)
    {
        $query = Client::with('company')->withCount('invoices');

        // Company filter
        if ($request->has('company_id') && $request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        $clients = $query->latest()
            ->paginate(15)
            ->through(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                    'invoices_count' => $client->invoices_count,
                    'company' => $client->company ? [
                        'id' => $client->company->id,
                        'name' => $client->company->name,
                    ] : null,
                ];
            });

        $companies = \App\Models\Company::orderBy('name')->get(['id', 'name']);

        return view('admin.clients.index', [
            'clients' => $clients,
            'companies' => $companies,
            'filters' => $request->only(['company_id']),
        ]);
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(StoreClientRequest $request)
    {
        $validated = $request->validated();
        if (! isset($validated['company_id'])) {
            return back()->withErrors(['company_id' => 'Company is required.'])->withInput();
        }

        $client = $this->clientService->createClientForAdmin($validated);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function show($id)
    {
        $client = Client::with(['invoices', 'company'])->findOrFail($id);

        return view('admin.clients.show', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'kra_pin' => $client->kra_pin,
                'invoices' => $client->invoices,
                'company' => $client->company ? [
                    'id' => $client->company->id,
                    'name' => $client->company->name,
                ] : null,
            ],
        ]);
    }

    public function edit($id)
    {
        $client = Client::with('company')->findOrFail($id);
        $companies = \App\Models\Company::orderBy('name')->get(['id', 'name']);

        return view('admin.clients.edit', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'kra_pin' => $client->kra_pin,
                'company_id' => $client->company_id,
            ],
            'companies' => $companies,
        ]);
    }

    public function update(UpdateClientRequest $request, $id)
    {
        $client = $this->clientService->updateClientForAdmin($id, $request->validated());

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

        $this->clientService->deleteClientForAdmin($id);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
