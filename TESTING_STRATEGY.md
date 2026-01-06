# Payment & Subscription Module - Testing Strategy

**Version**: 1.0  
**Date**: 2026-01-02  
**Status**: Draft - Awaiting Review  
**Based on**: InvoiceHub Payment & Subscription Module Blueprint v1

---

## Overview

This document outlines the comprehensive testing strategy for the Payment & Subscription Module. Testing is **critical** for financial systems and must ensure:

- **100% coverage** on state machines and invariant checks
- **80%+ overall code coverage**
- **Idempotency** of all webhook/callback handlers
- **Financial immutability** validation
- **Gateway-agnostic** domain logic verification

---

## Testing Philosophy

### Core Principles

1. **Test-Driven Development (TDD) for Critical Paths**
   - Write failing tests first for state machines
   - Implement to pass tests
   - Refactor with confidence

2. **Isolation & Independence**
   - Each test should be independent
   - Use database transactions or in-memory SQLite
   - Mock external services (gateways, APIs)

3. **Financial Safety First**
   - Never test with real payment gateways in unit/feature tests
   - Use sandbox/test mode for integration tests
   - Verify immutability assertions

4. **Blueprint Compliance**
   - Tests must verify blueprint invariants
   - State machine transitions must be exhaustively tested
   - Gateway-agnostic logic must be verified

---

## Test Structure & Organization

### Directory Structure

```
tests/
├── Unit/
│   ├── Models/
│   │   ├── SubscriptionTest.php          # State machines, invariants
│   │   └── PaymentTest.php               # State machines, invariants
│   ├── Services/
│   │   └── SubscriptionServiceTest.php    # Orchestration logic
│   ├── Repositories/
│   │   └── SubscriptionRepositoryTest.php
│   ├── Adapters/
│   │   ├── MpesaGatewayAdapterTest.php   # Mocked gateway calls
│   │   └── StripeGatewayAdapterTest.php   # Mocked gateway calls
│   └── DTOs/
│       └── PaymentContextTest.php         # Immutability, validation
├── Feature/
│   ├── Subscriptions/
│   │   ├── CreateSubscriptionTest.php
│   │   ├── CancelSubscriptionTest.php
│   │   └── SubscriptionLifecycleTest.php
│   ├── Payments/
│   │   ├── PaymentInitiationTest.php
│   │   ├── PaymentConfirmationTest.php
│   │   └── PaymentIdempotencyTest.php
│   ├── Webhooks/
│   │   ├── StripeWebhookTest.php
│   │   ├── MpesaWebhookTest.php
│   │   └── WebhookIdempotencyTest.php
│   └── Commands/
│       ├── ProcessMpesaRenewalsTest.php
│       ├── ProcessPaymentTimeoutsTest.php
│       └── ReconcileStripeSubscriptionsTest.php
└── Integration/
    ├── PaymentFlowTest.php                # End-to-end with sandbox
    ├── SubscriptionLifecycleTest.php      # Full lifecycle
    └── WebhookProcessingTest.php          # Real webhook payloads
```

---

## Test Categories & Priorities

### Priority 1: Critical Financial Logic (MUST HAVE)

#### 1.1 State Machine Tests (100% Coverage Required)

**Subscription State Machine**
- ✅ Valid transitions: PENDING → ACTIVE, ACTIVE → GRACE, GRACE → ACTIVE, GRACE → EXPIRED, ACTIVE → CANCELLED
- ❌ Invalid transitions: ACTIVE → PENDING, EXPIRED → ACTIVE (without payment), etc.
- ✅ Invariant: Cannot be ACTIVE without successful payment
- ✅ Invariant: Cannot revert to ACTIVE after EXPIRED without new payment
- ✅ Invariant: Gateway field immutable after creation

**Payment State Machine**
- ✅ Valid transitions: INITIATED → SUCCESS, INITIATED → FAILED, INITIATED → TIMEOUT
- ❌ Invalid transitions: SUCCESS → FAILED, FAILED → SUCCESS (terminal states)
- ✅ Invariant: Terminal states are immutable
- ✅ Invariant: Cannot transition twice to terminal state

