@props([
    'text',
    'icon' => null,
    'size' => 'md', // sm, md, lg
    'variant' => 'default', // default, outline
])

@php
    $sizeClasses = [
        'sm' => 'text-xs px-3 py-1',
        'md' => 'text-sm px-4 py-2',
        'lg' => 'text-base px-5 py-2.5',
    ];
    
    $iconSizes = [
        'sm' => 'h-3 w-3',
        'md' => 'h-4 w-4',
        'lg' => 'h-5 w-5',
    ];
    
    $variantClasses = [
        'default' => 'bg-blue-100 text-blue-700 border-blue-200',
        'outline' => 'bg-transparent text-blue-700 border-2 border-blue-500',
    ];
@endphp

<span class="inline-flex items-center gap-2 {{ $sizeClasses[$size] }} {{ $variantClasses[$variant] }} rounded-full font-medium border transition-colors">
    @if($icon)
        <svg class="{{ $iconSizes[$size] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($icon === 'shield-check')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            @elseif($icon === 'check-circle')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            @elseif($icon === 'lock-closed')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            @endif
        </svg>
    @endif
    {{ $text }}
</span>

