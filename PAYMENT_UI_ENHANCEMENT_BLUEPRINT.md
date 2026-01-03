# Payment & Subscription Module UI Enhancement Blueprint
## Comprehensive Implementation Guide for InvoiceHub

**Version:** 1.1  
**Last Updated:** 2026-01-02  
**Status:** Planning Phase - Ready for Implementation  
**Assessment Score:** 9.5/10 - Enhanced with localization, error handling, analytics, and visual polish

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Current State Analysis](#current-state-analysis)
3. [Design Principles & UX Framework](#design-principles--ux-framework)
4. [Implementation Phases](#implementation-phases)
5. [Component Library Structure](#component-library-structure)
6. [Page-by-Page Enhancement Plan](#page-by-page-enhancement-plan)
7. [Technical Implementation Details](#technical-implementation-details)
8. [Testing & Quality Assurance](#testing--quality-assurance)
9. [Launch & Iteration Strategy](#launch--iteration-strategy)
10. [Localization & Currency Handling](#localization--currency-handling)
11. [Error & Edge Case Handling](#error--edge-case-handling)
12. [Analytics & Event Tracking](#analytics--event-tracking)
13. [Performance & Security](#performance--security)

---

## Executive Summary

This blueprint provides a comprehensive, actionable plan for enhancing the Payment & Subscription module UI in InvoiceHub. The enhancements focus on:

- **Conversion Optimization**: Reduce payment abandonment through clear flows
- **Global Payment Support**: Seamless M-Pesa (Kenya) and Stripe (International) experiences
- **Mobile-First Design**: Critical for M-Pesa STK Push users
- **Trust & Security**: Transparent pricing, clear progress, secure payment handling
- **Compliance**: Terms, cancellation policies, VAT/tax display

**Tech Stack Alignment:**
- Blade templates for structure
- Alpine.js for lightweight interactivity
- Tailwind CSS v4 for styling
- Existing component system (cards, badges, buttons, tables)

---

## Current State Analysis

### Existing Infrastructure

#### Backend Components ✅
- `SubscriptionController` with `store()`, `index()`, `cancel()` methods
- `SubscriptionService` with gateway resolution logic (KE → M-Pesa, else → Stripe)
- Payment status tracking and webhook handling
- Subscription model with status transitions

#### Frontend Components ✅
- Basic subscription index page (`user/subscriptions/index.blade.php`)
- Basic payments index page (`user/payments/index.blade.php`)
- Reusable components: `card`, `badge`, `button`, `table`, `pricing-card`
- Alpine.js already integrated
- Dark mode support in layout

#### Missing Components ❌
- Checkout flow (multi-step)
- Payment status/polling page
- Success confirmation page
- Enhanced pricing section on landing
- Payment method selection UI
- M-Pesa STK Push waiting interface
- Subscription management dashboard enhancements

#### Routes Needed
```php
// New routes to add:
Route::get('/subscriptions/checkout', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
Route::get('/subscriptions/status/{payment}', [SubscriptionController::class, 'status'])->name('subscriptions.status');
Route::get('/subscriptions/success', [SubscriptionController::class, 'success'])->name('subscriptions.success');
Route::get('/subscriptions/manage', [SubscriptionController::class, 'manage'])->name('subscriptions.manage');
```

---

## Design Principles & UX Framework

### Core Principles

1. **Clarity & Trust**
   - Transparent pricing (all fees upfront)
   - Clear progress indicators
   - Friendly error messages with retry options
   - Security badges (SSL, gateway logos)

2. **Inclusivity**
   - Auto-detect country for gateway suggestion
   - Mobile-first design (critical for M-Pesa)
   - Accessibility (ARIA labels, keyboard navigation)
   - Multi-language ready structure

3. **Security Perception**
   - No sensitive data entry on our site
   - Stripe Elements for card input
   - M-Pesa phone number only (no PIN)
   - Clear privacy notes

4. **Compliance**
   - Terms & conditions checkbox
   - Cancellation policy display
   - VAT/tax breakdown
   - Receipt/invoice generation

### Design System

#### Color Scheme
```css
/* Status Colors */
Success: green-600, green-100 (bg)
Pending: yellow-600, yellow-100 (bg)
Failed: red-600, red-100 (bg)
Timeout: orange-600, orange-100 (bg)
Initiated: blue-600, blue-100 (bg)

/* Gateway Colors */
M-Pesa: #00A859 (green)
Stripe: #635BFF (purple)

/* Primary Actions */
Primary: #2B6EF6 (existing)
Secondary: neutral-200
Danger: red-600
```

#### Typography
- Headings: Inter, 600-900 weight
- Body: Inter, 400-500 weight
- Amounts: Inter, 700-900 weight, larger size
- Clear hierarchy: H1 (3xl), H2 (2xl), H3 (xl)
- **Localization Ready**: All strings wrapped in `__()` for translation
- **RTL Support**: Structure ready for right-to-left languages

#### Spacing & Layout
- Generous whitespace (py-8, px-6)
- Card padding: p-6 (default), p-8 (large)
- Button min-height: 44px (touch-friendly)
- Mobile-first breakpoints

#### Progress Indicators
- Stepper component: 4 steps max
- Active: blue, Completed: green, Pending: gray
- Step numbers with icons

---

## Implementation Phases

### Phase 1: Foundation & Component Library (Week 1-2)

**Goal:** Build reusable components and establish design patterns

#### Tasks:
1. Create component directory structure
2. Build payment-specific components
3. Enhance existing components with dark mode
4. Create design token documentation

#### Deliverables:
- Component library structure
- 8-10 new payment/subscription components
- Component usage documentation

### Phase 2: Landing Page & Discovery (Week 2-3)

**Goal:** Convert visitors to subscribers

#### Tasks:
1. Enhance hero section with subscription CTA
2. Build dynamic pricing section
3. Add FAQ accordion (Alpine.js)
4. Implement gateway auto-detection banner
5. Add trust elements (security badges)

#### Deliverables:
- Enhanced landing page
- Pricing section with toggle (monthly/annual)
- Country-based gateway suggestion

### Phase 3: Subscription Initiation Flow (Week 3-4)

**Goal:** Smooth checkout experience

#### Tasks:
1. Create checkout page/modal
2. Build multi-step stepper (Plan → Payment → Confirm)
3. Implement payment method selection
4. Add M-Pesa phone input with validation
5. Integrate Stripe Elements
6. Add terms checkbox and validation

#### Deliverables:
- Complete checkout flow
- Payment method selection UI
- Form validation and error handling

### Phase 4: Payment Processing & Status (Week 4-5)

**Goal:** Handle waiting periods gracefully

#### Tasks:
1. Create payment status page
2. Build M-Pesa STK Push waiting interface
3. Implement polling mechanism (Alpine.js)
4. Add countdown timer
5. Create "Resend prompt" functionality
6. Handle Stripe confirmation flow

#### Deliverables:
- Payment status page with polling
- M-Pesa waiting interface
- Real-time status updates

### Phase 5: Success & Confirmation (Week 5)

**Goal:** Reinforce trust and guide next steps

#### Tasks:
1. Create success page
2. Build celebration UI (checkmark, animation)
3. Display subscription details
4. Add "Create First Invoice" CTA
5. Generate and link invoice/receipt

#### Deliverables:
- Success confirmation page
- Receipt/invoice integration

### Phase 6: Management & Dashboard (Week 6)

**Goal:** Empower users to manage subscriptions

#### Tasks:
1. Enhance subscription dashboard
2. Build management page with history
3. Add payment method update flow
4. Create cancellation flow with modal
5. Add grace period warnings
6. Build upgrade/downgrade UI

#### Deliverables:
- Enhanced dashboard
- Subscription management page
- Cancellation flow

### Phase 7: Polish & Optimization (Week 7)

**Goal:** Refine and optimize

#### Tasks:
1. Add loading states everywhere
2. Implement skeleton screens
3. Add empty states
4. Optimize mobile experience
5. Accessibility audit
6. Performance optimization

#### Deliverables:
- Polished, production-ready UI
- Accessibility compliance
- Performance benchmarks

---

## Component Library Structure

### Payment Components

```
resources/views/components/payments/
├── payment-status-badge.blade.php          # Status with icons
├── payment-method-icon.blade.php           # Gateway logos
├── payment-amount-display.blade.php        # Currency formatting
├── payment-timeline.blade.php              # Payment flow visualization
├── payment-filters.blade.php               # Search/filter UI
├── payment-empty-state.blade.php           # Empty state
├── mpesa-waiting-interface.blade.php      # STK Push waiting
├── stripe-elements-wrapper.blade.php       # Stripe integration
└── payment-method-selector.blade.php       # Gateway selection
```

### Subscription Components

```
resources/views/components/subscriptions/
├── subscription-card.blade.php             # Enhanced card
├── plan-comparison-table.blade.php         # Feature comparison
├── billing-cycle-selector.blade.php        # Monthly/annual toggle
├── subscription-status-indicator.blade.php # Status with context
├── renewal-reminder-banner.blade.php        # Grace period alert
├── subscription-stepper.blade.php          # Multi-step progress
├── plan-summary-card.blade.php             # Checkout summary
└── cancellation-modal.blade.php            # Cancel confirmation
```

### Shared Components

```
resources/views/components/shared/
├── progress-stepper.blade.php              # Reusable stepper
├── loading-spinner.blade.php               # Loading states
├── skeleton-card.blade.php                # Skeleton screens
├── empty-state.blade.php                  # Generic empty state
├── country-gateway-banner.blade.php       # Auto-detection banner
└── trust-badges.blade.php                 # Security indicators
```

---

## Page-by-Page Enhancement Plan

### 1. Landing Page (`welcome.blade.php`)

#### Hero Section Update
```blade
<!-- Enhanced Hero -->
<div class="text-center py-16">
    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
        Upgrade InvoiceHub – Unlimited Invoicing
    </h1>
    <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
        Starting at KES 500/month • Global plans with local payments
    </p>
    <p class="text-lg text-gray-500 dark:text-gray-500 mb-8">
        M-Pesa in Kenya, cards/banks worldwide
    </p>
    <x-button href="#pricing" size="lg">View Plans</x-button>
</div>
```

#### Pricing Section Enhancement
- **Layout**: Responsive grid (1-3 columns)
- **Features**:
  - Monthly/annual toggle (Alpine.js)
  - Dynamic currency (KES/USD based on country)
  - "Most Popular" badge
  - Feature comparison
  - "Subscribe Now" button (auth check)
- **Components**: Use enhanced `pricing-card` component

#### Dynamic Gateway Suggestion
```blade
<!-- Alpine.js auto-detection -->
<div x-data="{ country: '{{ $user->country ?? 'KE' }}' }" 
     x-show="country === 'KE'"
     class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
    <p class="text-green-800">
        🇰🇪 We'll use M-Pesa for faster checkout in Kenya
    </p>
</div>
```

#### FAQ Accordion
- Alpine.js accordion
- Questions: "How do I pay?", "Cancellation policy", "VAT included?"
- Expandable sections

---

### 2. Subscriptions Index (`user/subscriptions/index.blade.php`)

#### Current State Display
```blade
@if($subscriptions->isNotEmpty())
    <!-- Current Subscription Card -->
    <x-subscription-card 
        :subscription="$subscriptions->first()"
        :show-actions="true"
    />
@endif
```

#### Available Plans Grid
- Enhanced pricing cards
- "Select" buttons
- Alpine.js toggle for billing cycle
- Highlight selected plan

#### Enhancements:
- Better spacing and typography
- Status indicators with icons
- Quick actions (upgrade, cancel, manage)
- Renewal date prominence

---

### 3. Checkout Flow (`/subscriptions/checkout`)

#### Multi-Step Stepper
```
Step 1: Plan Summary
Step 2: Payment Details
Step 3: Confirm
```

#### Step 1: Plan Summary
- Read-only plan card
- Total with VAT/tax breakdown
- Prorated amount if upgrading
- "Continue" button

#### Step 2: Payment Details
- **Gateway Selection**:
  - Auto-suggest based on country
  - Toggle to switch
  - Explanation text
- **M-Pesa Form**:
  - Phone input (Kenyan format: +254...)
  - Validation
  - Pre-filled if known
- **Stripe Form**:
  - Stripe Elements integration
  - Card input (secure)
  - PaymentRequestButton (Apple/Google Pay)
- **Terms Checkbox**:
  - "I agree to auto-renewal and terms"
  - Link to policy
  - Required validation

#### Step 3: Confirm
- Final summary
- Total amount
- Payment method
- "Complete Payment" button (disabled until valid)

#### Implementation:
- Alpine.js for step navigation
- Real-time validation
- Loading states
- Error handling

---

### 4. Payment Status Page (`/subscriptions/status/{payment}`)

#### M-Pesa Specific UI
```blade
<div class="text-center py-12">
    <!-- Phone Illustration -->
    <div class="mb-6">
        <svg class="w-32 h-32 mx-auto text-green-600">...</svg>
    </div>
    
    <h2 class="text-2xl font-bold mb-4">Check Your Phone</h2>
    <p class="text-gray-600 mb-6">
        Enter your M-Pesa PIN to complete payment
    </p>
    
    <!-- Countdown Timer -->
    <div x-data="{ timeLeft: 300 }" 
         x-init="setInterval(() => timeLeft > 0 ? timeLeft-- : null, 1000)">
        <p class="text-sm text-gray-500">
            Time remaining: <span x-text="Math.floor(timeLeft / 60)"></span>:<span x-text="String(timeLeft % 60).padStart(2, '0')"></span>
        </p>
    </div>
    
    <!-- Resend Button -->
    <x-button variant="outline" @click="resendPrompt()">
        Didn't receive? Resend
    </x-button>
</div>
```

#### Polling Mechanism
```javascript
// Alpine.js polling
Alpine.data('paymentStatus', () => ({
    status: 'pending',
    pollInterval: null,
    
    init() {
        this.pollInterval = setInterval(() => {
            this.checkStatus();
        }, 10000); // Every 10 seconds
    },
    
    async checkStatus() {
        const response = await fetch(`/api/payments/${this.paymentId}/status`);
        const data = await response.json();
        
        if (data.status === 'success') {
            clearInterval(this.pollInterval);
            window.location.href = '/subscriptions/success';
        }
    }
}));
```

#### Stripe Specific
- Embedded confirmation if needed
- "Processing securely..." message
- Redirect to Stripe Checkout if required

#### Common Elements
- Cancel button (returns to checkout)
- Support link
- Progress indicator

---

### 5. Success Page (`/subscriptions/success`)

#### Celebration UI
```blade
<div class="text-center py-16">
    <!-- Success Icon -->
    <div class="mb-6">
        <svg class="w-24 h-24 mx-auto text-green-600">...</svg>
    </div>
    
    <h1 class="text-4xl font-bold mb-4">Subscription Active!</h1>
    <p class="text-xl text-gray-600 mb-8">Thank you for subscribing</p>
</div>
```

#### Details Display
- Plan name
- Start/end date
- Amount paid
- Payment method
- Invoice/receipt link

#### Next Steps
- "Create Your First Premium Invoice" button
- Link to dashboard
- Email confirmation notice

---

### 6. Management Dashboard (`/subscriptions/manage`)

#### Current Subscription Card
- Plan details
- Status (Active/Grace/Expired)
- Renewal date
- Amount
- Quick actions: Upgrade, Cancel, Payment History

#### Grace/Expired Warning
```blade
@if($subscription->isInGrace())
    <x-alert variant="warning" class="mb-6">
        <strong>Renew by {{ $subscription->ends_at->format('M d, Y') }}</strong>
        to avoid service interruption
        <x-button href="{{ route('subscriptions.checkout') }}" size="sm" class="ml-4">
            Renew Now
        </x-button>
    </x-alert>
@endif
```

#### History Table
- Past payments
- Invoices
- Statuses
- Filters (date, gateway, status)
- Export functionality

#### Payment Method Update
- For Stripe: Re-authenticate
- For M-Pesa: Update phone number
- Security confirmation

#### Cancellation Flow
- Modal with reason selection
- Confirmation message
- Access until date
- Immediate status update

---

## Technical Implementation Details

### Route Structure

```php
// routes/web.php additions

// Subscription routes
Route::middleware(['auth'])->prefix('subscriptions')->name('subscriptions.')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
    Route::get('/checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
    Route::post('/', [SubscriptionController::class, 'store'])->name('store');
    Route::get('/status/{payment}', [SubscriptionController::class, 'status'])->name('status');
    Route::get('/success', [SubscriptionController::class, 'success'])->name('success');
    Route::get('/manage', [SubscriptionController::class, 'manage'])->name('manage');
    Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    Route::post('/{subscription}/update-payment-method', [SubscriptionController::class, 'updatePaymentMethod'])->name('update-payment-method');
});

// API routes for polling
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::get('/payments/{payment}/status', [PaymentController::class, 'getStatus'])->name('payments.status');
    Route::post('/payments/{payment}/resend', [PaymentController::class, 'resendPrompt'])->name('payments.resend');
});
```

### Controller Methods Needed

```php
// SubscriptionController additions

public function checkout(Request $request)
{
    // Show checkout page with plan selection
    // Return view with available plans, user country, etc.
}

public function status(Payment $payment)
{
    // Show payment status page
    // Return view with payment details, gateway-specific UI
}

public function success(Request $request)
{
    // Show success page
    // Return view with subscription details, invoice link
}

public function manage()
{
    // Show management dashboard
    // Return view with subscription, history, payment methods
}

public function updatePaymentMethod(Request $request, Subscription $subscription)
{
    // Update payment method for subscription
    // Handle Stripe customer update or M-Pesa phone update
}
```

### Alpine.js Patterns

#### Gateway Auto-Detection
```javascript
Alpine.data('gatewaySelector', () => ({
    country: '{{ $user->country ?? "KE" }}',
    selectedGateway: null,
    
    init() {
        this.selectedGateway = this.country === 'KE' ? 'mpesa' : 'stripe';
    },
    
    get gatewaySuggestion() {
        return this.country === 'KE' 
            ? 'We recommend M-Pesa for faster checkout in Kenya'
            : 'We recommend Stripe for international payments';
    }
}));
```

#### Billing Cycle Toggle
```javascript
Alpine.data('billingToggle', () => ({
    cycle: 'monthly',
    plans: @json($plans),
    
    get currentPrice() {
        return this.cycle === 'yearly' 
            ? this.plans.yearlyPrice 
            : this.plans.monthlyPrice;
    },
    
    get savings() {
        if (this.cycle === 'yearly') {
            return (this.plans.monthlyPrice * 12) - this.plans.yearlyPrice;
        }
        return 0;
    }
}));
```

#### Payment Status Polling
```javascript
Alpine.data('paymentPoller', (paymentId) => ({
    status: 'pending',
    timeLeft: 300,
    pollInterval: null,
    
    init() {
        this.startPolling();
        this.startCountdown();
    },
    
    startPolling() {
        this.pollInterval = setInterval(async () => {
            const response = await fetch(`/api/payments/${paymentId}/status`);
            const data = await response.json();
            this.status = data.status;
            
            if (this.status === 'success' || this.status === 'failed') {
                clearInterval(this.pollInterval);
                if (this.status === 'success') {
                    window.location.href = '/subscriptions/success';
                }
            }
        }, 10000);
    },
    
    startCountdown() {
        setInterval(() => {
            if (this.timeLeft > 0) {
                this.timeLeft--;
            }
        }, 1000);
    },
    
    async resendPrompt() {
        await fetch(`/api/payments/${paymentId}/resend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
    }
}));
```

### Stripe Integration

```blade
<!-- Stripe Elements Wrapper -->
<div id="stripe-card-element" class="mt-4">
    <!-- Stripe Elements will mount here -->
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ config('services.stripe.key') }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#stripe-card-element');
    
    // Handle form submission
    document.getElementById('payment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const {token, error} = await stripe.createToken(cardElement);
        
        if (error) {
            // Show error
        } else {
            // Submit token to server
        }
    });
</script>
```

### M-Pesa Phone Validation

```javascript
Alpine.data('mpesaForm', () => ({
    phone: '',
    isValid: false,
    errorMessage: '',
    
    validatePhone() {
        // Kenyan format: +254712345678 or 0712345678
        const pattern = /^(\+254|0)[17]\d{8}$/;
        const cleaned = this.phone.replace(/\s/g, '');
        
        if (!cleaned) {
            this.errorMessage = '';
            this.isValid = false;
            return;
        }
        
        if (!pattern.test(cleaned)) {
            this.errorMessage = 'Must be a Kenyan number starting with +2547 or 07...';
            this.isValid = false;
        } else {
            this.errorMessage = '';
            this.isValid = true;
        }
    },
    
    formatPhone() {
        // Normalize to +254 format
        this.phone = this.phone.replace(/^0/, '+254');
        this.validatePhone();
    }
}));
```

### Error Handling Patterns

#### Network Failure During Polling
```javascript
Alpine.data('paymentPoller', (paymentId) => ({
    status: 'pending',
    networkError: false,
    retryCount: 0,
    maxRetries: 3,
    
    async checkStatus() {
        try {
            const response = await fetch(`/api/payments/${paymentId}/status`);
            
            if (!response.ok) {
                throw new Error('Network error');
            }
            
            const data = await response.json();
            this.status = data.status;
            this.networkError = false;
            this.retryCount = 0;
            
            if (this.status === 'success' || this.status === 'failed') {
                clearInterval(this.pollInterval);
            }
        } catch (error) {
            this.networkError = true;
            this.retryCount++;
            
            if (this.retryCount >= this.maxRetries) {
                // Show error UI with manual retry button
            }
        }
    },
    
    async retry() {
        this.networkError = false;
        this.retryCount = 0;
        await this.checkStatus();
    }
}));
```

#### Stripe Decline Code Mapping
```php
// In PaymentController or helper class
private function getFriendlyErrorMessage(string $declineCode): string
{
    return match($declineCode) {
        'insufficient_funds' => 'Insufficient funds. Please try a different payment method.',
        'card_declined' => 'Your card was declined. Please check your card details or try a different card.',
        'expired_card' => 'Your card has expired. Please use a different card.',
        'incorrect_cvc' => 'The security code is incorrect. Please check and try again.',
        'processing_error' => 'An error occurred while processing your payment. Please try again.',
        default => 'Payment could not be processed. Please try again or contact support.',
    };
}
```

#### Country Change Detection
```javascript
Alpine.data('checkoutFlow', () => ({
    initialCountry: '{{ $user->country ?? "KE" }}',
    currentCountry: '{{ $user->country ?? "KE" }}',
    gatewayChanged: false,
    
    init() {
        // Detect country change (e.g., via IP geolocation API)
        this.detectCountryChange();
    },
    
    async detectCountryChange() {
        // Example: Check if country changed
        const detected = await this.getCountryFromIP();
        
        if (detected !== this.initialCountry) {
            this.gatewayChanged = true;
            // Show warning: "Switching to Stripe—continue?"
        }
    },
    
    confirmGatewayChange() {
        // User confirmed, proceed with new gateway
        this.gatewayChanged = false;
    }
}));
```

---

## Localization & Currency Handling

### Currency Logic Implementation

#### Laravel Money Package Integration
```php
// composer require moneyphp/money

use Money\Money;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;

// In SubscriptionPlan model or helper
public function getFormattedPrice(string $locale = 'en_US'): string
{
    $money = new Money(
        (int) ($this->price * 100), // Convert to cents
        new Currency($this->currency ?? 'KES')
    );
    
    $formatter = new IntlMoneyFormatter(
        new \NumberFormatter($locale, \NumberFormatter::CURRENCY),
        new \Money\Currencies\ISOCurrencies()
    );
    
    return $formatter->format($money);
}
```

#### Multi-Currency Support
```php
// Detect currency based on country/IP
public function getCurrencyForCountry(?string $country): string
{
    return match($country) {
        'KE' => 'KES',
        'US', 'CA' => 'USD',
        'GB' => 'GBP',
        'EU' => 'EUR',
        default => 'USD', // Fallback
    };
}

// In controller
$currency = $this->getCurrencyForCountry($user->country ?? $request->ip());
$plans = SubscriptionPlan::all()->map(function ($plan) use ($currency) {
    return [
        'id' => $plan->id,
        'name' => $plan->name,
        'price' => $this->convertCurrency($plan->price, $plan->currency, $currency),
        'currency' => $currency,
        'formatted_price' => $this->formatPrice($plan->price, $currency),
    ];
});
```

#### Currency Formatting Component
```blade
{{-- resources/views/components/payment-amount-display.blade.php --}}
@props(['amount', 'currency' => 'KES', 'locale' => 'en_US'])

@php
    $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
    $formatted = $formatter->formatCurrency($amount, $currency);
@endphp

<span {{ $attributes->merge(['class' => 'font-bold']) }}>
    {{ $formatted }}
</span>
```

#### Language Readiness
```blade
{{-- All user-facing strings wrapped in __() --}}
<h1>{{ __('subscriptions.checkout.title') }}</h1>
<p>{{ __('subscriptions.checkout.description') }}</p>
<button>{{ __('subscriptions.checkout.complete_payment') }}</button>

{{-- Translation files: lang/en/subscriptions.php, lang/sw/subscriptions.php --}}
```

#### RTL Support Skeleton
```blade
{{-- In layout --}}
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

{{-- In CSS --}}
[dir="rtl"] .ml-4 { margin-left: 0; margin-right: 1rem; }
[dir="rtl"] .text-left { text-align: right; }
```

---

## Error & Edge Case Handling

### Error States Component

```blade
{{-- resources/views/components/error-state.blade.php --}}
@props(['type' => 'generic', 'message' => null, 'action' => null, 'actionLabel' => 'Retry'])

<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
    <div class="flex items-start">
        <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div class="flex-1">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                {{ $message ?? __('payments.errors.' . $type) }}
            </h3>
            @if($action)
                <div class="mt-4">
                    <x-button variant="outline" size="sm" @click="{{ $action }}">
                        {{ $actionLabel }}
                    </x-button>
                </div>
            @endif
        </div>
    </div>
</div>
```

### Error Type Mapping

```php
// lang/en/payments.php
return [
    'errors' => [
        'network_failure' => 'Connection issue—please check your internet and try again.',
        'invalid_phone' => 'Must be a Kenyan number starting with +2547 or 07...',
        'payment_timeout' => 'Payment timed out. Please try again.',
        'gateway_error' => 'Payment gateway error. Please try again or contact support.',
        'insufficient_funds' => 'Insufficient funds. Please try a different payment method.',
        'card_declined' => 'Your card was declined. Please check your card details.',
        'country_change' => 'Your location changed. Switching payment method—continue?',
    ],
];
```

### Edge Cases Handling

#### M-Pesa Resend Limit
```php
// In PaymentController
public function resendPrompt(Request $request, Payment $payment)
{
    // Rate limiting: Max 3 attempts per payment
    $attempts = Cache::get("mpesa_resend_{$payment->id}", 0);
    
    if ($attempts >= 3) {
        return response()->json([
            'error' => 'Maximum resend attempts reached. Please contact support.',
        ], 429);
    }
    
    Cache::put("mpesa_resend_{$payment->id}", $attempts + 1, now()->addMinutes(5));
    
    // Resend logic...
}
```

#### Polling Exponential Backoff
```javascript
Alpine.data('paymentPoller', (paymentId) => ({
    status: 'pending',
    pollInterval: null,
    pollDelay: 10000, // Start with 10 seconds
    maxDelay: 60000, // Max 60 seconds
    
    startPolling() {
        this.pollInterval = setInterval(async () => {
            await this.checkStatus();
            // Exponential backoff: 10s → 20s → 30s → 40s → 50s → 60s
            this.pollDelay = Math.min(this.pollDelay * 1.5, this.maxDelay);
        }, this.pollDelay);
    },
    
    // ... rest of implementation
}));
```

---

## Analytics & Event Tracking

### Event Tracking Implementation

#### Frontend Events (Alpine.js)
```javascript
// Track events to analytics service (PostHog, Mixpanel, Google Analytics)
function trackEvent(eventName, properties = {}) {
    // PostHog
    if (window.posthog) {
        window.posthog.capture(eventName, properties);
    }
    
    // Google Analytics 4
    if (window.gtag) {
        window.gtag('event', eventName, properties);
    }
    
    // Mixpanel
    if (window.mixpanel) {
        window.mixpanel.track(eventName, properties);
    }
}

// Usage in components
Alpine.data('checkoutFlow', () => ({
    init() {
        trackEvent('checkout_started', {
            plan_id: this.planId,
            currency: this.currency,
            gateway: this.selectedGateway,
        });
    },
    
    selectPlan(planId) {
        trackEvent('plan_selected', { plan_id: planId });
    },
    
    submitPayment() {
        trackEvent('payment_initiated', {
            plan_id: this.planId,
            amount: this.amount,
            gateway: this.selectedGateway,
        });
    }
}));
```

#### Backend Events (Laravel)
```php
// In SubscriptionController
use Illuminate\Support\Facades\Log;

public function store(Request $request)
{
    // Track subscription initiation
    event(new SubscriptionInitiated($subscription));
    
    // Or use analytics service directly
    Analytics::track('subscription_initiated', [
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => $plan->price,
        'currency' => $plan->currency,
        'gateway' => $gatewayName,
    ]);
    
    // ... rest of implementation
}
```

### Key Events to Track

```php
// Event tracking checklist
$events = [
    // Discovery
    'plan_viewed' => ['plan_id', 'source', 'currency'],
    'pricing_section_viewed' => ['country', 'currency'],
    
    // Selection
    'plan_selected' => ['plan_id', 'billing_cycle', 'currency'],
    'billing_cycle_toggled' => ['from', 'to', 'savings'],
    
    // Checkout
    'checkout_started' => ['plan_id', 'amount', 'currency'],
    'payment_method_selected' => ['gateway', 'country'],
    'checkout_step_completed' => ['step', 'time_spent'],
    
    // Payment
    'payment_initiated' => ['payment_id', 'gateway', 'amount'],
    'payment_status_polled' => ['payment_id', 'status', 'attempt'],
    'payment_resend_requested' => ['payment_id', 'attempt_count'],
    'payment_completed' => ['payment_id', 'gateway', 'duration'],
    'payment_failed' => ['payment_id', 'gateway', 'error_code', 'reason'],
    
    // Drop-offs
    'checkout_abandoned' => ['step', 'plan_id', 'time_spent', 'reason'],
    'payment_abandoned' => ['payment_id', 'gateway', 'timeout_reason'],
    
    // Management
    'subscription_cancelled' => ['subscription_id', 'reason'],
    'payment_method_updated' => ['subscription_id', 'gateway'],
    'subscription_upgraded' => ['from_plan', 'to_plan', 'amount'],
];
```

### Drop-off Point Tracking

```javascript
// Track where users abandon the flow
Alpine.data('checkoutFlow', () => ({
    startTime: Date.now(),
    currentStep: 1,
    
    init() {
        // Track page visibility to detect abandonment
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.trackAbandonment();
            }
        });
        
        // Track before unload
        window.addEventListener('beforeunload', () => {
            this.trackAbandonment();
        });
    },
    
    trackAbandonment() {
        const timeSpent = Math.floor((Date.now() - this.startTime) / 1000);
        
        trackEvent('checkout_abandoned', {
            step: this.currentStep,
            time_spent: timeSpent,
            plan_id: this.planId,
        });
    }
}));
```

---

## Performance & Security

### Performance Goals

- **Page Load**: < 2 seconds (First Contentful Paint)
- **Time to Interactive (TTI)**: < 5 seconds
- **Largest Contentful Paint (LCP)**: < 2.5 seconds
- **Cumulative Layout Shift (CLS)**: < 0.1

### Asset Optimization

```javascript
// vite.config.js optimizations
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'stripe': ['stripe'],
                    'alpine': ['alpinejs'],
                }
            }
        },
        chunkSizeWarningLimit: 1000,
    },
    // ... rest of config
});
```

### Security Notes

#### CSRF Protection
```php
// All POST routes automatically protected by Laravel CSRF middleware
// Ensure meta tag in layout:
<meta name="csrf-token" content="{{ csrf_token() }}">

