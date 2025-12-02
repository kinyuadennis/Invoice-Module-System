@props([
    'invoice' => [
        'number' => 'INV-001',
        'client' => 'Demo Client Ltd',
        'items' => [
            ['description' => 'Consultation Services', 'quantity' => 1, 'unit_price' => 50000, 'total' => 50000],
        ],
        'subtotal' => 50000,
        'vat' => 8000,
        'platform_fee' => 1740,
        'total' => 59740,
    ],
    'editable' => false,
    'showMpesa' => true,
    'variant' => 'full', // full, minimal
])

<div class="bg-white rounded-xl shadow-2xl p-6 lg:p-8 transform hover:scale-105 transition-transform duration-300">
    <!-- Invoice Header -->
    <div class="border-b border-slate-200 pb-4 mb-4">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Invoice</h3>
                <p class="text-sm text-slate-600">#{{ $invoice['number'] ?? 'INV-001' }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-500">Issue Date</p>
                <p class="text-sm font-medium text-slate-900">{{ now()->format('M d, Y') }}</p>
            </div>
        </div>
    </div>
    
    <!-- Client Info -->
    <div class="mb-6">
        <p class="text-xs text-slate-500 mb-1">Bill To:</p>
        <p class="text-sm font-semibold text-slate-900">{{ $invoice['client'] ?? 'Demo Client Ltd' }}</p>
    </div>
    
    <!-- Line Items -->
    <div class="mb-6">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left py-2 text-slate-600 font-medium">Description</th>
                    <th class="text-right py-2 text-slate-600 font-medium">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($invoice['items'] ?? []) as $item)
                    <tr class="border-b border-slate-100">
                        <td class="py-2 text-slate-700">
                            {{ $item['description'] ?? 'Service' }}
                            @if(isset($item['quantity']) && $item['quantity'] > 1)
                                <span class="text-slate-500 text-xs">× {{ $item['quantity'] }}</span>
                            @endif
                        </td>
                        <td class="py-2 text-right font-medium text-slate-900">
                            KES {{ number_format($item['total'] ?? $item['unit_price'] ?? 0, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Totals -->
    <div class="space-y-2 mb-6">
        <div class="flex justify-between text-sm">
            <span class="text-slate-600">Subtotal</span>
            <span class="font-medium text-slate-900">KES {{ number_format($invoice['subtotal'] ?? 0, 2) }}</span>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-slate-600">VAT (16%)</span>
            <span class="font-medium text-slate-900">KES {{ number_format($invoice['vat'] ?? 0, 2) }}</span>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-slate-600">Platform Fee (3%)</span>
            <span class="font-medium text-slate-900">KES {{ number_format($invoice['platform_fee'] ?? 0, 2) }}</span>
        </div>
        <div class="flex justify-between pt-2 border-t-2 border-slate-200">
            <span class="font-bold text-slate-900">Total</span>
            <span class="font-black text-lg text-blue-600">KES {{ number_format($invoice['total'] ?? 0, 2) }}</span>
        </div>
    </div>
    
    <!-- M-Pesa Badge -->
    @if($showMpesa)
        <div class="flex items-center justify-center gap-2 bg-blue-50 border border-blue-200 rounded-lg py-2 px-4">
            <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-semibold text-blue-700">M-Pesa Payment Ready</span>
        </div>
    @endif
</div>

