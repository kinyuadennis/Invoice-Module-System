@props([
    'steps' => [], // Array of ['label' => 'Step 1', 'status' => 'completed'|'active'|'pending']
    'currentStep' => 1,
])

@php
// If steps not provided, use default
if (empty($steps)) {
    $steps = [
        ['label' => 'Plan Summary', 'status' => 'pending'],
        ['label' => 'Payment Details', 'status' => 'pending'],
        ['label' => 'Confirm', 'status' => 'pending'],
    ];
}

// Update step statuses based on currentStep
foreach ($steps as $index => &$step) {
    $stepNumber = $index + 1;
    if ($stepNumber < $currentStep) {
        $step['status'] = 'completed';
    } elseif ($stepNumber === $currentStep) {
        $step['status'] = 'active';
    } else {
        $step['status'] = 'pending';
    }
}
unset($step);
@endphp

<nav aria-label="Progress">
    <ol role="list" class="flex items-center">
        @foreach($steps as $index => $step)
            @php
                $stepNumber = $index + 1;
                $isLast = $stepNumber === count($steps);
            @endphp
            
            <li class="{{ $isLast ? '' : 'pr-8 sm:pr-20' }} relative">
                <div class="flex items-center">
                    @if($step['status'] === 'completed')
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-green-600 dark:bg-green-500">
                            <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
                            </svg>
                            <span class="absolute -inset-1.5"></span>
                        </span>
                        <span class="ml-4 text-sm font-medium text-gray-900 dark:text-white">{{ $step['label'] }}</span>
                    @elseif($step['status'] === 'active')
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-blue-600 dark:border-blue-500 bg-white dark:bg-gray-800" aria-current="step">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-600 dark:bg-blue-500"></span>
                            <span class="absolute -inset-1.5"></span>
                        </span>
                        <span class="ml-4 text-sm font-medium text-blue-600 dark:text-blue-400">{{ $step['label'] }}</span>
                    @else
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                            <span class="h-2.5 w-2.5 rounded-full bg-transparent"></span>
                            <span class="absolute -inset-1.5"></span>
                        </span>
                        <span class="ml-4 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $step['label'] }}</span>
                    @endif
                </div>
                
                @if(!$isLast)
                    <div class="absolute top-4 left-4 -ml-px h-0.5 w-full bg-gray-300 dark:bg-gray-600" aria-hidden="true"></div>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

