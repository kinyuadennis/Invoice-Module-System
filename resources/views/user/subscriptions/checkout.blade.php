@extends('layouts.user')

@section('title', 'Checkout')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div x-data="checkoutFlow({{ $selectedPlan?->id ?? 'null' }}, '{{ $suggestedGateway }}', '{{ $userCountry ?? 'KE' }}')" x-init="init()">
        <!-- Error Display -->
        <div x-show="errorMessage" x-cloak class="mb-6">
            <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-700 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-red-900 dark:text-red-200 mb-1">Error</h4>
                        <p class="text-sm text-red-800 dark:text-red-300" x-text="errorMessage"></p>
                    </div>
                    <button @click="errorMessage = ''" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div x-show="successMessage" x-cloak class="mb-6">
            <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-300 dark:border-green-700 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-green-800 dark:text-green-300" x-text="successMessage"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Stepper -->
        <x-shared.progress-stepper 
            :current-step="currentStep"
        />
        <!-- Step 1: Plan Summary -->
        <div x-show="currentStep === 1" x-cloak>
            <x-card class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Select Your Plan</h2>

                @if($activeSubscription)
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            You currently have an active subscription: <strong>{{ $activeSubscription->plan?->name }}</strong>
                        </p>
                    </div>
                @endif

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($availablePlans as $plan)
                        <div 
                            data-plan-id="{{ $plan->id }}"
                            data-plan-name="{{ $plan->name }}"
                            @click="selectPlan({{ $plan->id }}, {{ $plan->price }}, '{{ $plan->currency ?? 'KES' }}', '{{ $plan->name }}')"
                            :class="selectedPlanId === {{ $plan->id }} ? 'ring-2 ring-[#2B6EF6] bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-800'"
                            class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-6 cursor-pointer hover:border-[#2B6EF6] transition-all"
                        >
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $plan->name }}</h3>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                <x-payments.payment-amount-display 
                                    :amount="$plan->price" 
                                    :currency="$plan->currency ?? 'KES'"
                                    size="lg"
                                />
                                <span class="text-sm font-normal text-gray-600 dark:text-gray-400">
                                    / {{ $plan->billing_period ?? 'month' }}
                                </span>
                            </p>
                            @if($plan->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $plan->description }}</p>
                            @endif
                            <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                @php
                                    $features = is_array($plan->features) ? $plan->features : (is_string($plan->features) ? json_decode($plan->features, true) ?? explode(',', $plan->features) : []);
                                @endphp
                                @foreach($features as $feature)
                                    @php
                                        $featureText = is_string($feature) ? trim($feature) : '';
                                    @endphp
                                    @if($featureText)
                                        <li class="flex items-start">
                                            <svg class="w-4 h-4 text-[#2B6EF6] mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $featureText }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                @if($selectedPlan)
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Selected Plan</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $selectedPlan->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">
                                    <x-payments.payment-amount-display 
                                        :amount="$selectedPlan->price" 
                                        :currency="$selectedPlan->currency ?? 'KES'"
                                        size="lg"
                                    />
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex justify-end">
                    <x-button 
                        variant="primary" 
                        size="lg"
                        @click="nextStep()"
                        :disabled="!selectedPlanId"
                    >
                        Continue to Payment
                    </x-button>
                </div>
            </x-card>
        </div>

        <!-- Step 2: Payment Details -->
        <div x-show="currentStep === 2" x-cloak>
            <x-card class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Payment Details</h2>

                <!-- Gateway Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Payment Method
                    </label>
                    
                    <x-shared.country-gateway-banner 
                        :country="$userCountry ?? 'KE'" 
                        :suggested-gateway="$suggestedGateway"
                        class="mb-4"
                    />

                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- M-Pesa Option -->
                        <div 
                            @click="selectedGateway = 'mpesa'"
                            :class="selectedGateway === 'mpesa' ? 'ring-2 ring-[#2B6EF6] bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-800'"
                            class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer hover:border-[#2B6EF6] transition-all"
                        >
                            <div class="flex items-center gap-3">
                                <x-payments.payment-method-icon gateway="mpesa" size="md" />
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">M-Pesa</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Pay via M-Pesa STK Push</p>
                                </div>
                                <input 
                                    type="radio" 
                                    name="gateway" 
                                    value="mpesa" 
                                    x-model="selectedGateway"
                                    class="w-5 h-5 text-[#2B6EF6]"
                                >
                            </div>
                        </div>

                        <!-- Stripe Option -->
                        <div 
                            @click="selectedGateway = 'stripe'"
                            :class="selectedGateway === 'stripe' ? 'ring-2 ring-[#2B6EF6] bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-800'"
                            class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 cursor-pointer hover:border-[#2B6EF6] transition-all"
                        >
                            <div class="flex items-center gap-3">
                                <x-payments.payment-method-icon gateway="stripe" size="md" />
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Stripe</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Card, Bank, Apple Pay, Google Pay</p>
                                </div>
                                <input 
                                    type="radio" 
                                    name="gateway" 
                                    value="stripe" 
                                    x-model="selectedGateway"
                                    class="w-5 h-5 text-[#2B6EF6]"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- M-Pesa Phone Input -->
                <div x-show="selectedGateway === 'mpesa'" x-cloak class="mb-6">
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        M-Pesa Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="tel" 
                        id="phone"
                        name="phone"
                        x-model="phone"
                        @input="validatePhone()"
                        placeholder="+254712345678 or 0712345678"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#2B6EF6] focus:border-transparent dark:bg-gray-800 dark:text-white"
                        :class="phoneError ? 'border-red-500' : ''"
                    >
                    <p x-show="phoneError" x-cloak class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="phoneError"></p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Enter your M-Pesa registered phone number
                    </p>
                </div>

                <!-- Stripe Elements -->
                <div x-show="selectedGateway === 'stripe'" x-cloak class="mb-6">
                    <x-payments.stripe-elements-wrapper />
                </div>

                <!-- Terms Checkbox -->
                <div class="mb-6">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input 
                            type="checkbox" 
                            x-model="termsAccepted"
                            class="mt-1 w-4 h-4 text-[#2B6EF6] border-gray-300 rounded focus:ring-[#2B6EF6]"
                        >
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            I agree to the 
                            <a href="#" class="text-[#2B6EF6] hover:underline">Terms of Service</a> 
                            and 
                            <a href="#" class="text-[#2B6EF6] hover:underline">Privacy Policy</a>.
                            I understand that my subscription will auto-renew unless cancelled.
                        </span>
                    </label>
                </div>

                <div class="flex justify-between">
                    <x-button 
                        variant="outline" 
                        size="lg"
                        @click="previousStep()"
                    >
                        Back
                    </x-button>
                    <x-button 
                        variant="primary" 
                        size="lg"
                        @click="nextStep()"
                        :disabled="!canProceedToConfirm()"
                    >
                        Continue to Confirm
                    </x-button>
                </div>
            </x-card>
        </div>

        <!-- Step 3: Confirm -->
        <div x-show="currentStep === 3" x-cloak>
            <x-card class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Confirm Your Subscription</h2>

                <!-- Summary -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Plan</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="selectedPlanName"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Payment Method</span>
                            <span class="font-medium text-gray-900 dark:text-white uppercase" x-text="selectedGateway"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Amount</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="formatAmount(selectedAmount)"></span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">Total</span>
                                <span class="text-xl font-bold text-[#2B6EF6]" x-text="formatAmount(selectedAmount)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <x-button 
                        variant="outline" 
                        size="lg"
                        @click="previousStep()"
                    >
                        Back
                    </x-button>
                    <x-button 
                        variant="primary" 
                        size="lg"
                        @click="submitPayment()"
                        :disabled="processing"
                    >
                        <span x-show="!processing">Complete Payment</span>
                        <span x-show="processing" class="flex items-center">
                            <x-shared.loading-spinner size="sm" color="text-white" class="mr-2" />
                            Processing...
                        </span>
                    </x-button>
                </div>
            </x-card>
        </div>
    </div>
    </div>
</div>

@push('scripts')
<script>
function checkoutFlow(initialPlanId, suggestedGateway, userCountry) {
    return {
        currentStep: 1,
        selectedPlanId: initialPlanId,
        selectedPlanName: '',
        selectedAmount: 0,
        selectedCurrency: 'KES',
        selectedGateway: suggestedGateway,
        phone: '',
        phoneError: '',
        termsAccepted: false,
        processing: false,
        errorMessage: '',
        successMessage: '',

        init() {
            // If plan is pre-selected, set its details
            if (this.selectedPlanId) {
                const planElement = document.querySelector(`[data-plan-id="${this.selectedPlanId}"]`);
                if (planElement) {
                    this.selectedPlanName = planElement.dataset.planName || 'Selected Plan';
                }
            }
        },

        selectPlan(planId, price, currency, planName) {
            this.selectedPlanId = planId;
            this.selectedAmount = price;
            this.selectedCurrency = currency;
            this.selectedPlanName = planName || 'Selected Plan';
        },

        validatePhone() {
            // Kenyan format: +254712345678 or 0712345678
            const pattern = /^(\+254|0)[17]\d{8}$/;
            const cleaned = this.phone.replace(/\s/g, '');
            
            if (!cleaned) {
                this.phoneError = '';
                return false;
            }
            
            if (!pattern.test(cleaned)) {
                this.phoneError = 'Must be a Kenyan number starting with +2547 or 07...';
                return false;
            }
            
            this.phoneError = '';
            return true;
        },

        formatPhone() {
            // Normalize to +254 format
            this.phone = this.phone.replace(/^0/, '+254');
            this.validatePhone();
        },

        canProceedToConfirm() {
            if (!this.selectedPlanId || !this.selectedGateway || !this.termsAccepted) {
                return false;
            }

            if (this.selectedGateway === 'mpesa') {
                return this.validatePhone();
            }

            // For Stripe, validation happens on Stripe Elements side
            return true;
        },

        nextStep() {
            if (this.currentStep === 1 && !this.selectedPlanId) {
                return;
            }
            if (this.currentStep === 2 && !this.canProceedToConfirm()) {
                return;
            }
            this.currentStep++;
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },

        formatAmount(amount) {
            if (!amount) return 'KES 0';
            return new Intl.NumberFormat('en-KE', {
                style: 'currency',
                currency: this.selectedCurrency || 'KES',
            }).format(amount);
        },

        async submitPayment() {
            if (!this.canProceedToConfirm()) {
                return;
            }

            this.processing = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                // For Stripe, first confirm the payment method
                let paymentMethodId = null;
                if (this.selectedGateway === 'stripe' && window.stripeInstance && window.stripeCardElement) {
                    // Create payment method from card element
                    const { paymentMethod, error } = await window.stripeInstance.createPaymentMethod({
                        type: 'card',
                        card: window.stripeCardElement,
                    });

                    if (error) {
                        this.errorMessage = error.message || 'Failed to create payment method. Please check your card details.';
                        this.processing = false;
                        // Scroll to error
                        this.$nextTick(() => {
                            document.querySelector('[x-show="errorMessage"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        });
                        return;
                    }

                    paymentMethodId = paymentMethod.id;
                }

                // Format phone number for M-Pesa
                let phoneNumber = null;
                if (this.selectedGateway === 'mpesa') {
                    phoneNumber = this.phone.replace(/^0/, '+254').replace(/\s/g, '');
                }

                const formData = {
                    subscription_plan_id: this.selectedPlanId,
                    phone: phoneNumber,
                    payment_method: paymentMethodId,
                    _token: document.querySelector('meta[name="csrf-token"]').content,
                };

                const response = await fetch('{{ route("user.subscriptions.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': formData._token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(formData),
                });

                const data = await response.json();

                if (!response.ok) {
                    // Handle HTTP errors (validation, etc.)
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat();
                        this.errorMessage = errorMessages.join(' ') || 'Validation failed. Please check your input.';
                    } else {
                        this.errorMessage = data.message || data.error || 'Payment initiation failed. Please try again.';
                    }
                    this.processing = false;
                    // Scroll to error
                    this.$nextTick(() => {
                        document.querySelector('[x-show="errorMessage"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                    return;
                }

                if (data.success) {
                    // Show success message briefly before redirect
                    this.successMessage = 'Payment initiated successfully! Redirecting...';
                    
                    // Redirect to payment status page or subscriptions page
                    setTimeout(() => {
                        if (data.payment_id) {
                            window.location.href = `{{ route("user.subscriptions.payment-status", ":payment") }}`.replace(':payment', data.payment_id);
                        } else if (data.transaction_id) {
                            // M-Pesa - redirect to status page with transaction ID
                            window.location.href = `{{ route("user.subscriptions.index") }}?payment_id=${data.payment_id || data.transaction_id}`;
                        } else {
                            // Stripe subscription created - redirect to subscriptions page
                            window.location.href = '{{ route("user.subscriptions.index") }}';
                        }
                    }, 1000);
                } else {
                    this.errorMessage = data.error || data.message || 'Payment initiation failed. Please try again.';
                    this.processing = false;
                    // Scroll to error
                    this.$nextTick(() => {
                        document.querySelector('[x-show="errorMessage"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                }
            } catch (error) {
                console.error('Payment submission error:', error);
                this.errorMessage = 'An unexpected error occurred. Please try again or contact support if the problem persists.';
                this.processing = false;
                // Scroll to error
                this.$nextTick(() => {
                    document.querySelector('[x-show="errorMessage"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            }
        },
    };
}
</script>
@endpush

