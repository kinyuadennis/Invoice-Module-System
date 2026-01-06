@props([
    'country' => null,
    'suggestedGateway' => null, // 'mpesa' | 'stripe'
])

@php
// Auto-detect if not provided
if (!$suggestedGateway && $country) {
    $suggestedGateway = $country === 'KE' ? 'mpesa' : 'stripe';
}

$gatewayConfig = [
    'mpesa' => [
        'bg' => 'bg-green-50 dark:bg-green-900/20',
        'border' => 'border-green-200 dark:border-green-800',
        'text' => 'text-green-800 dark:text-green-200',
        'icon' => '🇰🇪',
        'message' => "We'll use M-Pesa for faster checkout in Kenya",
    ],
    'stripe' => [
        'bg' => 'bg-purple-50 dark:bg-purple-900/20',
        'border' => 'border-purple-200 dark:border-purple-800',
        'text' => 'text-purple-800 dark:text-purple-200',
        'icon' => '🌍',
        'message' => 'We recommend Stripe for international payments',
    ],
];

$config = $gatewayConfig[$suggestedGateway] ?? $gatewayConfig['stripe'];
@endphp

@if($suggestedGateway)
    <div {{ $attributes->merge(['class' => 'rounded-lg border p-4 ' . $config['bg'] . ' ' . $config['border']]) }}>
        <div class="flex items-center gap-3">
            <span class="text-2xl">{{ $config['icon'] }}</span>
            <p class="text-sm font-medium {{ $config['text'] }}">
                {{ $config['message'] }}
            </p>
        </div>
    </div>
@endif

