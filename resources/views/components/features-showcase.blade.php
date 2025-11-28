@props([
    'categories' => ['payment', 'compliance', 'analytics', 'automation'],
    'features' => [],
    'showComparison' => false
])

<section id="features" class="bg-white py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Features Built for Kenyan Businesses</h2>
            <p class="mt-4 text-lg text-gray-600">Everything you need to get paid faster and stay compliant</p>
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap justify-center gap-4 mb-12" x-data="{ activeTab: 'all' }">
            <button 
                @click="activeTab = 'all'"
                :class="activeTab === 'all' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="px-6 py-2 rounded-lg font-semibold transition-colors"
            >
                All Features
            </button>
            @foreach($categories as $category)
                <button 
                    @click="activeTab = '{{ $category }}'"
                    :class="activeTab === '{{ $category }}' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-6 py-2 rounded-lg font-semibold transition-colors capitalize"
                >
                    {{ ucfirst($category) }}
                </button>
            @endforeach
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{ activeTab: 'all' }">
            @foreach($features as $feature)
                <x-kenyan-feature-card :feature="$feature" />
            @endforeach
        </div>

        @if($showComparison)
        <!-- Feature Comparison Table -->
        <div class="mt-16">
            <div class="bg-gray-50 rounded-xl p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">InvoiceHub vs Manual Invoicing</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-900">Feature</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-900">Manual</th>
                                <th class="text-center py-3 px-4 font-semibold text-emerald-600">InvoiceHub</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-3 px-4 text-gray-700">Invoice Creation Time</td>
                                <td class="py-3 px-4 text-center text-gray-600">30 minutes</td>
                                <td class="py-3 px-4 text-center text-emerald-600 font-semibold">60 seconds</td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 text-gray-700">Payment Tracking</td>
                                <td class="py-3 px-4 text-center text-gray-600">Manual spreadsheets</td>
                                <td class="py-3 px-4 text-center text-emerald-600 font-semibold">Automated</td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 text-gray-700">KRA Compliance</td>
                                <td class="py-3 px-4 text-center text-gray-600">Manual entry</td>
                                <td class="py-3 px-4 text-center text-emerald-600 font-semibold">Auto-generated</td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 text-gray-700">Payment Reminders</td>
                                <td class="py-3 px-4 text-center text-gray-600">Manual calls/emails</td>
                                <td class="py-3 px-4 text-center text-emerald-600 font-semibold">Automated</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

