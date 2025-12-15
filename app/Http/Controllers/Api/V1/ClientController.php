<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;

/**
 * ClientController (API)
 *
 * Handles client information via API.
 * All queries are company-scoped.
 */
class ClientController extends Controller
{
    /**
     * List clients for authenticated user's company.
     * Company-scoped automatically via middleware.
     */
    public function index(Request $request)
    {
        $companyId = $request->input('company_id'); // Set by middleware

        $query = Client::where('company_id', $companyId);

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100);
        $clients = $query->latest()->paginate($perPage);

        return ClientResource::collection($clients);
    }

    /**
     * Get single client.
     * Must belong to authenticated user's company.
     */
    public function show(Request $request, $id)
    {
        $companyId = $request->input('company_id'); // Set by middleware

        $client = Client::where('company_id', $companyId)
            ->findOrFail($id);

        return new ClientResource($client);
    }
}
