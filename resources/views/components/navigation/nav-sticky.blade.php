@props([
'logo' => 'InvoiceHub',
'links' => [],
'ctaText' => 'Sign Up',
'ctaHref' => null,
])

<nav
    x-data="{ scrolled: false, mobileMenuOpen: false }"
    @scroll.window="scrolled = window.scrollY > 10"
    class="sticky top-0 z-50 transition-all duration-300"
    :class="scrolled ? 'bg-white shadow-md' : 'bg-white/95 backdrop-blur-sm'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="text-2xl font-black text-slate-900 hover:text-blue-600 transition-colors">
                    {{ $logo }}
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-1 lg:space-x-4">
                @foreach($links as $link)
                <a
                    href="{{ $link['href'] ?? '#' }}"
                    class="text-slate-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                    {{ $link['text'] ?? '' }}
                </a>
                @endforeach

                @auth
                {{-- User Profile Dropdown --}}
                <div x-data="{ dropdownOpen: false }" class="relative">
                    <button
                        @click="dropdownOpen = !dropdownOpen"
                        @click.away="dropdownOpen = false"
                        class="flex items-center space-x-2 text-slate-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{-- User Avatar --}}
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>

                        {{-- User Name & Role --}}
                        <div class="hidden lg:block text-left">
                            <div class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</div>
                            @if(auth()->user()->role === 'admin')
                            <div class="text-xs text-blue-600 font-medium">Admin</div>
                            @endif
                        </div>

                        {{-- Dropdown Arrow --}}
                        <svg
                            class="w-4 h-4 transition-transform"
                            :class="{ 'rotate-180': dropdownOpen }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div
                        x-show="dropdownOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50"
                        x-cloak>
                        {{-- User Info Header --}}
                        <div class="px-4 py-3 border-b border-slate-200">
                            <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                            @if(auth()->user()->role === 'admin')
                            <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Administrator
                            </span>
                            @endif
                        </div>

                        {{-- Menu Items --}}
                        <div class="py-1">
                            @if(auth()->user()->role === 'admin')
                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Admin Dashboard
                            </a>
                            @else
                            <a
                                href="{{ route('user.dashboard') }}"
                                class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Dashboard
                            </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors text-left">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="text-slate-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                    Login
                </a>
                <a
                    href="{{ $ctaHref ?? route('register') }}"
                    class="bg-blue-500 text-white hover:bg-blue-600 px-4 py-2 rounded-lg text-sm font-bold transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                    {{ $ctaText }}
                </a>
                @endauth
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center space-x-2">
                @guest
                <a href="{{ route('login') }}" class="text-slate-700 hover:text-blue-600 px-4 py-3 text-base font-medium min-h-[44px]">
                    Login
                </a>
                @endguest
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="p-2 rounded-md text-slate-700 hover:text-blue-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    aria-label="Toggle menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        @click.away="mobileMenuOpen = false"
        class="md:hidden bg-white border-t border-slate-200 shadow-lg"
        x-cloak>
        <div class="px-4 pt-2 pb-4 space-y-1">
            @foreach($links as $link)
            <a
                href="{{ $link['href'] ?? '#' }}"
                class="block text-slate-700 hover:text-blue-600 hover:bg-slate-50 px-3 py-2 rounded-md text-base font-medium transition-colors"
                @click="mobileMenuOpen = false">
                {{ $link['text'] ?? '' }}
            </a>
            @endforeach

            @guest
            <div class="pt-4 border-t border-slate-200">
                <a
                    href="{{ $ctaHref ?? route('register') }}"
                    class="block w-full text-center bg-blue-500 text-white hover:bg-blue-600 px-4 py-3 rounded-lg text-base font-bold transition-colors shadow-lg"
                    @click="mobileMenuOpen = false">
                    {{ $ctaText }}
                </a>
            </div>
            @endguest
        </div>
    </div>
</nav>