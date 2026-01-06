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

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-[#1F1F1F] rounded-2xl shadow-sm dark:shadow-[0_8px_24px_rgba(0,0,0,0.3)] border border-gray-100 dark:border-[#2A2A2A] transition-all duration-300 hover:shadow-lg hover: dark:hover:shadow-[0_12px_40px_rgba(0,0,0,0.4)] ' . $paddingClasses]) }}>
    {{ $slot }}
</div>