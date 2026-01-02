# Payment & Subscription Module - Detailed Implementation Plan

**Version**: 1.0  
**Date**: 2026-01-02  
**Status**: Ready for Review  
**Based on**: InvoiceHub Payment & Subscription Module Blueprint v1

---

## Overview

This plan implements a subscription-based payment system to replace platform fees, with support for M-Pesa (manual) and Stripe (automated) gateways. The implementation follows strict architectural separation, financial immutability principles, and gateway-agnostic domain logic.

### Scope
- Replace platform fee model with subscription model
- Implement payment gateway adapters (M-Pesa, Stripe)
- Build subscription lifecycle management
- Integrate with existing invoice system via snapshots
- Ensure auditability and financial immutability

### Estimated Timeline
- **Phase 0**: 8-12 hours
- **Phase 1**: 20-30 hours
- **Phase 2**: 15-25 hours
- **Phase 3**: 25-35 hours
- **Total**: 68-102 hours (approximately 2-3 weeks)

### Dependencies
- Laravel Cashier (Stripe integration) - **REQUIRED**
- M-Pesa Daraja API credentials - **REQUIRED**
- Email/SMS notification system - **REQUIRED**
- Stripe/Daraja sandbox accounts for testing - **REQUIRED**

### Success Metrics
- ✅ 100% test coverage on state machines
- ✅ All gateway callbacks are idempotent
- ✅ Zero financial data mutations after invoice creation
- ✅ All configurable values are explicit constants
- ✅ Acceptance checklist (Pre-Merge Gate) passes

---

## Required Constants & Configuration Values

**✅ CONFIRMED - All values explicitly defined per product owner confirmation**

### Payment Constants
```php
// app/Config/PaymentConstants.php (to be created)
class PaymentConstants
{
    // Payment timeout (seconds) - Confirmed: 5 minutes per blueprint section 4
    public const PAYMENT_TIMEOUT_SECONDS = 300; // 5 minutes
    
    // Payment states
    public const PAYMENT_STATUS_INITIATED = 'INITIATED';
    public const PAYMENT_STATUS_SUCCESS = 'SUCCESS';
    public const PAYMENT_STATUS_FAILED = 'FAILED';
    public const PAYMENT_STATUS_TIMEOUT = 'TIMEOUT';
    
    // Gateway identifiers
    public const GATEWAY_MPESA = 'mpesa';
    public const GATEWAY_STRIPE = 'stripe';
}
```

### Subscription Constants
```php
// app/Config/SubscriptionConstants.php (to be created)
class SubscriptionConstants
{
    // Subscription states
    public const SUBSCRIPTION_STATUS_PENDING = 'PENDING';
    public const SUBSCRIPTION_STATUS_ACTIVE = 'ACTIVE';
    public const SUBSCRIPTION_STATUS_GRACE = 'GRACE';
    public const SUBSCRIPTION_STATUS_EXPIRED = 'EXPIRED';
    public const SUBSCRIPTION_STATUS_CANCELLED = 'CANCELLED';
    
    // Grace period (days) - Confirmed: 7 days per blueprint section 2/5
    public const RENEWAL_GRACE_DAYS = 7;
    
    // Renewal notification lead time (days) - Confirmed: 3 days per blueprint section 5
    public const RENEWAL_NOTIFICATION_LEAD_DAYS = 3;
    
    // Gateway identifiers
    public const GATEWAY_MPESA = 'mpesa';
    public const GATEWAY_STRIPE = 'stripe';
}
```

### Gateway Constants
```php
// app/Config/GatewayConstants.php (to be created)
class GatewayConstants
{
    // Retry configuration - Confirmed: 3 retries per blueprint section 5
    public const WEBHOOK_MAX_RETRIES = 3;
    public const RETRY_BACKOFF_BASE_SECONDS = 60; // Doubles: 60s, 120s, 240s
    
    // Webhook timeout (seconds)
    public const WEBHOOK_TIMEOUT_SECONDS = 30;
    
    // Country codes
    public const COUNTRY_KENYA = 'KE';
}
```

**✅ All values confirmed by product owner on 2026-01-02**

---

## ✅ Clarifications - RESOLVED

All questions have been answered by product owner on 2026-01-02. See `CLARIFICATIONS_NEEDED.md` for full details.