**Test Approach:**
```php
// Example structure
public function test_subscription_can_transition_from_pending_to_active_with_payment()
public function test_subscription_cannot_transition_to_active_without_payment()
public function test_subscription_cannot_revert_from_expired_to_active_without_new_payment()
public function test_payment_cannot_transition_twice_to_terminal_state()
```

#### 1.2 Idempotency Tests (CRITICAL)

**Webhook/Callback Idempotency**
- ✅ Duplicate webhook callbacks don't create duplicate payments
- ✅ Duplicate webhook callbacks don't change payment status
- ✅ Idempotency key prevents duplicate payment creation
- ✅ Gateway reference uniqueness enforced

**Test Approach:**
```php
public function test_duplicate_stripe_webhook_is_ignored()
public function test_duplicate_mpesa_callback_is_ignored()
public function test_idempotency_key_prevents_duplicate_payment()
```

#### 1.3 Financial Immutability Tests

**Invoice Immutability**
- ✅ Invoice cannot be modified after creation
- ✅ Invoice snapshot is immutable
- ✅ Payment amounts cannot be changed after confirmation
- ✅ Subscription invoices are created only after PaymentConfirmed

**Test Approach:**
```php
public function test_invoice_cannot_be_modified_after_creation()
public function test_invoice_snapshot_is_immutable()
public function test_subscription_invoice_created_only_after_payment_confirmed()
```

### Priority 2: Business Logic (HIGH PRIORITY)

#### 2.1 Service Orchestration Tests

**SubscriptionService Tests**
- ✅ Gateway selection (KE → M-Pesa, else → Stripe)
- ✅ Payment initiation flow
- ✅ Payment confirmation flow
- ✅ Subscription activation on payment success
- ✅ Renewal failure handling (transition to GRACE)
- ✅ Renewal success handling (transition to ACTIVE)
- ✅ Error handling and rollback

**Test Approach:**
```php
public function test_gateway_selection_kenya_user_uses_mpesa()
public function test_gateway_selection_non_kenya_user_uses_stripe()
public function test_payment_initiation_creates_payment_and_emits_event()
public function test_payment_confirmation_activates_subscription()
public function test_renewal_failure_transitions_to_grace()
```

#### 2.2 Repository Tests

**SubscriptionRepository Tests**
- ✅ CRUD operations
- ✅ Audit log creation
- ✅ Finding active subscriptions
- ✅ Query methods

#### 2.3 Gateway Adapter Tests (Mocked)

**MpesaGatewayAdapter Tests**
- ✅ STK Push initiation (mocked HTTP call)
- ✅ Callback parsing and validation
- ✅ Error handling
- ✅ `supportsRecurring()` returns false

**StripeGatewayAdapter Tests**
- ✅ PaymentIntent creation (mocked Stripe API)
- ✅ Webhook parsing and validation
- ✅ Subscription cancellation
- ✅ `supportsRecurring()` returns true

**Test Approach:**
- Mock HTTP client for M-Pesa
- Mock Stripe SDK for Stripe
- Verify correct API calls are made
- Verify error handling

### Priority 3: Integration & End-to-End (MEDIUM PRIORITY)

#### 3.1 Payment Flow Integration Tests

**M-Pesa Flow (Sandbox)**
- ✅ End-to-end: Initiate → STK Push → Callback → Confirmation
- ✅ Error scenarios: User cancels, timeout, network failure
- ✅ Idempotency with real callbacks

**Stripe Flow (Test Mode)**
- ✅ End-to-end: Initiate → PaymentIntent → Webhook → Confirmation
- ✅ Error scenarios: Payment fails, webhook delayed
- ✅ Idempotency with real webhooks

**Test Approach:**
- Use sandbox/test credentials
- Real API calls (but test mode)
- Verify database state after flow
- Clean up test data

#### 3.2 Subscription Lifecycle Integration Tests

- ✅ Full lifecycle: Create → Pay → Activate → Renew → Cancel
- ✅ Grace period flow: Renewal fails → GRACE → Payment succeeds → ACTIVE
- ✅ Expiration flow: GRACE → EXPIRED

