<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    /**
     * Search items by description/name for autocomplete.
     */
    public function search(Request $request)
    {
        $user = Auth::user();

        if (! $user->company_id) {
            return response()->json(['error' => 'Company not found'], 403);
        }

        $query = $request->input('query', '');

        if (strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        // Search items by name (description) for the user's company
        $items = Item::forCompany($user->company_id)
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
