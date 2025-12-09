@props(['padding' => 'default'])

@php
$paddingClasses = [
    'none' => '',
    'sm' => 'p-3',
    'default' => 'p-5',
    'lg' => 'p-6',
    'xl' => 'p-8',
][$padding] ?? 'p-5';
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-gray-200 transition-shadow duration-150 hover:shadow-md ' . $paddingClasses]) }}>
    {{ $slot }}
</div>

