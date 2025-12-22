@extends('layouts.user')

@section('title', 'Inventory Item Details')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $inventoryItem['name'] }}</h1>
            <p class="mt-1 text-sm text-gray-600">View inventory item details</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('user.inventory.edit', $inventoryItem['id']) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        </div>
    </div>

    <!-- Stock Status Alert -->
    @if($inventoryItem['is_out_of_stock'] ?? false)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-sm font-medium text-red-800">This item is out of stock!</p>
            </div>
        </div>
    @elseif($inventoryItem['is_low_stock'] ?? false)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-sm font-medium text-yellow-800">Low stock alert! Current stock is below minimum level.</p>
            </div>
        </div>
    @endif

    <!-- Item Details Card -->
    <x-card>
        <div class="space-y-6">
            <!-- Stock Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-blue-600 font-medium">Current Stock</p>
                    <p class="text-2xl font-bold text-blue-900 mt-1">
                        @if($inventoryItem['track_stock'] ?? false)
                            {{ number_format($inventoryItem['current_stock'], 2) }} {{ $inventoryItem['unit_of_measure'] ?? 'pcs' }}
                        @else
                            Not Tracked
                        @endif
                    </p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-green-600 font-medium">Minimum Stock</p>
                    <p class="text-2xl font-bold text-green-900 mt-1">{{ number_format($inventoryItem['minimum_stock'], 2) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-sm text-purple-600 font-medium">Cost Price</p>
                    <p class="text-2xl font-bold text-purple-900 mt-1">KES {{ number_format($inventoryItem['cost_price'], 2) }}</p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-4">
                    <p class="text-sm text-indigo-600 font-medium">Selling Price</p>
                    <p class="text-2xl font-bold text-indigo-900 mt-1">KES {{ number_format($inventoryItem['selling_price'], 2) }}</p>
                </div>
            </div>

            <!-- Item Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 pt-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Item Information</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">SKU:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $inventoryItem['sku'] ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Category:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $inventoryItem['category'] ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Location:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $inventoryItem['location'] ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Barcode:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $inventoryItem['barcode'] ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Unit of Measure:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $inventoryItem['unit_of_measure'] ?? 'pcs' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Supplier:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ $inventoryItem['supplier'] ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Auto-deduct on Invoice:</span>
                            <span class="font-medium text-gray-900 ml-2">{{ ($inventoryItem['auto_deduct_on_invoice'] ?? false) ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Description</h3>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $inventoryItem['description'] ?? 'No description provided.' }}</p>
                </div>
            </div>

            <!-- Stock Actions -->
            @if($inventoryItem['track_stock'] ?? false)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Stock Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Record Purchase -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Record Purchase</h4>
                            <form method="POST" action="{{ route('user.inventory.purchase', $inventoryItem['id']) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                                    <input type="number" name="quantity" step="0.01" min="0.01" required class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Unit Cost (Optional)</label>
                                    <input type="number" name="unit_cost" step="0.01" min="0" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Reference Number (Optional)</label>
                                    <input type="text" name="reference_number" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                    <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                                </div>
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                    Record Purchase
                                </button>
                            </form>
                        </div>

                        <!-- Record Adjustment -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Record Adjustment</h4>
                            <form method="POST" action="{{ route('user.inventory.adjustment', $inventoryItem['id']) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Quantity Change</label>
                                    <input type="number" name="quantity" step="0.01" required class="w-full rounded-lg border-gray-300 text-sm" placeholder="Positive to add, negative to subtract">
                                    <p class="text-xs text-gray-500 mt-1">Use positive numbers to add stock, negative to subtract</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Reason *</label>
                                    <textarea name="notes" rows="3" required class="w-full rounded-lg border-gray-300 text-sm" placeholder="Explain the reason for this adjustment..."></textarea>
                                </div>
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                    Record Adjustment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Stock Movements -->
            @if(isset($inventoryItem['recent_movements']) && count($inventoryItem['recent_movements']) > 0)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Stock Movements</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock After</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($inventoryItem['recent_movements'] as $movement)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($movement['movement_date'])->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                {{ $movement['type'] === 'purchase' ? 'bg-green-100 text-green-800' : 
                                                   ($movement['type'] === 'sale' ? 'bg-red-100 text-red-800' : 
                                                   ($movement['type'] === 'adjustment' ? 'bg-blue-100 text-blue-800' : 
                                                   ($movement['type'] === 'return' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'))) }}">
                                                {{ ucfirst($movement['type']) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium 
                                            {{ $movement['quantity'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $movement['quantity'] > 0 ? '+' : '' }}{{ number_format($movement['quantity'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600">
                                            {{ number_format($movement['stock_after'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $movement['notes'] ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </x-card>
</div>
@endsection