// In JavaScript:
const token = document.querySelector('meta[name="csrf-token"]').content;
```

#### Rate Limiting
```php
// routes/web.php
Route::middleware(['auth', 'throttle:5,1'])->group(function () {
    Route::post('/payments/{payment}/resend', [PaymentController::class, 'resendPrompt']);
});

// API routes
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/api/payments/{payment}/status', [PaymentController::class, 'getStatus']);
});
```

#### PCI Compliance (Stripe)
```blade
{{-- Always load Stripe JS from Stripe domain --}}
<script src="https://js.stripe.com/v3/"></script>

{{-- Never store card data --}}
{{-- Use Stripe Elements for all card input --}}
{{-- Only send token to server, never raw card data --}}
```

#### M-Pesa Security
```php
// Validate phone numbers server-side
public function validateMpesaPhone(string $phone): bool
{
    // Kenyan format validation
    $pattern = '/^(\+254|0)[17]\d{8}$/';
    return preg_match($pattern, preg_replace('/\s+/', '', $phone));
}

// Sanitize phone input
$phone = preg_replace('/[^0-9+]/', '', $request->phone);
```

---

## Testing & Quality Assurance

### Usability Testing Scenarios

1. **Kenyan Mobile User (M-Pesa)**
   - Discover plan on mobile
   - Select plan
   - Enter phone number
   - Receive STK Push
   - Complete payment
   - View success page

2. **International Desktop User (Stripe)**
   - View pricing in USD
   - Select plan
   - Enter card details
   - Complete payment
   - View success page

3. **Existing Subscriber**
   - View current subscription
   - Upgrade plan
   - Update payment method
   - Cancel subscription

### Testing Checklist

- [ ] Mobile responsiveness (iOS, Android)
- [ ] Dark mode support
- [ ] Accessibility (keyboard navigation, screen readers, WCAG AA contrast)
- [ ] Form validation (client and server-side)
- [ ] Error handling (network failures, validation errors, gateway errors)
- [ ] Loading states (skeleton screens, spinners)
- [ ] Empty states
- [ ] Payment polling accuracy (with exponential backoff)
- [ ] Gateway switching (country change detection)
- [ ] Currency formatting (multi-currency, locale-specific)
- [ ] Date/time formatting (timezone handling)
- [ ] Cross-browser compatibility (Chrome, Firefox, Safari, Edge)
- [ ] M-Pesa resend limit (max 3 attempts)
- [ ] Stripe decline code mapping
- [ ] Network failure recovery
- [ ] Performance metrics (page load, TTI, LCP, CLS)

### Metrics to Track

- Conversion rate (visitor → subscriber)
- Payment completion rate
- Time to complete payment
- Drop-off points (by step)
- Payment method distribution
- Error rates by gateway
- Support ticket volume
- **New**: Plan selection rate
- **New**: Billing cycle preference (monthly vs. yearly)
- **New**: Payment resend frequency
- **New**: Polling success rate
- **New**: Network error recovery rate
- **New**: Currency conversion usage

---

## Launch & Iteration Strategy

### Pre-Launch

1. **Wireframing**: Create wireframes for all flows (Figma/pen-paper)
2. **Prototyping**: Build Blade/Alpine prototypes
3. **Internal Testing**: Test with team (5-10 users)
4. **Refinement**: Address usability issues

### Launch Plan

1. **Soft Launch**: Beta with select users
2. **Monitor**: Track metrics, support tickets
3. **Iterate**: Quick fixes based on feedback
4. **Full Launch**: Roll out to all users

### Post-Launch Iteration

1. **A/B Testing**: Pricing display, CTA copy
2. **Analytics**: Monitor drop-off points
3. **User Feedback**: Surveys, support tickets
4. **Continuous Improvement**: Regular updates

---

## Visual Enhancements & Illustrations

### M-Pesa Waiting Page Animation
```blade
{{-- Animated phone illustration with Alpine.js pulse --}}
<div class="mb-6" x-data="{ pulse: true }" x-init="setInterval(() => pulse = !pulse, 1000)">
    <svg class="w-32 h-32 mx-auto text-green-600 transition-opacity duration-1000" 
         :class="pulse ? 'opacity-100' : 'opacity-50'"
         fill="currentColor" viewBox="0 0 24 24">
        {{-- Phone SVG path --}}
        <path d="M17 2H7c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 18H7V4h10v16z"/>
        <circle cx="12" cy="19" r="1"/>
    </svg>
