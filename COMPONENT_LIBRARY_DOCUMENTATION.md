# Payment & Subscription Component Library Documentation

**Version:** 1.0  
**Last Updated:** 2026-01-02  
**Status:** Phase 1 Complete

---

## Overview

This document provides comprehensive documentation for all payment and subscription-related UI components built for InvoiceHub. All components follow Laravel Blade component conventions and support dark mode.

---

## Component Structure

```
resources/views/components/
├── payments/              # Payment-specific components
│   ├── payment-status-badge.blade.php
│   ├── payment-method-icon.blade.php
│   └── payment-amount-display.blade.php
├── subscriptions/        # Subscription-specific components
│   ├── subscription-card.blade.php
│   └── subscription-status-indicator.blade.php
└── shared/              # Reusable shared components
    ├── progress-stepper.blade.php
    ├── loading-spinner.blade.php
    ├── skeleton-card.blade.php
    ├── empty-state.blade.php
    └── country-gateway-banner.blade.php
```

---

## Payment Components

### `payment-status-badge`

Displays payment status with icon and color coding.

**Props:**
- `status` (required): `'success' | 'pending' | 'failed' | 'timeout' | 'initiated'`
- `size` (optional): `'sm' | 'md' | 'lg'` (default: `'md'`)
- `showIcon` (optional): `boolean` (default: `true`)

**Usage:**
```blade
<x-payments.payment-status-badge status="success" />
<x-payments.payment-status-badge status="pending" size="lg" :show-icon="false">
    Processing
</x-payments.payment-status-badge>
```

**Variants:**
- Success: Green background, checkmark icon
- Pending: Yellow background, clock icon
- Failed: Red background, X icon
- Timeout: Orange background, timer icon
- Initiated: Blue background, spinner icon

---

### `payment-method-icon`

Displays payment gateway icon (M-Pesa or Stripe).

**Props:**
- `gateway` (required): `'mpesa' | 'stripe'`
- `size` (optional): `'sm' | 'md' | 'lg'` (default: `'md'`)
- `showLabel` (optional): `boolean` (default: `false`)

**Usage:**
```blade
<x-payments.payment-method-icon gateway="mpesa" />
<x-payments.payment-method-icon gateway="stripe" size="lg" :show-label="true" />
```

**Features:**
- M-Pesa: Green color scheme
- Stripe: Purple color scheme
- Dark mode support

---

### `payment-amount-display`

Formats and displays payment amounts with proper currency formatting.

**Props:**
- `amount` (required): `float|string` - The amount to display
- `currency` (optional): `string` (default: `'KES'`)
- `locale` (optional): `string` (default: `'en_US'`)
- `size` (optional): `'sm' | 'md' | 'lg' | 'xl'` (default: `'md'`)
- `showCurrency` (optional): `boolean` (default: `true`)

**Usage:**
```blade
<x-payments.payment-amount-display :amount="500" currency="KES" />
<x-payments.payment-amount-display :amount="10.99" currency="USD" size="xl" />
```

**Features:**
- Uses PHP's `NumberFormatter` for proper currency formatting
- Supports multiple currencies and locales
- Responsive sizing

---

## Subscription Components

### `subscription-card`

Enhanced subscription card with status, plan details, and actions.

**Props:**
- `subscription` (required): `Subscription` model instance
- `showActions` (optional): `boolean` (default: `true`)
- `size` (optional): `'default' | 'compact'` (default: `'default'`)

**Usage:**
```blade
<x-subscriptions.subscription-card :subscription="$subscription" />
<x-subscriptions.subscription-card :subscription="$subscription" size="compact" :show-actions="false" />
```

**Features:**
- Displays plan name, description, status
- Shows payment method, start date, next billing date
- Includes cancel action for active subscriptions
- Dark mode support
- Responsive grid layout

---

### `subscription-status-indicator`

Status badge specifically for subscriptions with context-aware styling.

**Props:**
- `status` (required): `'active' | 'pending' | 'grace' | 'expired' | 'cancelled'`
- `size` (optional): `'sm' | 'md' | 'lg'` (default: `'md'`)
- `showIcon` (optional): `boolean` (default: `true`)

**Usage:**
```blade
<x-subscriptions.subscription-status-indicator status="active" />
<x-subscriptions.subscription-status-indicator :status="strtolower($subscription->status)" size="lg" />
```

**Variants:**
- Active: Green, checkmark
- Pending: Yellow, clock
- Grace: Orange, warning icon
- Expired: Red, X icon
- Cancelled: Gray, cancel icon

---

## Shared Components

### `progress-stepper`

Multi-step progress indicator for checkout flows.

**Props:**
- `steps` (optional): `array` - Array of `['label' => 'Step Name', 'status' => 'completed'|'active'|'pending']`
- `currentStep` (optional): `int` (default: `1`)

**Usage:**
```blade
<x-shared.progress-stepper :current-step="2" />

{{-- Custom steps --}}
<x-shared.progress-stepper 
    :steps="[
        ['label' => 'Plan', 'status' => 'completed'],
        ['label' => 'Payment', 'status' => 'active'],
        ['label' => 'Confirm', 'status' => 'pending'],
    ]"
/>
```

