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
    :class="scrolled ? 'bg-white shadow-md' : 'bg-white/95 backdrop-blur-sm'"
>
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
                        class="text-slate-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        {{ $link['text'] ?? '' }}
                    </a>
                @endforeach
                
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                            Admin
                        </a>
                    @else
                        <a href="{{ route('user.dashboard') }}" class="text-blue-600 hover:text-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                            Dashboard
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="text-slate-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Login
                    </a>
                    <a 
                        href="{{ $ctaHref ?? route('register') }}" 
                        class="bg-blue-500 text-white hover:bg-blue-600 px-4 py-2 rounded-lg text-sm font-bold transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
                    >
                        {{ $ctaText }}
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center space-x-2">
                @guest
                    <a href="{{ route('login') }}" class="text-slate-700 hover:text-blue-600 px-3 py-2 text-sm font-medium">
                        Login
                    </a>
                @endguest
                <button 
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="p-2 rounded-md text-slate-700 hover:text-blue-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    aria-label="Toggle menu"
                >
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
        x-cloak
    >
        <div class="px-4 pt-2 pb-4 space-y-1">
            @foreach($links as $link)
                <a 
                    href="{{ $link['href'] ?? '#' }}" 
                    class="block text-slate-700 hover:text-blue-600 hover:bg-slate-50 px-3 py-2 rounded-md text-base font-medium transition-colors"
                    @click="mobileMenuOpen = false"
                >
                    {{ $link['text'] ?? '' }}
                </a>
            @endforeach
            
            @guest
                <div class="pt-4 border-t border-slate-200">
                    <a 
                        href="{{ $ctaHref ?? route('register') }}" 
                        class="block w-full text-center bg-blue-500 text-white hover:bg-blue-600 px-4 py-3 rounded-lg text-base font-bold transition-colors shadow-lg"
                        @click="mobileMenuOpen = false"
                    >
                        {{ $ctaText }}
                    </a>
                </div>
            @endguest
        </div>
    </div>
</nav>

