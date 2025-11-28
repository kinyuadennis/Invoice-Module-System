@props(['clients', 'stats'])

<div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 min-h-screen flex items-center py-12 sm:py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 items-center">
            <!-- LEFT COLUMN (60%) -->
            <div class="text-white max-w-lg">
                <!-- Badge -->
                <div class="inline-flex items-center px-4 py-2 mb-6 bg-white/20 backdrop-blur-md rounded-full border border-white/30">
                    <span class="text-sm font-semibold">🚀 Used by {{ $stats['businesses'] }}+ Kenyan businesses</span>
                </div>

                <!-- H1 -->
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-black leading-tight mb-6 drop-shadow-2xl">
                    Create Professional Invoices that Get Paid
                </h1>

                <!-- Subheadline -->
                <p class="text-xl md:text-2xl text-indigo-100 mb-8 leading-relaxed drop-shadow-md">
                    Send via M-Pesa, Email, WhatsApp. Get paid 3x faster
                </p>

                <!-- Dual CTA -->
                <div class="flex flex-col sm:flex-row gap-4 mb-6">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-indigo-600 font-bold rounded-lg hover:bg-gray-50 shadow-xl hover:shadow-2xl transition-all duration-200 transform hover:scale-105">
                        Start Free (No Card)
                    </a>
                    <a href="#demo" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 backdrop-blur-sm transition-all duration-200">
                        ▶ Watch Demo (45s)
                    </a>
                </div>

                <!-- Trust signals -->
                <p class="text-sm text-indigo-200">
                    ✓ Free to create • ✓ 0.8% platform fee • ✓ No monthly fees
                </p>
            </div>

            <!-- RIGHT COLUMN (40%) - Mock Invoice Card -->
            <div class="relative">
                <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl p-6 lg:p-8 border border-white/20">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">John's Garage</h3>
                            <p class="text-sm text-gray-500">#INV-00123</p>
                        </div>
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">PAID</span>
                    </div>

                    <!-- Line Items -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">Car Repair Service</span>
                            <span class="font-semibold text-gray-900">KSh 8,500</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">Parts & Materials</span>
                            <span class="font-semibold text-gray-900">KSh 2,300</span>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="space-y-2 pt-4 border-t border-gray-200">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span>KSh 10,800</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Platform Fee (0.8%)</span>
                            <span>KSh 86</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                            <span class="text-gray-900">Total</span>
                            <span class="text-emerald-600">KSh 10,886</span>
                        </div>
                    </div>

                    <!-- CTA Button -->
                    <a href="{{ route('register') }}" class="mt-6 block w-full text-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                        Send Invoice → M-Pesa/Email
                    </a>
                </div>

                <!-- Floating Stat -->
                <div class="absolute -bottom-4 -right-4 bg-yellow-400 text-gray-900 px-4 py-2 rounded-lg shadow-xl transform rotate-3">
                    <p class="text-sm font-bold">3x Faster Payments</p>
                </div>
            </div>
        </div>
    </div>
</div>

