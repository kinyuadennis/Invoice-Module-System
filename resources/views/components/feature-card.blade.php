@props([
    'icon',
    'title',
    'description',
    'benefit' => null,
    'badge' => null,
    'variant' => 'default', // default, highlighted
])

<div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300 border border-slate-200 hover:border-blue-200 transform hover:-translate-y-1 {{ $variant === 'highlighted' ? 'ring-2 ring-blue-200' : '' }}">
    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
        {!! $icon !!}
    </div>
    
    @if($badge)
        <span class="inline-block mb-2 px-2 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full border border-blue-200">
            {{ $badge }}
        </span>
    @endif
    
    <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $title }}</h3>
    <p class="text-sm text-slate-600 mb-3 leading-relaxed">{{ $description }}</p>
    
    @if($benefit)
        <p class="text-sm font-semibold text-blue-600">{{ $benefit }}</p>
    @endif
</div>