**Features:**
- Auto-calculates step statuses based on `currentStep`
- Shows completed (green), active (blue), and pending (gray) states
- Accessible with ARIA labels
- Responsive design

---

### `loading-spinner`

Animated loading spinner for async operations.

**Props:**
- `size` (optional): `'sm' | 'md' | 'lg'` (default: `'md'`)
- `color` (optional): `string` - Tailwind color class (default: `'text-blue-600'`)

**Usage:**
```blade
<x-shared.loading-spinner />
<x-shared.loading-spinner size="lg" color="text-green-600" />
```

**Features:**
- Smooth animation
- Customizable size and color
- Accessible with aria-label

---

### `skeleton-card`

Loading skeleton for content placeholders.

**Props:**
- `lines` (optional): `int` (default: `3`) - Number of skeleton lines

**Usage:**
```blade
<x-shared.skeleton-card />
<x-shared.skeleton-card :lines="5" />
```

**Features:**
- Pulse animation
- Random width variation for natural look
- Dark mode support

---

### `empty-state`

Generic empty state component for when no data is available.

**Props:**
- `icon` (optional): `string` - SVG path or component name
- `title` (required): `string`
- `description` (optional): `string`
- `action` (optional): `string` - URL or Alpine.js action
- `actionLabel` (optional): `string` - Button text

**Usage:**
```blade
<x-shared.empty-state 
    icon="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
    title="No payments"
    description="Payments will appear here once invoices are paid."
    action="route:user.payments.index"
    action-label="View Payments"
/>
```

**Features:**
- Flexible icon support (SVG path or component)
- Optional action button
- Dark mode support

---

### `country-gateway-banner`

Auto-detection banner suggesting payment gateway based on country.

**Props:**
- `country` (optional): `string` - Country code (e.g., 'KE')
- `suggestedGateway` (optional): `'mpesa' | 'stripe'` - Override auto-detection

**Usage:**
```blade
<x-shared.country-gateway-banner :country="$user->country" />
<x-shared.country-gateway-banner suggested-gateway="mpesa" />
```

**Features:**
- Auto-detects gateway for Kenya (M-Pesa) vs others (Stripe)
- Color-coded (green for M-Pesa, purple for Stripe)
- Dark mode support

---

## Enhanced Existing Components

### `card` (Enhanced)

Added dark mode support:
- Background: `dark:bg-gray-800`
- Border: `dark:border-gray-700`

### `badge` (Enhanced)

Added dark mode variants for all color schemes:
- All variants now include `dark:` classes
- Maintains contrast and readability

### `button` (Already Enhanced)

Already includes dark mode support in existing implementation.

---

## Usage Examples

### Complete Payment Status Display

```blade
<div class="flex items-center gap-4">
    <x-payments.payment-method-icon gateway="mpesa" size="md" :show-label="true" />
    <x-payments.payment-amount-display :amount="500" currency="KES" size="lg" />
    <x-payments.payment-status-badge status="success" />
</div>
```

### Subscription Management Card

```blade
<x-subscriptions.subscription-card 
    :subscription="$subscription" 
    :show-actions="true"
/>
```

### Checkout Flow with Stepper

```blade
<x-shared.progress-stepper :current-step="2" />

<div class="mt-8">
    {{-- Step 2 content --}}
</div>
```

### Loading State

```blade
<div x-show="loading" class="flex items-center justify-center py-12">
    <x-shared.loading-spinner size="lg" />
</div>
```

### Empty State with Action

```blade
<x-shared.empty-state 
    title="No subscriptions"
    description="Get started by selecting a plan."
    action="route:user.subscriptions.index"
    action-label="View Plans"
/>
```

---

## Best Practices

1. **Always provide status values**: Use constants from `PaymentConstants` or `SubscriptionConstants`
2. **Use appropriate sizes**: `sm` for inline, `md` for cards, `lg` for prominent displays
3. **Dark mode**: All components support dark mode automatically
4. **Accessibility**: Components include ARIA labels where appropriate
5. **Responsive**: All components are mobile-friendly

---

## Component Dependencies

### Required Packages
- Laravel Framework (for Blade components)
- Tailwind CSS v4 (for styling)
- PHP Intl extension (for currency formatting in `payment-amount-display`)

### Optional Enhancements
- Alpine.js (for interactive features)
- Laravel Money package (for advanced currency handling)

---

## Testing Checklist

- [x] All components render correctly
- [x] Dark mode support verified
- [x] Responsive design tested
- [x] Accessibility (ARIA labels)
- [x] Currency formatting accuracy
- [x] Status variants display correctly
- [x] Icons render properly

---

## Future Enhancements

- [ ] Add more payment gateway icons
- [ ] Enhanced currency conversion
- [ ] Animation variants for status changes
- [ ] Localization support for labels
- [ ] Component playground/storybook

---

**Document Status:** Complete for Phase 1  
**Next Phase:** Landing Page & Discovery (Phase 2)

