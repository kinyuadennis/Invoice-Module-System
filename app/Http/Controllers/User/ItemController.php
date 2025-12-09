<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Search items by description/name for autocomplete.
     */
    public function search(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $query = $request->input('query', '');

        if (strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        // Search items by name (description) for the user's active company
        $items = Item::forCompany($companyId)
            ->where('name', 'like', "%{$query}%")
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get(['id', 'name', 'unit_price']);

        return response()->json([
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit_price' => (float) $item->unit_price,
                ];
            }),
        ]);
    }
}