### 1. Subscription Model Relationship ✅
**Answer**: **Option B** - Extend/refactor CompanySubscription to match blueprint
- Add `user_id` (nullable foreign key) alongside `company_id`
- Update relationships: BelongsTo both User and Company
- Rename model to `Subscription` if needed for consistency

### 2. Payment Model Extension ✅
**Answer**: **Option C** - Use polymorphic relationship
- Add `payable_type` (string) and `payable_id` (bigInteger) to Payment model
- Make `invoice_id` nullable/removed if redundant
- Supports both invoice and subscription payments with future extensibility

### 3. PaymentGatewayInterface Method Signatures ✅
**Answer**: Use typed DTOs/Value Objects for type safety

**`initiatePayment(PaymentContext $context): GatewayResponse`**
- `PaymentContext`: subscriptionId, amount, currency, userDetails (phone/customerId/country), reference, description
- `GatewayResponse`: transactionId, clientSecret (nullable), success, metadata

**`confirmPayment(GatewayCallbackPayload $payload): PaymentResult`**
- `GatewayCallbackPayload`: rawData (array), gatewayReference, signature (nullable)
- `PaymentResult`: status, paymentId, gatewayReference, metadata

**`cancelSubscription(SubscriptionContext $context): GatewayResult`**
- `SubscriptionContext`: subscriptionId, gatewaySubscriptionId (nullable), reason
- `GatewayResult`: success, errorMessage (nullable), metadata

### 4-7. Constants ✅
- Grace Period: **7 days** (confirmed per blueprint)
- Renewal Notification: **3 days** before due (confirmed per blueprint section 5)
- Payment Timeout: **300 seconds (5 minutes)** (confirmed per blueprint section 4)
- Webhook Retries: **3 retries** with 60s base backoff (confirmed per blueprint section 5)

### 8. Existing PaymentGatewayService ✅
**Answer**: **Option A** - Refactor existing service to use adapter pattern
- Wrap current methods into adapters
- Inject via factory in service
- Maintain invoice payment flows during refactor

### 9. Plan Code vs Plan ID ✅
**Answer**: **Option C** - Use both
- Add `plan_code` (string, e.g., 'pro') to Subscription
- Keep `subscription_plan_id` foreign key to SubscriptionPlan
- Supports display/audits without breaking lookups

---

## Phase 0: Foundations

**Objectives**: Establish core structures without business logic  
**Estimated Effort**: 8-12 hours  
**Dependencies**: Laravel Cashier installed, API keys in .env

### Sub-Tasks

#### 0.1 User Model Extension
- [ ] Create migration: `add_country_to_users_table.php`
- [ ] Add `country` field (string, nullable initially, required later)
- [ ] Update User model `$fillable`
- [ ] Add validation for country code format
- [ ] Create test: User model can have country
- **Effort**: 1-2 hours
- **Risk**: Low - straightforward migration

#### 0.2 Subscription Model Refactoring
- [ ] Create migration: `refactor_company_subscriptions_to_subscriptions.php`
  - Rename table: `company_subscriptions` → `subscriptions` (or keep name, update model)
  - Add `user_id` (nullable, foreign key to users)
  - Add `plan_code` (string, e.g., 'pro', 'basic')
  - Update `status` enum to match blueprint: PENDING, ACTIVE, GRACE, EXPIRED, CANCELLED
  - Update `gateway` field if needed (enum: mpesa, stripe)
  - Add `next_billing_at` (datetime, nullable)
  - Ensure `company_id`, `subscription_plan_id`, `starts_at`, `ends_at`, `auto_renew` exist
  - Add indexes: `user_id`, `company_id`, `status`, `gateway`, `next_billing_at`
- [ ] Refactor CompanySubscription model → Subscription model
  - Update namespace and class name if renaming
  - Implement `$fillable` with all blueprint fields
  - Update `casts()` for dates and boolean
  - Add relationships: `user()`, `company()`, `plan()`, `payments()` (polymorphic)
  - Add state check methods: `isPending()`, `isActive()`, `isInGrace()`, `isExpired()`, `isCancelled()`
  - Add invariant checks (blueprint section 2.1)
- [ ] Update SubscriptionFactory
- [ ] Create tests:
  - Model creation and relationships (user, company, plan, payments)
  - State check methods
  - Invariant enforcement
- **Effort**: 3-4 hours
- **Risk**: Medium - Refactoring existing model
- **Mitigation**: Test thoroughly, ensure backward compatibility with existing data

