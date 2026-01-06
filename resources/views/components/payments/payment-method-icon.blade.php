@props([
    'gateway', // 'mpesa' | 'stripe'
    'size' => 'md', // 'sm' | 'md' | 'lg'
    'showLabel' => false,
])

@php
$gatewayConfig = [
    'mpesa' => [
        'color' => 'text-green-600 dark:text-green-400',
        'bg' => 'bg-green-50 dark:bg-green-900/20',
        'label' => 'M-Pesa',
        'icon' => 'M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z',
    ],
    'stripe' => [
        'color' => 'text-purple-600 dark:text-purple-400',
        'bg' => 'bg-purple-50 dark:bg-purple-900/20',
        'label' => 'Stripe',
        'icon' => 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z',
    ],
];

$config = $gatewayConfig[strtolower($gateway)] ?? $gatewayConfig['stripe'];
$sizeClasses = [
    'sm' => 'w-4 h-4',
    'md' => 'w-6 h-6',
    'lg' => 'w-8 h-8',
][$size] ?? 'w-6 h-6';
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <div class="flex items-center justify-center {{ $config['bg'] }} rounded-lg p-1.5">
        <svg class="{{ $sizeClasses }} {{ $config['color'] }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path d="{{ $config['icon'] }}"/>
        </svg>
    </div>
    @if($showLabel)
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200 dark:text-gray-300">{{ $config['label'] }}</span>
    @endif
</div>

