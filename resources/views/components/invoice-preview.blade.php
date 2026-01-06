@props(['animated' => true])

<div 
    class="relative max-w-md mx-auto lg:max-w-lg"
    @if($animated)
        x-data="{ 
            loaded: false,
            init() {
                this.loaded = true;
            }
        }"
        x-bind:class="{ 'opacity-0 translate-y-4': !loaded, 'opacity-100 translate-y-0': loaded }"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
    @endif
>
    <!-- Invoice Card -->
    <div class="bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden hover:shadow-3xl transition-shadow duration-300">
        <!-- Invoice Header -->
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Amani Tech Solutions</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Invoice #INV-2024-0842</p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- M-Pesa Badge -->
                    <div class="flex items-center px-3 py-1 bg-emerald-100 rounded-full">
                        <svg class="w-4 h-4 text-emerald-700 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                        </svg>
                        <span class="text-xs font-semibold text-emerald-700">M-Pesa</span>
                    </div>
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold uppercase">Paid</span>
                </div>
            </div>
        </div>

        <!-- Invoice Body -->
        <div class="px-6 py-5">
            <!-- Bill To -->
            <div class="mb-6 pb-4 border-b border-gray-200">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Bill To</p>
                <p class="text-sm font-semibold text-gray-900">John Mwangi</p>
                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Nairobi, Kenya</p>
            </div>

            <!-- Line Items -->
            <div class="space-y-3 mb-6">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Web Development Services</p>
                        <p class="text-xs text-gray-500 mt-0.5">Custom website development</p>
                    </div>
                    <p class="text-sm font-semibold text-gray-900 ml-4">KES 25,000</p>
                </div>
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Mobile App Development</p>
                        <p class="text-xs text-gray-500 mt-0.5">iOS & Android app</p>
                    </div>
                    <p class="text-sm font-semibold text-gray-900 ml-4">KES 45,000</p>
                </div>
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Consulting Services</p>
                        <p class="text-xs text-gray-500 mt-0.5">Technical consultation</p>
                    </div>
                    <p class="text-sm font-semibold text-gray-900 ml-4">KES 12,500</p>
                </div>
            </div>

            <!-- Totals -->
            <div class="space-y-2 pt-4 border-t border-gray-200">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                    <span>Subtotal</span>
                    <span>KES 82,500</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                    <span>VAT (16%)</span>
                    <span>KES 13,200</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300 items-center pt-2 border-t border-gray-200">
                    <div class="flex items-center gap-1">
                        <span>Platform Fee (3%)</span>
                        <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20" title="Charged only when payment is received">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span>KES 765</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300 mt-2">
                    <span class="text-base font-bold text-gray-900">Total</span>
                    <span class="text-xl font-bold text-emerald-600">KES 96,465</span>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="mt-6 pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-semibold text-emerald-700">Paid via M-Pesa</span>
                    </div>
                    <span class="text-xs text-gray-500">Dec 15, 2024</span>
                </div>
            </div>
        </div>

        <!-- Subtle Glow Effect -->
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/50 to-teal-50/50 opacity-0 hover:opacity-100 transition-opacity duration-300 pointer-events-none rounded-xl"></div>
    </div>

    <!-- Floating Trust Indicator -->
    <div class="absolute -bottom-4 -right-4 bg-white rounded-lg shadow-lg px-4 py-2 border border-gray-200 hidden lg:block">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">Payment Received</span>
        </div>
    </div>
</div>

