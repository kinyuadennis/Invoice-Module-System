@props(['padding' => 'default'])

@php
$paddingClasses = [
    'none' => '',
    'sm' => 'p-4',
    'default' => 'p-6',
    'lg' => 'p-8',
][$padding] ?? 'p-6';
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-gray-200 ' . $paddingClasses]) }}>
    {{ $slot }}
</div>

