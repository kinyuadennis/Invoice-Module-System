# Clarifications Needed Before Implementation

**Status**: ⏸️ **BLOCKED** - Cannot proceed to Phase 0 until these are resolved  
**Date**: 2026-01-02

This document lists critical questions that must be answered before implementation can begin. These questions arise from ambiguities in the blueprint or conflicts with existing code.

---

## 🔴 CRITICAL - Blocks Phase 0

### 1. PaymentGatewayInterface Method Signatures

**Question**: The blueprint defines method names but not detailed signatures. Please provide:

- **`initiatePayment(PaymentContext $context): GatewayResponse`**
  - What should `PaymentContext` contain? (amount, currency, user_id, subscription_id, etc.)
  - What should `GatewayResponse` contain? (transaction_id, client_secret, success flag, etc.)

- **`confirmPayment(GatewayCallbackPayload $payload): PaymentResult`**
  - What structure for `GatewayCallbackPayload`? (raw array, typed object?)
  - What should `PaymentResult` contain? (status, payment_id, gateway_reference, etc.)

- **`cancelSubscription(SubscriptionContext $context): GatewayResult`**
  - What should `SubscriptionContext` contain? (subscription_id, gateway_subscription_id, etc.)
  - What should `GatewayResult` contain? (success, error_message, etc.)

- **`supportsRecurring(): bool`** - ✅ Clear, no question

**Impact**: Cannot define interface in Phase 0 without these details.

---

### 2. Subscription Model Relationship

**Question**: Blueprint defines Subscription with `user_id`, but existing `CompanySubscription` uses `company_id`. 

**Options**:
- A) Create new `Subscription` model with `user_id` (per blueprint exactly)
- B) Extend/refactor `CompanySubscription` to match blueprint
- C) Use both (Subscription → CompanySubscription relationship)
- D) Other approach?

**Additional Context**:
- Existing `CompanySubscription` model exists with `company_id`
- Blueprint section 2.1 explicitly lists `user_id` field
- User model has `active_company_id` relationship

**Impact**: Affects Phase 0 model creation and migration strategy.

---

### 3. Payment Model Extension Strategy

**Question**: Existing `Payment` model has `invoice_id`, `company_id`. Blueprint requires `subscription_id`.

**Options**:
- A) Add `subscription_id` to existing Payment model (nullable) - supports both invoice and subscription payments
- B) Create separate `SubscriptionPayment` model
- C) Use polymorphic relationship (`payable_type`, `payable_id`)

**Additional Context**:
- Existing Payment model is used for invoice payments
- Need to support both invoice payments (existing) and subscription payments (new)
- Blueprint doesn't specify relationship strategy

**Impact**: Affects Phase 0 migration and model structure.

---

## 🟡 HIGH PRIORITY - Needed for Constants

### 4. Grace Period Duration

**Question**: What should `MPESA_RENEWAL_GRACE_DAYS` be?

**Current Proposal**: `3` days  
**Reasoning**: Standard grace period, allows time for payment retry

**Impact**: Affects Phase 3 grace period handling

---

### 5. Renewal Notification Lead Time

**Question**: How many days before renewal should users be notified?

**Current Proposal**: `5` days  
**Reasoning**: Gives users time to update payment methods

**Impact**: Affects Phase 3 notification scheduling

---

### 6. Payment Timeout Duration

**Question**: How long should we wait for payment confirmation before marking as TIMEOUT?

**Current Proposal**: `3600` seconds (1 hour)  
**Reasoning**: Allows time for user to complete payment, not too long to block retries

**Impact**: Affects Phase 1 payment timeout handling

---

### 7. Webhook Retry Configuration

**Questions**:
- **Max Retries**: How many times should we retry failed webhook processing?  
  **Current Proposal**: `5` retries
- **Backoff Base**: What should the base delay be for exponential backoff?  
  **Current Proposal**: `60` seconds (doubles each retry: 60s, 120s, 240s, 480s, 960s)

**Impact**: Affects Phase 3 failure handling

---

## 🟢 MEDIUM PRIORITY - Implementation Details

### 8. Existing PaymentGatewayService Integration

**Question**: `app/Http/Services/PaymentGatewayService.php` already exists with M-Pesa and Stripe methods. Should we:

- A) Refactor existing service to use adapter pattern (recommended per blueprint)
- B) Create new service alongside existing (might cause confusion)
- C) Replace entirely (risky, breaks existing invoice payment flow)

**Additional Context**:
- Existing service handles invoice payments
- New system handles subscription payments
- Blueprint requires adapter pattern with interface

**Recommendation**: Option A - Refactor to adapter pattern, but keep existing invoice payment functionality working during transition.

**Impact**: Affects integration approach and risk level

---

### 9. Plan Code vs Plan ID

**Question**: Blueprint Subscription model uses `plan_code` (string), but existing `SubscriptionPlan` uses `id` (integer).

**Options**:
- A) Use `plan_code` as string identifier (per blueprint)
- B) Use `subscription_plan_id` foreign key (like CompanySubscription)
- C) Use both (code for display, id for relationship)

**Impact**: Affects Phase 0 model structure

---

## 📋 Summary

**Critical Blockers (3)**: 
- PaymentGatewayInterface signatures
- Subscription model relationship
- Payment model extension strategy

**High Priority (4)**:
- Grace period duration
- Renewal notification timing
- Payment timeout duration
- Webhook retry configuration

**Medium Priority (2)**:
- Existing PaymentGatewayService integration
- Plan code vs ID strategy

---

## Next Steps

1. **Product Owner Review**: Please review this document and provide answers
2. **Update Implementation Plan**: Update `IMPLEMENTATION_PLAN.md` with answers
3. **Update Constants**: Add confirmed values to constants files
4. **Begin Phase 0**: Proceed with implementation after all critical blockers resolved

---

**Note**: Per the implementation guide's "Mandatory Stop Conditions", proceeding without these clarifications would be considered a failure, not progress. All questions marked with 🔴 must be answered before Phase 0 can begin.