#### 0.3 Payment Model Extension (Polymorphic)
- [ ] Review existing Payment model structure
- [ ] Create migration: `add_polymorphic_fields_to_payments_table.php`
  - Add `payable_type` (string, e.g., 'App\Models\Invoice', 'App\Models\Subscription')
  - Add `payable_id` (bigInteger, unsigned)
  - Add `idempotency_key` (string, unique index)
  - Add `raw_gateway_payload` (json, nullable)
  - Update `status` enum to match blueprint: INITIATED, SUCCESS, FAILED, TIMEOUT
  - Update `gateway` enum if needed (mpesa, stripe)
  - Make `invoice_id` nullable (will use polymorphic relationship)
  - Add composite index on `payable_type`, `payable_id`
- [ ] Update Payment model
  - Add `payable_type`, `payable_id`, `idempotency_key`, `raw_gateway_payload` to `$fillable`
  - Add casts for new fields
  - Add `payable()` polymorphic relationship (morphTo)
  - Update `invoice()` relationship to use polymorphic (or keep as convenience method)
  - Add `subscription()` relationship via polymorphic
  - Add state check methods per blueprint
  - Add invariant checks (blueprint section 2.2)
- [ ] Update PaymentFactory to support polymorphic relationships
- [ ] Create data migration script (optional) to convert existing invoice_id to polymorphic
- [ ] Create tests:
  - Polymorphic relationship (invoice and subscription)
  - Idempotency key uniqueness
  - State transitions (initial)
  - Backward compatibility with invoice_id
- **Effort**: 3-4 hours
- **Risk**: Medium - Polymorphic relationships, data migration
- **Mitigation**: Test thoroughly, optional data migration script, maintain backward compatibility

#### 0.4 PaymentGatewayInterface Definition and DTOs
- [ ] Create interface: `app/Payments/Contracts/PaymentGatewayInterface.php`
  - Define method signatures with typed DTOs:
    - `initiatePayment(PaymentContext $context): GatewayResponse`
    - `confirmPayment(GatewayCallbackPayload $payload): PaymentResult`
    - `cancelSubscription(SubscriptionContext $context): GatewayResult`
    - `supportsRecurring(): bool`
  - Add PHPDoc with contract requirements
  - Document gateway-agnostic contract rules
- [ ] Create DTO/Value Object classes in `app/Payments/DTOs/`:
  - `PaymentContext` (readonly class):
    - `subscriptionId` (string/UUID)
    - `amount` (float)
    - `currency` (string, ISO code)
    - `userDetails` (array: phone/customerId/country)
    - `reference` (string, unique)
    - `description` (string)
  - `GatewayResponse` (readonly class):
    - `transactionId` (string)
    - `clientSecret` (string|null)
    - `success` (bool)
    - `metadata` (array)
  - `GatewayCallbackPayload` (readonly class):
    - `rawData` (array)
    - `gatewayReference` (string)
    - `signature` (string|null)
  - `PaymentResult` (readonly class):
    - `status` (string: 'confirmed'|'failed'|'timeout')
    - `paymentId` (string, internal DB ID)
    - `gatewayReference` (string)
    - `metadata` (array)
  - `SubscriptionContext` (readonly class):
    - `subscriptionId` (string, internal UUID)
    - `gatewaySubscriptionId` (string|null)
    - `reason` (string, for audit)
  - `GatewayResult` (readonly class):
    - `success` (bool)
    - `errorMessage` (string|null)
    - `metadata` (array)
- [ ] Create tests:
  - Interface can be implemented
  - Method signatures are correct
  - DTOs are immutable and type-safe
- **Effort**: 3-4 hours
- **Risk**: Low - Signatures confirmed by product owner
- **Mitigation**: Use readonly classes for immutability, comprehensive type hints

#### 0.5 Gateway Adapter Skeletons
- [ ] Create `app/Payments/Adapters/MpesaGatewayAdapter.php`
  - Implement `PaymentGatewayInterface`
  - Empty methods with `throw new \Exception('Not implemented')`
  - Add PHPDoc comments referencing blueprint section 5.1
- [ ] Create `app/Payments/Adapters/StripeGatewayAdapter.php`
  - Implement `PaymentGatewayInterface`
  - Empty methods with `throw new \Exception('Not implemented')`
  - Add PHPDoc comments referencing blueprint section 5.2
- [ ] Create tests:
  - Adapters implement interface correctly
  - Methods throw exceptions (temporary)
