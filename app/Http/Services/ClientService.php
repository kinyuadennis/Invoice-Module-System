<?php

namespace App\Http\Services;

use App\Models\Client;

class ClientService
{
    /**
     * Get all clients for a company
     */
    public function getAllClients(int $companyId)
    {
        return Client::where('company_id', $companyId)->get();
    }

    /**
     * Get client by ID (scoped to company)
     */
    public function getClientById(int $id, int $companyId)
    {
        return Client::where('company_id', $companyId)->findOrFail($id);
    }

    /**
     * Create a new client
     */
    public function createClient(array $data, int $companyId)
    {
        $data['company_id'] = $companyId;

        return Client::create($data);
    }

    /**
     * Update a client (scoped to company)
     */
    public function updateClient(int $id, array $data, int $companyId)
    {
        $client = Client::where('company_id', $companyId)->findOrFail($id);
        $client->update($data);

        return $client;
    }

    /**
     * Delete a client (scoped to company)
     */
    public function deleteClient(int $id, int $companyId): void
    {
        $client = Client::where('company_id', $companyId)->findOrFail($id);
        $client->delete();
    }
}
