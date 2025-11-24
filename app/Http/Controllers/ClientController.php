<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClientService;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;


class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index()
    {
        $clients = $this->clientService->getAllClients();
        return response()->json($clients);
    }

    public function store(StoreClientRequest $request)
    {
        $client = $this->clientService->createClient($request->validated());
        return response()->json(['client' => $client, 'message' => 'Client created successfully'], 201);
    }

    public function show($id)
    {
        $client = $this->clientService->getClientById($id);
        return response()->json($client);
    }

    public function update(UpdateClientRequest $request, $id)
    {
        $client = $this->clientService->updateClient($id, $request->validated());
        return response()->json(['client' => $client, 'message' => 'Client updated successfully']);
    }

    public function destroy($id)
    {
        $this->clientService->deleteClient($id);
        return response()->json(['message' => 'Client deleted successfully']);
    }
}