- **Effort**: 1-2 hours
- **Risk**: Low - Skeleton code only

#### 0.6 Domain Events
- [ ] Create `app/Payments/Events/PaymentInitiated.php`
- [ ] Create `app/Payments/Events/PaymentConfirmed.php`
- [ ] Create `app/Payments/Events/PaymentFailed.php`
- [ ] Create `app/Payments/Events/PaymentCancelled.php`
- [ ] Create `app/Subscriptions/Events/SubscriptionCreated.php`
- [ ] Create `app/Subscriptions/Events/SubscriptionActivated.php`
- [ ] Create `app/Subscriptions/Events/SubscriptionRenewed.php`
- [ ] Create `app/Subscriptions/Events/SubscriptionCancelled.php`
- [ ] Create `app/Subscriptions/Events/SubscriptionExpired.php`
- [ ] Each event should contain relevant data (payment/subscription IDs, amounts, etc.)
- [ ] Create tests:
  - Events can be instantiated
  - Events contain required data
- **Effort**: 2-3 hours
- **Risk**: Low - Standard Laravel event pattern

### Phase 0 Deliverables
- ✅ User model has country field
- ✅ Subscription model created with all blueprint fields
- ✅ Payment model extended with subscription fields
- ✅ PaymentGatewayInterface defined
- ✅ Adapter skeletons created
- ✅ Domain events created
- ✅ All tests passing

### Phase 0 Definition of Done
- `php artisan migrate` succeeds
- Interface methods type-hint correctly
- All initial tests pass
- Code formatted with Pint
- No linter errors

---

## Phase 1: One-Time Payments

**Objectives**: Enable initial subscription payments via gateways  
**Estimated Effort**: 20-30 hours  
**Dependencies**: Phase 0 complete, sandbox accounts for M-Pesa/Stripe

### Sub-Tasks

#### 1.1 M-Pesa Gateway Adapter Implementation
- [ ] Install/configure M-Pesa Daraja SDK (if not already)
- [ ] Implement `initiatePayment()`:
  - STK Push request to Daraja API
  - Handle request/response
  - Store gateway reference in Payment model
  - Return GatewayResponse with transaction reference
- [ ] Implement `confirmPayment()`:
  - Parse Daraja callback payload
  - Validate callback signature/IP
  - Extract transaction status
  - Return PaymentResult with status
  - Handle idempotency (check by gateway_reference)
- [ ] Implement `cancelSubscription()`:
  - Return error/not supported (per blueprint 5.1 - M-Pesa has no native cancel)
- [ ] Implement `supportsRecurring()`: Return `false`
- [ ] Add error handling:
  - Network failures
  - Invalid responses
  - Timeout handling
- [ ] Create tests:
  - STK Push initiation
  - Callback parsing and validation
  - Idempotency handling
  - Error scenarios
- [ ] Create integration tests (with sandbox):
  - End-to-end STK Push flow
  - Callback processing
- **Effort**: 10-15 hours
- **Risk**: High - M-Pesa API complexity, callback handling
- **Mitigation**: Use sandbox thoroughly, implement robust error handling

#### 1.2 Stripe Gateway Adapter Implementation
- [ ] Verify Laravel Cashier installation
- [ ] Implement `initiatePayment()`:
  - Create Stripe PaymentIntent via Cashier
  - Store intent ID in Payment model
  - Return GatewayResponse with client_secret
- [ ] Implement `confirmPayment()`:
  - Parse Stripe webhook payload
  - Verify webhook signature (per blueprint section 7)
  - Extract payment status from event
  - Return PaymentResult with status
  - Handle idempotency (check by gateway_reference)
- [ ] Implement `cancelSubscription()`:
  - Cancel Stripe subscription via Cashier
  - Return GatewayResult
- [ ] Implement `supportsRecurring()`: Return `true`
- [ ] Add error handling:
  - Stripe API errors
  - Webhook verification failures
  - Timeout handling
- [ ] Create tests:
  - PaymentIntent creation
  - Webhook parsing and validation
  - Idempotency handling
  - Error scenarios
- [ ] Create integration tests (with Stripe test mode):
  - End-to-end payment flow
  - Webhook processing
- **Effort**: 8-12 hours
- **Risk**: Medium - Stripe integration is well-documented
- **Mitigation**: Use Stripe test mode, follow Cashier documentation

