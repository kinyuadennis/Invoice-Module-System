@props([
    'clientSecret' => null,
])

<div id="stripe-card-element-wrapper" class="space-y-4">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Card Details <span class="text-red-500">*</span>
    </label>
    
    <!-- Stripe Elements will mount here -->
    <div id="stripe-card-element" class="px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800">
        <!-- Stripe Elements will inject the card input here -->
    </div>
    
    <!-- Error display -->
    <div id="stripe-card-errors" class="text-sm text-red-600 dark:text-red-400" role="alert"></div>
    
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Your card details are processed securely by Stripe. We never store your card information.
    </p>
</div>

@push('scripts')
@if(config('services.stripe.key'))
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stripeKey = '{{ config("services.stripe.key") }}';
    
    if (!stripeKey) {
        console.error('Stripe key not configured');
        return;
    }

    const stripe = Stripe(stripeKey);
    const elements = stripe.elements();
    
    // Create card element
    const cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#32325d',
                fontFamily: 'Inter, system-ui, sans-serif',
                '::placeholder': {
                    color: '#aab7c4',
                },
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a',
            },
        },
    });

    // Mount card element
    const cardElementContainer = document.getElementById('stripe-card-element');
    if (cardElementContainer) {
        cardElement.mount('#stripe-card-element');
        
        // Handle real-time validation errors
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('stripe-card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }

    // Make stripe and cardElement available globally for form submission
    window.stripeInstance = stripe;
    window.stripeCardElement = cardElement;
});
</script>
@endif
@endpush