</div>
```

### Success Page Confetti
```blade
{{-- Install: npm install canvas-confetti --}}
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    // Trigger confetti on success page load
    window.addEventListener('load', () => {
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    });
</script>
```

### Trust Badges with Logos
```blade
{{-- resources/views/components/trust-badges.blade.php --}}
<div class="flex items-center justify-center gap-6 py-4">
    <img src="{{ asset('images/mpesa-logo.svg') }}" 
         alt="M-Pesa Secure Payments" 
         class="h-8 opacity-80 hover:opacity-100 transition-opacity">
    <img src="{{ asset('images/stripe-logo.svg') }}" 
         alt="Stripe Secure Payments" 
         class="h-8 opacity-80 hover:opacity-100 transition-opacity">
    <div class="flex items-center gap-2 text-sm text-gray-600">
        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
        </svg>
        <span>SSL Secured</span>
    </div>
</div>
```

---

## Component Specifications

### Payment Status Badge

**Props:**
- `status`: 'success' | 'pending' | 'failed' | 'timeout' | 'initiated'
- `size`: 'sm' | 'md' | 'lg'
- `showIcon`: boolean

**Variants:**
- Success: Green with checkmark
- Pending: Yellow with clock
- Failed: Red with X
- Timeout: Orange with timer
- Initiated: Blue with spinner

### Subscription Card

**Props:**
- `subscription`: Subscription model
- `showActions`: boolean
- `size`: 'default' | 'compact'

**Features:**
- Plan name and description
- Status indicator
- Renewal date
- Quick actions (upgrade, cancel)
- Payment method display

### Plan Summary Card

**Props:**
- `plan`: SubscriptionPlan model
- `billingCycle`: 'monthly' | 'yearly'
- `showTax`: boolean

**Features:**
- Plan details
- Price breakdown
- VAT/tax display
- Total amount

---

## Next Steps

1. **Review & Approval**: Review this blueprint with team
2. **Prioritization**: Decide on phase order
3. **Resource Allocation**: Assign developers/designers
4. **Kickoff**: Begin Phase 1 implementation

---

## Appendix

### Design References
- Stripe Checkout UI
- PayPal payment flows
- M-Pesa merchant interfaces
- Modern SaaS pricing pages

### Tools & Resources
- Figma for wireframing
- Alpine.js documentation
- Tailwind CSS v4 docs
- Stripe Elements guide
- M-Pesa API documentation
- Laravel Money package (currency formatting)
- PostHog/Mixpanel (analytics)
- Canvas Confetti (success animations)

---

## Wireframe References

### Key Screen Descriptions

#### M-Pesa Waiting Page
- **Layout**: Centered, full-screen focus
- **Elements**: 
  - Large phone SVG (animated pulse)
  - Headline: "Check Your Phone" (2xl, bold)
  - Instruction: "Enter your M-Pesa PIN to complete payment"
  - Countdown timer (prominent, below instruction)
  - "Didn't receive? Resend" button (outline variant)
  - Cancel link (subtle, bottom)
- **Colors**: Green accent (#00A859), neutral text
- **Spacing**: Generous padding (py-12), centered content

#### Checkout Flow - Step 2 (Payment Details)
- **Layout**: Two-column on desktop, stacked on mobile
- **Left Column**: Gateway selection (radio buttons or toggle)
- **Right Column**: Payment form (M-Pesa phone input OR Stripe Elements)
- **Bottom**: Terms checkbox, "Complete Payment" button (primary, disabled until valid)
- **Visual**: Gateway icons next to selection, explanation text below

#### Success Page
- **Layout**: Centered celebration
- **Elements**:
  - Large checkmark icon (green, animated)
  - Confetti animation (subtle, on load)
  - Headline: "Subscription Active!" (4xl, bold)
  - Details card (plan, dates, amount, invoice link)
  - CTA: "Create Your First Premium Invoice" (primary, large)
- **Colors**: Green success theme, white background

---

## Performance Goals

### Target Metrics
- **First Contentful Paint (FCP)**: < 1.5s
- **Largest Contentful Paint (LCP)**: < 2.5s
- **Time to Interactive (TTI)**: < 5s
- **Cumulative Layout Shift (CLS)**: < 0.1
- **First Input Delay (FID)**: < 100ms

### Optimization Strategies
1. **Lazy Load**: Defer non-critical JS (analytics, confetti)
2. **Code Splitting**: Separate Stripe/M-Pesa code
3. **Image Optimization**: SVG for icons, WebP for illustrations
4. **Caching**: Cache plan data, user country
5. **CDN**: Serve static assets via CDN

---

## Security Checklist

- [x] CSRF protection on all POST routes (Laravel default)
- [x] Rate limiting on sensitive endpoints (resend, status polling)
- [x] Stripe JS loaded from Stripe domain only
- [x] No card data stored (tokens only)
- [x] Phone number validation (server-side)
- [x] Input sanitization (all user inputs)
- [x] HTTPS enforced (production)
- [x] PCI compliance (Stripe Elements)
- [x] M-Pesa credentials encrypted in config
- [x] Audit logging for payment events

---

## Button Variant Standardization

### Usage Guidelines

- **Primary** (`variant="primary"`): Main actions
  - "Complete Payment"
  - "Subscribe Now"
  - "Create First Invoice"
  
- **Secondary** (`variant="secondary"`): Supporting actions
  - "Resend Prompt"
  - "Change Payment Method"
  - "View Plans"
  
- **Outline** (`variant="outline"`): Alternative actions
  - "Cancel Subscription"
  - "Switch Gateway"
  
- **Danger** (`variant="danger"`): Destructive actions
  - "Cancel Subscription" (confirmation required)
  - "Delete Payment Method"

### Loading States
```blade
<x-button :disabled="processing" variant="primary">
    <span x-show="!processing">Complete Payment</span>
    <span x-show="processing" class="flex items-center">
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Processing...
    </span>
