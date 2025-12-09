@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
$variantClasses = [
    'primary' => 'bg-[#2B6EF6] text-white hover:bg-[#2563EB] focus-visible:outline-2 focus-visible:outline-[#2B6EF6] focus-visible:outline-offset-2 shadow-sm hover:shadow-md',
    'secondary' => 'bg-neutral-200 text-neutral-900 hover:bg-neutral-300 focus-visible:outline-2 focus-visible:outline-neutral-500 focus-visible:outline-offset-2',
    'outline' => 'border border-neutral-300 text-neutral-700 bg-white hover:bg-neutral-50 focus-visible:outline-2 focus-visible:outline-[#2B6EF6] focus-visible:outline-offset-2',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-red-500 focus-visible:outline-offset-2 shadow-sm hover:shadow-md',
    'ghost' => 'text-neutral-700 hover:bg-neutral-100 focus-visible:outline-2 focus-visible:outline-neutral-500 focus-visible:outline-offset-2',
][$variant] ?? 'bg-[#2B6EF6] text-white hover:bg-[#2563EB]';

$sizeClasses = [
    'sm' => 'px-3 py-2 text-sm min-h-[36px]',
    'md' => 'px-4 py-3 text-base min-h-[44px]',
    'lg' => 'px-6 py-3.5 text-lg min-h-[48px]',
][$size] ?? 'px-4 py-3 text-base min-h-[44px]';
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center font-semibold rounded-lg focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150 ' . $variantClasses . ' ' . $sizeClasses]) }}>
    {{ $slot }}
</button>