#### 1.3 SubscriptionService Orchestration
- [ ] Create `app/Http/Services/SubscriptionService.php` (or `app/Services/SubscriptionService.php`)
- [ ] Implement gateway selection logic:
  - Check user country (KE → M-Pesa, else → Stripe)
  - Instantiate appropriate adapter
- [ ] Implement `initiateSubscription()` use case:
  - Create Subscription (PENDING status)
  - Create Payment (INITIATED status)
  - Call gateway adapter `initiatePayment()`
  - Store gateway reference
  - Emit PaymentInitiated event
  - Handle errors (rollback if needed)
- [ ] Implement payment confirmation handler:
  - Receive PaymentResult from gateway
  - Update Payment status
  - Handle idempotency (check existing payments)
  - Emit PaymentConfirmed or PaymentFailed event
- [ ] Add gateway adapter factory/resolver
- [ ] Create tests:
  - Gateway selection logic
  - Subscription initiation flow
  - Payment confirmation flow
  - Error handling and rollback
- **Effort**: 4-6 hours
- **Risk**: Medium - Orchestration complexity
- **Mitigation**: Keep logic simple, test thoroughly

#### 1.4 Routes and Controllers
- [ ] Create `app/Http/Controllers/User/SubscriptionController.php`
  - `store()` - Initiate subscription
  - Protected route, requires auth
- [ ] Create webhook/callback routes (public, CSRF exempt):
  - M-Pesa callback route
  - Stripe webhook route
- [ ] Create `app/Http/Controllers/Webhook/MpesaWebhookController.php`
  - Validate callback
  - Call SubscriptionService confirmation handler
  - Return appropriate HTTP response
- [ ] Create/update `app/Http/Controllers/Webhook/StripeWebhookController.php`
  - Verify webhook signature
  - Parse event type
  - Call SubscriptionService confirmation handler
  - Return 200 OK
- [ ] Add routes to `routes/web.php`:
  - `POST /subscriptions` (protected)
  - `POST /webhooks/mpesa` (public)
  - `POST /webhooks/stripe` (public)
- [ ] Create tests:
  - Controller methods
  - Route accessibility
  - Webhook validation
- **Effort**: 3-4 hours
- **Risk**: Low - Standard Laravel patterns
- **Mitigation**: Follow existing webhook controller patterns

#### 1.5 Idempotency Implementation
- [ ] Add unique index on `payments.idempotency_key` (if not in Phase 0)
- [ ] Implement idempotency check in SubscriptionService:
  - Generate idempotency key for each payment
  - Check for existing payment with same key
  - Return existing payment if found
- [ ] Implement idempotency in webhook handlers:
  - Check for existing payment with gateway_reference
  - Ignore duplicate callbacks
- [ ] Create tests:
  - Duplicate payment initiation
  - Duplicate webhook callbacks
  - Idempotency key generation
- **Effort**: 2-3 hours
- **Risk**: Low - Standard idempotency pattern
- **Mitigation**: Use database constraints

### Phase 1 Deliverables
- ✅ M-Pesa STK Push initiation works
- ✅ Stripe PaymentIntent creation works
- ✅ M-Pesa callbacks processed correctly
- ✅ Stripe webhooks processed correctly
- ✅ Payments transition to SUCCESS/FAILED states
- ✅ Idempotency enforced

### Phase 1 Definition of Done
- Manual test: Kenyan user gets STK prompt, confirms → Payment SUCCESS
- Manual test: International user pays via card → Payment SUCCESS
- All integration tests pass
- Webhook tests pass (mocked and real)
- Code formatted with Pint
- No linter errors

---

## Phase 2: Subscriptions

**Objectives**: Add full subscription lifecycle management  
**Estimated Effort**: 15-25 hours  
**Dependencies**: Phase 1 complete, email/SMS setup

### Sub-Tasks

#### 2.1 State Machine Implementation
- [ ] Create Subscription state enum or use string constants
- [ ] Implement state transition methods in Subscription model:
  - `transitionToActive()` - Enforce invariants (payment required)
  - `transitionToGrace()` - Set grace period end date
  - `transitionToExpired()` - Enforce no backward transitions
  - `transitionToCancelled()` - Handle cancellation
- [ ] Implement Payment state transition methods:
  - `markAsSuccess()` - Enforce terminal state
  - `markAsFailed()` - Enforce terminal state
  - `markAsTimeout()` - Enforce terminal state
