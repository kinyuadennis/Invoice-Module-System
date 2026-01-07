<nav x-data="{ mobileMenuOpen: false }" class="bg-white dark:bg-[#242424] border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="/" class="font-bold text-2xl text-indigo-600">
                        InvoiceHub
                    </a>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="#features" class="border-transparent text-gray-500 hover:border-gray-300 dark:border-[#404040] hover:text-gray-700 dark:text-gray-200 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Features
                    </a>
                    <a href="#pricing" class="border-transparent text-gray-500 hover:border-gray-300 dark:border-[#404040] hover:text-gray-700 dark:text-gray-200 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        Pricing
                    </a>
                    <a href="#faq" class="border-transparent text-gray-500 hover:border-gray-300 dark:border-[#404040] hover:text-gray-700 dark:text-gray-200 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        FAQ
                    </a>
                </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:items-center sm:space-x-4">
                <!-- Theme Toggle -->
                <button
                    x-data="{
                        darkMode: localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                        toggleTheme() {
                            this.darkMode = !this.darkMode;
                            if (this.darkMode) {
                                document.documentElement.classList.add('dark');
                                localStorage.theme = 'dark';
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.theme = 'light';
                            }
                        }
                    }"
                    @click="toggleTheme()"
                    class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors rounded-full hover:bg-gray-100 dark:hover:bg-[#333333]"
                    aria-label="Toggle Dark Mode">
                    <!-- Sun Icon (showing in dark mode) -->
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon Icon (showing in light mode) -->
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                    Log in
                </a>
                <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow-sm transition-colors duration-200">
                    Get Started
                </a>
            </div>
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:bg-[#2A2A2A] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg class="h-6 w-6" :class="{'hidden': mobileMenuOpen, 'block': !mobileMenuOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg class="h-6 w-6" :class="{'block': mobileMenuOpen, 'hidden': !mobileMenuOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div x-show="mobileMenuOpen" class="sm:hidden" id="mobile-menu" style="display: none;">
        <div class="pt-2 pb-3 space-y-1">
            <a href="#features" class="border-transparent text-gray-500 hover:bg-gray-50 dark:bg-[#1A1A1A] hover:border-gray-300 dark:border-[#404040] hover:text-gray-700 dark:text-gray-200 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Features</a>
            <a href="#pricing" class="border-transparent text-gray-500 hover:bg-gray-50 dark:bg-[#1A1A1A] hover:border-gray-300 dark:border-[#404040] hover:text-gray-700 dark:text-gray-200 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">Pricing</a>
            <a href="#faq" class="border-transparent text-gray-500 hover:bg-gray-50 dark:bg-[#1A1A1A] hover:border-gray-300 dark:border-[#404040] hover:text-gray-700 dark:text-gray-200 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">FAQ</a>
        </div>
        <div class="pt-4 pb-4 border-t border-gray-200 dark:border-[#333333]">
            <div class="flex items-center px-4 space-y-2 flex-col">
                <!-- Mobile Theme Toggle -->
                <button
                    x-data="{
                        darkMode: localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                        toggleTheme() {
                            this.darkMode = !this.darkMode;
                            if (this.darkMode) {
                                document.documentElement.classList.add('dark');
                                localStorage.theme = 'dark';
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.theme = 'light';
                            }
                        }
                    }"
                    @click="toggleTheme()"
                    class="flex w-full items-center justify-center px-4 py-2 border border-gray-300 dark:border-[#404040] shadow-sm text-base font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-[#242424] hover:bg-gray-50 dark:hover:bg-[#1A1A1A] gap-2">
                    <span x-text="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">Switch Theme</span>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
                <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-[#404040] shadow-sm text-base font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-[#242424] hover:bg-gray-50 dark:bg-[#1A1A1A]">
                    Log in
                </a>
                <a href="{{ route('register') }}" class="block w-full text-center px-4 py-2 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Get Started
                </a>
            </div>
        </div>
    </div>
</nav>