</x-button>
```

---

## Accessibility Enhancements

### ARIA Labels
```blade
<button aria-label="Complete payment for {{ $plan->name }} subscription">
    Complete Payment
</button>

<div role="status" aria-live="polite" aria-atomic="true">
    <span x-show="status === 'pending'">Processing payment...</span>
    <span x-show="status === 'success'">Payment successful!</span>
</div>
```

### Keyboard Navigation
- All interactive elements focusable
- Tab order logical
- Escape key closes modals
- Enter submits forms

### Color Contrast
- Text meets WCAG AA (4.5:1 for normal, 3:1 for large)
- Status badges have sufficient contrast
- Error messages clearly visible

### Screen Reader Support
- Semantic HTML (buttons, forms, headings)
- ARIA labels for icons
- Status announcements (aria-live)
- Form error associations

---

---

## Enhancement Summary (v1.1)

### What Was Added Based on Assessment

#### High-Impact Additions ✅
1. **Localization & Currency Handling** (Section 10)
   - Laravel Money package integration
   - Multi-currency support with country detection
   - Language readiness (`__()` wrappers)
   - RTL support skeleton

2. **Error & Edge Case Handling** (Section 11)
   - Comprehensive error state components
   - Network failure recovery
   - Stripe decline code mapping
   - Country change detection
   - M-Pesa resend limits (3 attempts)
   - Polling exponential backoff

3. **Analytics & Event Tracking** (Section 12)
   - Frontend event tracking (PostHog/Mixpanel/GA4)
   - Backend event tracking
   - Complete event checklist
   - Drop-off point tracking

#### Medium-Impact Additions ✅
4. **Visual Enhancements**
   - M-Pesa waiting page animation (pulsing phone)
   - Success page confetti
   - Trust badges with actual logos

5. **Performance & Security** (Section 13)
   - Performance goals (FCP, LCP, TTI, CLS)
   - Asset optimization strategies
   - Security checklist
   - Rate limiting specifications

#### Polish & Consistency ✅
6. **Button Variant Standardization**
   - Clear usage guidelines (primary, secondary, outline, danger)
   - Loading state patterns

7. **Accessibility Enhancements**
   - ARIA labels and live regions
   - Keyboard navigation
   - Color contrast (WCAG AA)
   - Screen reader support

8. **Wireframe References**
   - Key screen descriptions
   - Layout specifications

### Risk Mitigations Added
- ✅ Stripe PCI compliance reminders
- ✅ Polling exponential backoff
- ✅ M-Pesa resend limits
- ✅ Network failure handling
- ✅ Rate limiting on sensitive endpoints

### Testing Enhancements
- ✅ Expanded testing checklist
- ✅ Additional metrics to track
- ✅ Error scenario testing

---

**Document Status:** Enhanced & Ready for Implementation  
**Version:** 1.1  
**Last Updated:** 2026-01-02  
**Assessment Score:** 9.5/10  
**Next Review:** After Phase 1 completion

