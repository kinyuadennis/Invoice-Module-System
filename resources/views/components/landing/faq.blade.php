<div id="faq" class="bg-white dark:bg-[#242424]">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:py-16 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto divide-y-2 divide-gray-200">
            <h2 class="text-center text-3xl font-extrabold text-gray-900 sm:text-4xl">
                Frequently asked questions
            </h2>
            <dl class="mt-6 space-y-6 divide-y divide-gray-200">
                <div x-data="{ open: false }" class="pt-6">
                    <dt class="text-lg">
                        <button @click="open = !open" type="button" class="text-left w-full flex justify-between items-start text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" aria-controls="faq-0" aria-expanded="false">
                            <span class="font-medium text-gray-900">
                                Is this KRA e-invoice (TIMS) compliant?
                            </span>
                            <span class="ml-6 h-7 flex items-center">
                                <svg class="rotate-0 h-6 w-6 transform" :class="{'rotate-180': open, 'rotate-0': !open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                    </dt>
                    <dd x-show="open" class="mt-2 pr-12" id="faq-0" style="display: none;">
                        <p class="text-base text-gray-500">
                            Yes. Every invoice you send includes the required QR code and tax information, and is automatically reported to the KRA e-TIMS system in real time.
                        </p>
                    </dd>
                </div>

                <div x-data="{ open: false }" class="pt-6">
                    <dt class="text-lg">
                        <button @click="open = !open" type="button" class="text-left w-full flex justify-between items-start text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" aria-controls="faq-1" aria-expanded="false">
                            <span class="font-medium text-gray-900">
                                Can I accept M‑PESA payments?
                            </span>
                            <span class="ml-6 h-7 flex items-center">
                                <svg class="rotate-0 h-6 w-6 transform" :class="{'rotate-180': open, 'rotate-0': !open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                    </dt>
                    <dd x-show="open" class="mt-2 pr-12" id="faq-1" style="display: none;">
                        <p class="text-base text-gray-500">
                            Absolutely. Simply configure your Safaricom Daraja credentials and enable the Lipa na M‑PESA option. Your customers can pay invoices directly through M-PESA.
                        </p>
                    </dd>
                </div>

                <div x-data="{ open: false }" class="pt-6">
                    <dt class="text-lg">
                        <button @click="open = !open" type="button" class="text-left w-full flex justify-between items-start text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" aria-controls="faq-2" aria-expanded="false">
                            <span class="font-medium text-gray-900">
                                How soon can I start invoicing?
                            </span>
                            <span class="ml-6 h-7 flex items-center">
                                <svg class="rotate-0 h-6 w-6 transform" :class="{'rotate-180': open, 'rotate-0': !open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                    </dt>
                    <dd x-show="open" class="mt-2 pr-12" id="faq-2" style="display: none;">
                        <p class="text-base text-gray-500">
                            Within minutes. Our guided setup gets you creating your first invoice in under 5 seconds.
                        </p>
                    </dd>
                </div>

                <div x-data="{ open: false }" class="pt-6">
                    <dt class="text-lg">
                        <button @click="open = !open" type="button" class="text-left w-full flex justify-between items-start text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" aria-controls="faq-3" aria-expanded="false">
                            <span class="font-medium text-gray-900">
                                Is there a free plan or trial?
                            </span>
                            <span class="ml-6 h-7 flex items-center">
                                <svg class="rotate-0 h-6 w-6 transform" :class="{'rotate-180': open, 'rotate-0': !open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                    </dt>
                    <dd x-show="open" class="mt-2 pr-12" id="faq-3" style="display: none;">
                        <p class="text-base text-gray-500">
                            Yes. We offer a free trial so you can test all features before paying.
                        </p>
                    </dd>
                </div>

                <div x-data="{ open: false }" class="pt-6">
                    <dt class="text-lg">
                        <button @click="open = !open" type="button" class="text-left w-full flex justify-between items-start text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" aria-controls="faq-4" aria-expanded="false">
                            <span class="font-medium text-gray-900">
                                Will my accountant like this?
                            </span>
                            <span class="ml-6 h-7 flex items-center">
                                <svg class="rotate-0 h-6 w-6 transform" :class="{'rotate-180': open, 'rotate-0': !open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                    </dt>
                    <dd x-show="open" class="mt-2 pr-12" id="faq-4" style="display: none;">
                        <p class="text-base text-gray-500">
                            Yes. Provide your accountant access (multi-user) and they’ll appreciate the audit trail and reporting. We also integrate with popular accounting tools if needed.
                        </p>
                    </dd>
                </div>

                <div x-data="{ open: false }" class="pt-6">
                    <dt class="text-lg">
                        <button @click="open = !open" type="button" class="text-left w-full flex justify-between items-start text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" aria-controls="faq-5" aria-expanded="false">
                            <span class="font-medium text-gray-900">
                                What support do you offer?
                            </span>
                            <span class="ml-6 h-7 flex items-center">
                                <svg class="rotate-0 h-6 w-6 transform" :class="{'rotate-180': open, 'rotate-0': !open}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </button>
                    </dt>
                    <dd x-show="open" class="mt-2 pr-12" id="faq-5" style="display: none;">
                        <p class="text-base text-gray-500">
                            Email and phone support (Kenya hours), plus an online help center. Our team includes Kenyan tax experts.
                        </p>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>