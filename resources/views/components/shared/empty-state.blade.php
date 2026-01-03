@props([
    'icon' => null, // SVG path or component name
    'title',
    'description' => null,
    'action' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12']) }}>
    @if($icon)
        <div class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4">
            @if(str_starts_with($icon, '<svg'))
                {!! $icon !!}
            @else
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            @endif
        </div>
    @endif

    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
        {{ $title }}
    </h3>

    @if($description)
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            {{ $description }}
        </p>
    @endif

    @if($action && $actionLabel)
        <div>
            @if(str_starts_with($action, 'http') || str_starts_with($action, '/') || str_starts_with($action, 'route:'))
                <x-button href="{{ str_starts_with($action, 'route:') ? route(str_replace('route:', '', $action)) : $action }}" variant="primary">
                    {{ $actionLabel }}
                </x-button>
            @else
                <x-button @click="{{ $action }}" variant="primary">
                    {{ $actionLabel }}
                </x-button>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>

