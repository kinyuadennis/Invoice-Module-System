<div class="py-12 bg-gradient-to-br from-blue-50 to-gray-50" id="demo-preview">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Try Before You Sign Up</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                See your invoicing dashboard
            </p>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                No login required. Explore what you get instantly.
            </p>
        </div>

        <!-- Dashboard Preview Mockup -->
        <div class="bg-white dark:bg-[#242424] rounded-xl shadow-2xl overflow-hidden border border-gray-200 dark:border-[#333333]">
            <!-- Dashboard Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-white dark:bg-[#242424]/20 flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-white font-bold text-lg">Invoices</h3>
                </div>
                <button class="px-4 py-2 bg-white dark:bg-[#242424] text-blue-600 rounded-lg font-semibold text-sm hover:bg-blue-50 transition-colors">
                    + New Invoice
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6 bg-gray-50 dark:bg-[#1A1A1A]">
                <div class="bg-white dark:bg-[#242424] rounded-lg p-4 shadow-sm border border-gray-200 dark:border-[#333333]">
                    <div class="text-sm text-gray-500 mb-1">Sent</div>
                    <div class="text-2xl font-bold text-gray-900">KES 39.5k</div>
                    <div class="text-xs text-gray-400 mt-1">12 invoices</div>
                </div>
                <div class="bg-white dark:bg-[#242424] rounded-lg p-4 shadow-sm border border-gray-200 dark:border-[#333333]">
                    <div class="text-sm text-gray-500 mb-1">Total Outstanding</div>
                    <div class="text-2xl font-bold text-gray-900">KES 42.6k</div>
                    <div class="text-xs text-gray-400 mt-1">8 unpaid</div>
                </div>
                <div class="bg-white dark:bg-[#242424] rounded-lg p-4 shadow-sm border border-gray-200 dark:border-[#333333]">
                    <div class="text-sm text-gray-500 mb-1">Overdue</div>
                    <div class="text-2xl font-bold text-red-600">KES 17.8k</div>
                    <div class="text-xs text-gray-400 mt-1">3 invoices</div>
                </div>
            </div>

            <!-- Invoice List -->
            <div class="p-6">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wide mb-4">Recently Updated</h4>
                <div class="space-y-3">
                    <!-- Invoice 1 - Paid -->
                    <div class="flex items-center justify-between p-4 bg-white dark:bg-[#242424] rounded-lg border border-gray-200 dark:border-[#333333] hover:border-blue-300 transition-colors cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">#000001 - Fairfield Electronics</div>
                                <div class="text-sm text-gray-500">Due: 08/24/2023</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">KES 125</div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Paid</span>
                        </div>
                    </div>

                    <!-- Invoice 2 - Viewed -->
                    <div class="flex items-center justify-between p-4 bg-white dark:bg-[#242424] rounded-lg border border-gray-200 dark:border-[#333333] hover:border-blue-300 transition-colors cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">#000002 - Nairobi Hardware</div>
                                <div class="text-sm text-gray-500">Due: 08/27/2023</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">KES 125</div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Viewed</span>
                        </div>
                    </div>

                    <!-- Invoice 3 - Overdue -->
                    <div class="flex items-center justify-between p-4 bg-white dark:bg-[#242424] rounded-lg border border-gray-200 dark:border-[#333333] hover:border-red-300 transition-colors cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">#000003 - Tech Solutions Ltd</div>
                                <div class="text-sm text-gray-500">Due: 08/11/2023</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">KES 225</div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Overdue</span>
                        </div>
                    </div>

                    <!-- Invoice 4 - Draft -->
                    <div class="flex items-center justify-between p-4 bg-white dark:bg-[#242424] rounded-lg border border-gray-200 dark:border-[#333333] hover:border-gray-300 dark:border-[#404040] transition-colors cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-gray-100 dark:bg-[#2A2A2A] flex items-center justify-center">
                                <svg class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">#000004 - Retail Kenya</div>
                                <div class="text-sm text-gray-500">Draft</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">KES 180</div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-[#2A2A2A] text-gray-800">Draft</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Footer -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 text-center">
                <p class="text-white text-sm mb-3">Ready to manage your invoices like this?</p>
                <a href="{{ route('register') }}" class="inline-block px-6 py-3 bg-white dark:bg-[#242424] text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg">
                    Start Free Trial
                </a>
            </div>
        </div>
    </div>
</div>