#### 3.3 Webhook Processing Integration Tests

- ✅ Real webhook payloads (from Stripe test mode)
- ✅ Real M-Pesa callbacks (from sandbox)
- ✅ Signature verification
- ✅ IP whitelist validation (M-Pesa)

### Priority 4: Feature Tests (USER-FACING)

#### 4.1 User Subscription Flow

- ✅ User can view available plans
- ✅ User can initiate subscription
- ✅ Gateway auto-detection based on country
- ✅ Payment initiation UI flow
- ✅ Subscription status display

#### 4.2 Cancellation Flow

- ✅ User can cancel active subscription
- ✅ Cancellation updates subscription status
- ✅ Gateway-specific cancellation (Stripe)
- ✅ M-Pesa cancellation (internal only)

#### 4.3 Error Scenarios

- ✅ Payment timeout handling
- ✅ Webhook failure and retry
- ✅ Country change handling
- ✅ Reconciliation discrepancies

---

## Testing Tools & Setup

### Required Tools

1. **PHPUnit** (already configured)
   - Version: 11.x
   - Configuration: `phpunit.xml`

2. **Mocking**
   - PHPUnit's built-in mocking
   - Mock HTTP clients for gateway APIs
   - Mock Stripe SDK

3. **Database**
   - In-memory SQLite for unit tests
   - Test database for integration tests
   - Migrations run before each test suite

4. **Queue Testing**
   - `Queue::fake()` for job testing
   - `Bus::fake()` for command testing

5. **Event Testing**
   - `Event::fake()` to verify events are fired
   - Assert event payloads

6. **Notification Testing**
   - `Notification::fake()` to verify notifications sent
   - Assert notification content

### Test Environment Configuration

```php
// phpunit.xml already configured with:
- DB_CONNECTION=sqlite
- DB_DATABASE=:memory:
- QUEUE_CONNECTION=sync
- MAIL_MAILER=array
```

### Test Data Management

**Factories Needed:**
- `SubscriptionFactory` - Create test subscriptions
- `PaymentFactory` - Create test payments
- `UserFactory` - Already exists
- `CompanyFactory` - If needed
- `SubscriptionPlanFactory` - If needed

**Seeding:**
- Minimal seeders for integration tests
- Use factories in tests, not seeders

---

## Test Implementation Strategy

### Phase 1: Foundation Tests (Start Here)

**Estimated: 8-10 hours**

1. **Create Factories**
   - `SubscriptionFactory`
   - `PaymentFactory`
   - Update `UserFactory` to include `country`

2. **State Machine Tests**
   - `SubscriptionTest` - All transitions and invariants
   - `PaymentTest` - All transitions and invariants
   - **Target: 100% coverage on state machines**

3. **Basic Service Tests**
   - `SubscriptionServiceTest` - Gateway selection, basic flows
   - Mock adapters for gateway calls

### Phase 2: Core Business Logic (Critical)

**Estimated: 10-12 hours**

1. **Idempotency Tests**
   - Webhook idempotency
   - Payment initiation idempotency
   - Duplicate callback handling

2. **Service Orchestration Tests**
   - Complete `SubscriptionServiceTest`
   - Payment initiation and confirmation flows
   - Renewal handling

3. **Repository Tests**
   - `SubscriptionRepositoryTest`
   - Audit logging verification

### Phase 3: Gateway Adapters (Mocked)

**Estimated: 6-8 hours**

1. **MpesaGatewayAdapter Tests**
   - Mock HTTP client
   - Test STK Push initiation
   - Test callback parsing
   - Error scenarios

2. **StripeGatewayAdapter Tests**
   - Mock Stripe SDK
   - Test PaymentIntent creation
   - Test webhook parsing
   - Test subscription cancellation

### Phase 4: Integration Tests (Sandbox/Test Mode)

**Estimated: 8-10 hours**

1. **Payment Flow Integration**
   - M-Pesa end-to-end (sandbox)
   - Stripe end-to-end (test mode)
   - Error scenarios

2. **Webhook Processing Integration**
   - Real webhook payloads
   - Signature verification
   - IP whitelist (M-Pesa)