- [ ] Add invariant checks (blueprint sections 2.1, 2.2):
  - Subscription cannot be ACTIVE without payment
  - Payment cannot transition twice to terminal state
  - Gateway field immutable after creation
- [ ] Create tests:
  - Valid state transitions
  - Invalid state transitions (should fail)
  - Invariant enforcement
  - Terminal state immutability
- **Effort**: 4-6 hours
- **Risk**: Medium - State machine complexity
- **Mitigation**: Write failing tests first, implement to pass

#### 2.2 SubscriptionRepository
- [ ] Create `app/Subscriptions/Repositories/SubscriptionRepository.php`
- [ ] Implement audited CRUD operations:
  - `create()` - Log creation
  - `update()` - Log changes (audit trail)
  - `find()` - Standard find
  - `findActiveForUser()` - Find active subscription
- [ ] Integrate with AuditLog model (if exists) or create audit entries
- [ ] Create tests:
  - Repository methods
  - Audit log creation
- **Effort**: 2-3 hours
- **Risk**: Low - Standard repository pattern
- **Mitigation**: Follow existing repository patterns if any

#### 2.3 SubscriptionService Extensions
- [ ] Extend SubscriptionService:
  - `activateSubscription()` - Handle PaymentConfirmed event
  - `cancelSubscription()` - Handle user cancellation
  - `handleRenewalFailure()` - Transition to GRACE
  - `handleRenewalSuccess()` - Transition back to ACTIVE
- [ ] Implement gateway-specific cancellation:
  - M-Pesa: Mark as cancelled (no gateway call)
  - Stripe: Call adapter `cancelSubscription()`
- [ ] Integrate with state machine methods
- [ ] Emit domain events (SubscriptionActivated, SubscriptionCancelled, etc.)
- [ ] Create tests:
  - Subscription activation flow
  - Cancellation flow (both gateways)
  - Renewal handling
  - Event emission
- **Effort**: 4-6 hours
- **Risk**: Medium - Integration complexity
- **Mitigation**: Test each method independently

#### 2.4 Invoice Snapshot Integration
- [ ] Create event listener for `PaymentConfirmed` event
- [ ] In listener, create invoice snapshot:
  - Use existing `InvoiceSnapshotService`
  - Snapshot data: amount, currency, plan description, gateway (metadata)
  - Create Invoice record (immutable)
- [ ] Ensure invoice creation is idempotent
- [ ] Create tests:
  - Invoice created on PaymentConfirmed
  - Snapshot contains correct data
  - Idempotency (duplicate events)
- **Effort**: 3-4 hours
- **Risk**: Low - Use existing snapshot service
- **Mitigation**: Review existing InvoiceSnapshotService first

#### 2.5 Frontend Dashboard UI
- [ ] Create Blade view: `resources/views/user/subscriptions/index.blade.php`
  - List user's subscriptions
  - Show status, plan, dates
  - Show next billing date
- [ ] Create Blade view: `resources/views/user/subscriptions/show.blade.php`
  - Subscription details
  - Payment history
  - Cancel button (if active)
- [ ] Create subscription selection/purchase page:
  - List available plans
  - Auto-detect gateway (based on user country)
  - Initiate subscription
- [ ] Add Alpine.js for dynamic interactions:
  - Gateway selection display
  - Form submission
- [ ] Update navigation/routes
- [ ] Create tests:
  - Views render correctly
  - Forms submit correctly
- **Effort**: 4-6 hours
- **Risk**: Low - Standard Laravel/Blade patterns
- **Mitigation**: Follow existing view patterns

### Phase 2 Deliverables
- ✅ Users can subscribe
- ✅ Subscriptions activate after payment
- ✅ Users can cancel subscriptions
- ✅ Invoices generated on payment confirmation
- ✅ Dashboard shows subscription status

### Phase 2 Definition of Done
- User can subscribe, see status, cancel
- Invoice generated immutably on payment
- All state transitions work correctly
- All tests passing
- UI is functional and tested

---

## Phase 3: Renewals & Edge Cases

**Objectives**: Complete recurring billing and handle failures  
**Estimated Effort**: 25-35 hours  
**Dependencies**: Phase 2 complete

### Sub-Tasks

#### 3.1 M-Pesa Renewal Scheduler
- [ ] Create `app/Console/Commands/RenewSubscriptionsCommand.php`
- [ ] Implement logic:
  - Find subscriptions due for renewal (`next_billing_at <= now`)
  - Filter by gateway = M-Pesa, status = ACTIVE
  - For each subscription:
    - Create Payment (INITIATED)
    - Send STK Push via adapter
    - Update next_billing_at
    - Handle errors (transition to GRACE if fails)
