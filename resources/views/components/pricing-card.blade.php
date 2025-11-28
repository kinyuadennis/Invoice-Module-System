@props(['name', 'price', 'period', 'features', 'popular' => false, 'cta' => 'Get Started'])

<div class="relative bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 border-2 {{ $popular ? 'border-indigo-500 scale-105' : 'border-gray-200' }}">
    @if($popular)
        <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
            <span class="bg-indigo-600 text-white px-4 py-1 rounded-full text-xs font-bold">MOST POPULAR</span>
        </div>
    @endif

    <h3 class="text-2xl font-black text-gray-900 mb-2">{{ $name }}</h3>
    <div class="mb-6">
        <span class="text-4xl font-black text-gray-900">{{ $price }}</span>
        @if($period)
            <span class="text-gray-600">/{{ $period }}</span>
        @endif
    </div>

    <ul class="space-y-3 mb-8">
        @foreach($features as $feature)
            <li class="flex items-start">
                <svg class="w-5 h-5 text-emerald-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm text-gray-700">{{ $feature }}</span>
            </li>
        @endforeach
    </ul>

    <a href="{{ route('register') }}" class="block w-full text-center px-6 py-3 {{ $popular ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-100 hover:bg-gray-200' }} {{ $popular ? 'text-white' : 'text-gray-900' }} font-bold rounded-lg transition-colors">
        {{ $cta }}
    </a>
</div>