3. **Subscription Lifecycle Integration**
   - Full lifecycle test
   - Grace period flow
   - Expiration flow

### Phase 5: Feature Tests (User-Facing)

**Estimated: 4-6 hours**

1. **Subscription Management**
   - Create subscription flow
   - Cancel subscription flow
   - View subscriptions

2. **Error Scenarios**
   - Payment failures
   - Timeout handling
   - Country change

---

## Test Coverage Requirements

### Minimum Coverage Targets

| Component | Target Coverage | Priority |
|-----------|----------------|----------|
| State Machines (Subscription, Payment) | **100%** | **CRITICAL** |
| Invariant Checks | **100%** | **CRITICAL** |
| Idempotency Logic | **100%** | **CRITICAL** |
| Service Orchestration | **90%+** | **HIGH** |
| Gateway Adapters | **85%+** | **HIGH** |
| Controllers | **80%+** | **MEDIUM** |
| Commands | **80%+** | **MEDIUM** |
| **Overall** | **80%+** | **REQUIRED** |

### Coverage Exclusions

- **DTOs** (readonly classes, no logic)
- **Events** (data containers)
- **Config classes** (constants only)
- **Exception classes** (if simple)

---

## Test Data & Fixtures

### Factory Patterns

```php
// SubscriptionFactory example
Subscription::factory()
    ->active()
    ->for($user)
    ->for($plan)
    ->create();

// PaymentFactory example
Payment::factory()
    ->success()
    ->for($subscription, 'payable')
    ->create();
```

### Test Scenarios

**Happy Paths:**
- Successful subscription creation and activation
- Successful renewal
- Successful cancellation

**Failure Paths:**
- Payment timeout
- Payment failure
- Webhook failure
- Gateway errors

**Edge Cases:**
- Duplicate webhooks
- Country change
- Grace period expiration
- Reconciliation discrepancies

---

## Mocking Strategy

### What to Mock

✅ **MUST Mock:**
- HTTP clients (M-Pesa API calls)
- Stripe SDK (PaymentIntent, Subscription APIs)
- External services
- Time-dependent operations (use `Carbon::setTestNow()`)

❌ **DON'T Mock:**
- Database operations (use real DB in tests)
- Eloquent models
- Internal services (SubscriptionService, etc.)
- Events (use `Event::fake()` to verify, not mock)

### Mock Examples

```php
// Mock M-Pesa HTTP client
Http::fake([
    'sandbox.safaricom.co.ke/*' => Http::response(['CheckoutRequestID' => 'test-123']),
]);

// Mock Stripe SDK
$this->mock(Stripe\PaymentIntent::class, function ($mock) {
    $mock->shouldReceive('create')
        ->andReturn((object)['id' => 'pi_test', 'client_secret' => 'secret']);
});
```

---

## Test Execution Strategy

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Unit/Models/SubscriptionTest.php

# Run with filter
php artisan test --filter=test_subscription_can_transition_to_active

