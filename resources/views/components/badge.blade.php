@props(['variant' => 'default'])

@php
$classes = [
    'default' => 'bg-neutral-100 text-neutral-800',
    'primary' => 'bg-[#EFF6FF] text-[#1D4ED8]',
    'success' => 'bg-green-100 text-green-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'danger' => 'bg-red-100 text-red-800',
    'info' => 'bg-blue-100 text-blue-800',
    'accent' => 'bg-purple-100 text-purple-800',
][$variant] ?? 'bg-neutral-100 text-neutral-800';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold ' . $classes]) }}>
    {{ $slot }}
</span>

