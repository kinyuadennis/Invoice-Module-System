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

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 transition-all duration-200 hover:shadow-lg ' . $paddingClasses]) }}>
    {{ $slot }}
</div>