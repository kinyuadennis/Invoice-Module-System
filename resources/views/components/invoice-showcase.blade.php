@props([
    'invoices' => [],
    'showFilters' => true,
    'showActions' => false
])

<section class="bg-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Recent Invoices</h2>
            <p class="mt-4 text-lg text-gray-600">See how Kenyan businesses are getting paid faster</p>
        </div>

        @if($showFilters)
        <!-- Filters -->
        <div class="mb-8" x-data="invoiceFilters()">
            <div class="flex flex-wrap items-center gap-4 justify-center">
                <!-- Status Filter -->
                <div class="relative">
                    <select 
                        x-model="filters.status" 
                        @change="applyFilters()"
                        class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                        <option value="">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="sent">Sent</option>
                        <option value="overdue">Overdue</option>
                        <option value="draft">Draft</option>
                    </select>
                    <svg class="absolute right-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <!-- Payment Method Filter -->
                <div class="relative">
                    <select 
                        x-model="filters.paymentMethod" 
                        @change="applyFilters()"
                        class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 text-sm font-medium text-gray-700 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                        <option value="">All Payment Methods</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cash">Cash</option>
                    </select>
                    <svg class="absolute right-2 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        x-model="filters.search" 
                        @input.debounce.300ms="applyFilters()"
                        placeholder="Search invoices..."
                        class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2 pl-10 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        @endif

        <!-- Invoice Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="invoiceShowcase()">
            @forelse($invoices as $invoice)
                <x-enhanced-invoice-card :invoice="$invoice" :showActions="$showActions" />
            @empty
                <div class="col-span-3 text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-4 text-gray-500">No invoices yet. Be the first!</p>
                    <a href="{{ route('register') }}" class="mt-4 inline-block text-emerald-600 font-semibold hover:text-emerald-700">
                        Create your first invoice →
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>

<script>
function invoiceFilters() {
    return {
        filters: {
            status: '',
            paymentMethod: '',
            search: ''
        },
        
        applyFilters() {
            // Filter logic would be handled server-side or via Livewire
            // This is just for UI state management
            console.log('Filters applied:', this.filters);
        }
    }
}

function invoiceShowcase() {
    return {
        init() {
            // Initialize any showcase-specific logic
        }
    }
}
</script>

