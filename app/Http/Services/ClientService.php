<?php

namespace App\Http\Services;

use App\Models\Client;

class ClientService
{
    public function getAllClients()
    {
        return Client::all();
    }

    public function getClientById($id)
    {
        return Client::findOrFail($id);
    }

    public function createClient(array $data)
    {
        return Client::create($data);
    }

    public function updateClient($id, array $data)
    {
        $client = Client::findOrFail($id);
        $client->update($data);

        return $client;
    }

    public function deleteClient($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
    }
}