- [ ] Schedule command in `app/Console/Kernel.php`:
  - Run daily (or as configured)
  - Time: Configured hour (e.g., 2 AM)
- [ ] Add notification before renewal:
  - Send email/SMS X days before (RENEWAL_NOTIFICATION_LEAD_DAYS)
  - Create notification job/command
- [ ] Create tests:
  - Command finds due subscriptions
  - STK Push sent correctly
  - Grace period handling
  - Error handling
- [ ] Create integration tests:
  - End-to-end renewal flow
- **Effort**: 8-12 hours
- **Risk**: High - Scheduler complexity, time-based logic
- **Mitigation**: Use Laravel scheduler, test with time mocking

#### 3.2 Stripe Webhook Listener
- [ ] Extend StripeWebhookController:
  - Handle `invoice.paid` event → Renewal success
  - Handle `invoice.payment_failed` event → Renewal failure
  - Handle `customer.subscription.deleted` event → Cancellation
- [ ] Implement renewal success handler:
  - Find subscription by Stripe subscription ID
  - Create Payment record
  - Update subscription (next_billing_at, status)
  - Emit SubscriptionRenewed event
- [ ] Implement renewal failure handler:
  - Transition subscription to GRACE
  - Emit notification
  - Set grace period end date
- [ ] Handle webhook idempotency:
  - Check for existing payment by gateway_reference
  - Ignore duplicate events
- [ ] Create tests:
  - Webhook event parsing
  - Renewal success handling
  - Renewal failure handling
  - Idempotency
- [ ] Create integration tests (Stripe test mode):
  - End-to-end renewal webhook flow
- **Effort**: 6-8 hours
- **Risk**: Medium - Webhook complexity
- **Mitigation**: Use Stripe CLI for local testing

#### 3.3 Grace Period Handling
- [ ] Create scheduled command: `ProcessGracePeriodExpirationsCommand`
- [ ] Implement logic:
  - Find subscriptions in GRACE status
  - Check if grace_period_end < now
  - Transition to EXPIRED
  - Emit SubscriptionExpired event
- [ ] Schedule command (run daily)
- [ ] Add grace period notifications:
  - Send reminder during grace period
  - Send expiration notice
- [ ] Create tests:
  - Grace period expiration
  - Notification sending
- **Effort**: 3-4 hours
- **Risk**: Low - Time-based logic
- **Mitigation**: Use time mocking in tests

#### 3.4 Failure Playbook Implementation
- [ ] Implement callback timeout handling:
  - Scheduled job to check INITIATED payments
  - If created_at + PAYMENT_TIMEOUT_SECONDS < now → mark as TIMEOUT
  - Transition subscription appropriately
- [ ] Implement retry logic (with backoff):
  - Queue failed webhook processing
  - Retry with exponential backoff (RETRY_BACKOFF_BASE_SECONDS)
  - Max retries: WEBHOOK_MAX_RETRIES
- [ ] Implement country change handling:
  - Detect user country change
  - Cancel existing subscription
  - Create new subscription with new gateway
- [ ] Implement reconciliation job:
  - For Stripe: Query Stripe API for subscription status
  - Compare with InvoiceHub database
  - Log discrepancies
  - Manual review required
- [ ] Create tests:
  - Timeout handling
  - Retry logic
  - Country change handling
  - Reconciliation (mocked)
- **Effort**: 6-8 hours
- **Risk**: High - Edge case complexity
- **Mitigation**: Implement one scenario at a time, test thoroughly

#### 3.5 Notifications
- [ ] Create notification classes:
  - `SubscriptionRenewalDueNotification` (email/SMS)
  - `SubscriptionRenewalFailedNotification`
  - `SubscriptionGracePeriodNotification`
  - `SubscriptionExpiredNotification`
- [ ] Integrate with existing notification system
- [ ] Create notification templates (Blade)
- [ ] Schedule notification jobs
- [ ] Create tests:
  - Notifications sent correctly
  - Templates render correctly
- **Effort**: 2-3 hours
- **Risk**: Low - Standard Laravel notifications
- **Mitigation**: Follow existing notification patterns

#### 3.6 Security Enhancements
- [ ] Implement webhook signature verification:
  - Stripe: Use Stripe webhook signature verification
  - M-Pesa: IP whitelist + payload verification (per blueprint)