# Run with coverage (requires Xdebug)
php artisan test --coverage
php artisan test --coverage --min=80
```

### Test Organization

1. **Fast Tests First** (Unit tests)
   - Run frequently during development
   - Should complete in < 30 seconds

2. **Integration Tests** (Slower)
   - Run before commits
   - May take 2-5 minutes

3. **Feature Tests** (User flows)
   - Run before deployment
   - Verify end-to-end functionality

---

## Critical Test Scenarios

### Must-Have Test Cases

#### State Machine Tests
- [ ] Subscription: PENDING → ACTIVE (with payment)
- [ ] Subscription: ACTIVE → GRACE (renewal failure)
- [ ] Subscription: GRACE → ACTIVE (renewal success)
- [ ] Subscription: GRACE → EXPIRED (grace period ends)
- [ ] Subscription: ACTIVE → CANCELLED
- [ ] Subscription: Cannot ACTIVE without payment
- [ ] Subscription: Cannot EXPIRED → ACTIVE without new payment
- [ ] Payment: INITIATED → SUCCESS
- [ ] Payment: INITIATED → FAILED
- [ ] Payment: INITIATED → TIMEOUT
- [ ] Payment: Terminal states are immutable

#### Idempotency Tests
- [ ] Duplicate Stripe webhook ignored
- [ ] Duplicate M-Pesa callback ignored
- [ ] Duplicate payment initiation prevented
- [ ] Idempotency key uniqueness enforced

#### Financial Immutability Tests
- [ ] Invoice cannot be modified after creation
- [ ] Invoice snapshot is immutable
- [ ] Payment amount cannot change after confirmation
- [ ] Subscription invoice created only after PaymentConfirmed

#### Service Orchestration Tests
- [ ] Gateway selection: KE → M-Pesa
- [ ] Gateway selection: Non-KE → Stripe
- [ ] Payment initiation creates payment and emits event
- [ ] Payment confirmation activates subscription
- [ ] Renewal failure transitions to GRACE
- [ ] Renewal success transitions to ACTIVE
- [ ] Error handling and rollback

#### Webhook Tests
- [ ] Stripe webhook signature verification
- [ ] M-Pesa IP whitelist validation
- [ ] Webhook retry on failure
- [ ] Webhook idempotency

---

## Test Maintenance

### Best Practices

1. **Keep Tests Fast**
   - Use in-memory database for unit tests
   - Mock external services
   - Avoid unnecessary setup

2. **Keep Tests Independent**
   - Each test should be able to run alone
   - Don't rely on test execution order
   - Clean up after each test

3. **Use Descriptive Names**
   - `test_subscription_cannot_be_active_without_payment()`
   - `test_duplicate_webhook_is_ignored_idempotently()`

4. **Test Behavior, Not Implementation**
   - Test what the code does, not how it does it
   - Focus on business logic, not internal details

5. **Maintain Test Coverage**
   - Run coverage reports regularly
   - Fix coverage gaps before merging
   - Don't let coverage drop below 80%

---

## Implementation Order (Refined)

### Phase 1: Foundation (START HERE) - 8-10 hours

1. ✅ **Create Factories**
   - `SubscriptionFactory` with states (pending, active, grace, expired, cancelled)
   - `PaymentFactory` with states (initiated, success, failed, timeout)
   - Update `UserFactory` to include `country` field

2. ✅ **Update TestCase Base Class**
   - Add `RefreshDatabase` trait
   - Set up test database configuration

3. ✅ **State Machine Tests (100% Coverage Required)**
   - `SubscriptionTest` - All transitions and invariants
   - `PaymentTest` - All transitions and invariants

### Phase 2: Core Business Logic - 10-12 hours

4. **Idempotency Tests**
   - Webhook idempotency
   - Payment initiation idempotency

5. **Service Orchestration Tests**
   - `SubscriptionServiceTest` with mocked adapters

### Phase 3: Gateway Adapters - 6-8 hours

6. **Adapter Tests (Mocked)**
   - `MpesaGatewayAdapterTest`
   - `StripeGatewayAdapterTest`

### Phase 4: Integration Tests - 8-10 hours

7. **End-to-End Tests**
   - Payment flows (sandbox/test mode)
   - Webhook processing
   - Subscription lifecycle

### Phase 5: Feature Tests - 4-6 hours

8. **User-Facing Tests**
   - Subscription creation/cancellation
   - Error scenarios

---

## Testing Best Practices (Refined)

### Test Organization
- **Unit Tests**: Fast, isolated, mocked dependencies
- **Feature Tests**: User flows, database interactions
- **Integration Tests**: Real APIs (sandbox/test mode only)

### Test Data Strategy
- **Use Factories** for all test data
- **Factory States** for common scenarios (active, cancelled, etc.)
- **No Seeders** in tests - factories only

### Mocking Strategy
- **Mock**: HTTP clients, Stripe SDK, external APIs
- **Real**: Database, Eloquent models, internal services
- **Fake**: Events, Notifications, Queue (use `Event::fake()`, etc.)

### Test Execution
```bash
# Run all tests
php artisan test

# Run specific suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage (requires Xdebug)
php artisan test --coverage --min=80
```

---

**Status**: ✅ **READY FOR IMPLEMENTATION** - Strategy refined. Beginning Phase 1.

