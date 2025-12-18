<div class="relative bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
            <svg class="hidden lg:block absolute right-0 inset-y-0 h-full w-48 text-white transform translate-x-1/2" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                <polygon points="50,0 100,0 50,100 0,100" />
            </svg>

            <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                <div class="sm:text-center lg:text-left">
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                        <span class="block xl:inline">Effortless invoicing for</span>
                        <span class="block text-blue-600 xl:inline">Kenyan businesses</span>
                    </h1>
                    <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                        Generate KRA-compliant e-invoices in seconds. Trusted accounting software for SMEs.
                    </p>
                    <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                        <div class="rounded-md shadow">
                            <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg">
                                Start Free Trial
                            </a>
                        </div>

                    </div>

                    <!-- Trust Cue Badge -->
                    <div class="mt-6 flex items-center justify-center lg:justify-start gap-2">
                        <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            KRA-eTIMS compliant – start free
                        </span>
                    </div>

                    <!-- Integration Logos -->
                    <div class="mt-8">
                        <p class="text-xs font-semibold uppercase text-gray-400 tracking-wide mb-4 text-center lg:text-left">
                            Integrates with
                        </p>
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4">
                            <div class="h-10 px-3 bg-gray-100 rounded flex items-center text-xs font-bold text-gray-600">
                                KRA eTIMS
                            </div>
                            <div class="h-10 px-3 bg-green-50 rounded flex items-center text-xs font-bold text-green-600">
                                Safaricom M-PESA
                            </div>
                            <span class="text-gray-400 font-medium text-sm flex items-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z" />
                                </svg>
                                RetailKE
                            </span>
                            <span class="text-gray-400 font-medium text-sm flex items-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                                </svg>
                                TechSolutions
                            </span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-blue-900 flex items-center justify-center p-8 lg:p-12">
        <div class="w-full max-w-lg mx-auto">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl mb-4">
                See it in action
            </h2>
            <p class="text-lg text-blue-100 mb-8">
                Generate eTIMS invoices instantly. Watch how easy it is to create a compliant invoice and send it via WhatsApp.
            </p>

            <!-- Video Placeholder / Demo Trigger -->
            <div class="relative rounded-xl overflow-hidden shadow-2xl border border-blue-700 group cursor-pointer" onclick="window.dispatchEvent(new CustomEvent('start-demo'))">
                <div class="aspect-w-16 aspect-h-9 bg-gray-900 flex items-center justify-center relative">
                    <!-- Play Button Overlay -->
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 group-hover:bg-black/30 transition-colors">
                        <div class="h-16 w-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="h-8 w-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                        </div>
                    </div>
                    <!-- Placeholder Content -->
                    <div class="w-full h-full flex flex-col items-center justify-center p-8 text-center">
                        <span class="text-blue-200 font-medium text-lg">Interactive Demo Preview</span>
                        <span class="text-blue-400 text-sm mt-2">2 Minutes • No sign-up required</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center lg:text-left">
                <button onclick="window.dispatchEvent(new CustomEvent('start-demo'))" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 transition-colors shadow-sm">
                    Create e-Invoice Now
                </button>
            </div>
        </div>
    </div>
</div>