- [ ] Add rate limiting to webhook endpoints
- [ ] Add request logging (audit trail)
- [ ] Ensure no sensitive data in logs
- [ ] Create tests:
  - Signature verification
  - Invalid signature rejection
  - Rate limiting
- **Effort**: 2-3 hours
- **Risk**: Medium - Security critical
- **Mitigation**: Follow blueprint section 7 exactly

### Phase 3 Deliverables
- ✅ M-Pesa renewals processed automatically
- ✅ Stripe renewals handled via webhooks
- ✅ Grace periods enforced
- ✅ Failure scenarios handled
- ✅ Notifications sent
- ✅ Security validations in place

### Phase 3 Definition of Done
- Simulated time-based tests: Renewal succeeds/fails per gateway
- All failure playbook scenarios pass
- No data corruption in any scenario
- All tests passing (80%+ coverage)
- Security validations working
- Code formatted with Pint
- No linter errors

---

## Testing Strategy

### Unit Tests
- Models: State machines, invariants, relationships
- Services: Business logic, error handling
- Adapters: Gateway-specific logic (mocked)
- Repositories: Data access, audit logging

### Integration Tests
- End-to-end payment flows (sandbox/test mode)
- Webhook processing (real webhooks in test mode)
- State transitions
- Invoice creation

### Feature Tests
- User subscription flow
- Cancellation flow
- Dashboard display
- Error scenarios

### Coverage Requirements
- Minimum 80% code coverage
- 100% coverage on state machines
- 100% coverage on invariant checks
- All critical paths tested

---

## Deployment Checklist

### Pre-Deployment
- [ ] All tests passing
- [ ] Code review completed
- [ ] Acceptance checklist passed
- [ ] Environment variables configured
- [ ] API keys configured (production)
- [ ] Database migrations tested
- [ ] Backup strategy in place

### Deployment
- [ ] Run database migrations
- [ ] Clear cache (`php artisan cache:clear`)
- [ ] Clear config cache (`php artisan config:clear`)
- [ ] Restart queue workers
- [ ] Verify scheduler is running
- [ ] Test webhook endpoints (Stripe webhook tester)
- [ ] Monitor logs for errors

### Post-Deployment
- [ ] Verify subscriptions can be created
- [ ] Verify payments process correctly
- [ ] Verify webhooks receive callbacks
- [ ] Monitor error logs
- [ ] Verify scheduled jobs run

---

## Acceptance Checklist (Pre-Merge Gate)

Before the module can be approved or merged, all of the following must be true:

- [ ] Subscription and Payment state machines are fully implemented and tested
- [ ] Duplicate callbacks and webhooks are harmless and idempotent
- [ ] Invoices are immutable once created and marked paid
- [ ] No controller contains gateway-specific business logic
- [ ] All gateway adapters implement the defined interface exactly
- [ ] All configurable values are defined explicitly (no magic numbers)
- [ ] Logs and records are sufficient for audits, disputes, and reconciliation
- [ ] All tests passing (80%+ coverage)
- [ ] Code formatted with Pint
- [ ] No linter errors
- [ ] Documentation updated

---

## Risk Register

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| M-Pesa API complexity | High | Medium | Use sandbox thoroughly, robust error handling |
| State machine bugs | High | Medium | Write failing tests first, comprehensive testing |
| Webhook delays/missing | High | Low | Reconciliation job, timeout handling |
| Gateway downtime | High | Low | Retry logic, graceful degradation |
| Data migration issues | Medium | Low | Test migrations on staging, backup strategy |
| Performance issues | Medium | Low | Monitor, optimize queries, use queues |

---

## Notes

1. **Financial Immutability**: Historical data (including platform fees) should remain untouched. New system operates alongside until fully validated.

2. **Parallel Development**: Platform fee removal can proceed in parallel with subscription implementation, but subscription must be tested and validated before platform fee removal in production.

3. **Migration Strategy**: Consider running both systems in parallel initially, then switching over once subscription system is validated.

4. **Blueprint Adherence**: This plan follows the blueprint exactly. Any deviations must be approved by product owner.

---

## Revision History

- **v1.0** (2026-01-02): Initial plan based on blueprint v1

---

**Status**: ✅ **READY FOR IMPLEMENTATION** - All clarifications resolved on 2026-01-02. Proceed to Phase 0.

