# Subscription Module Structure

This document outlines the directory structure and organization for the subscription and payment module that will replace the platform fee system.

**Status**: Preliminary structure - awaiting blueprint for full implementation plan.

## Directory Organization

### Payments Module (`app/Payments/`)

Gateway adapters and payment processing logic.

```
app/Payments/
├── Contracts/
│   └── PaymentGatewayInterface.php    # Gateway contract (awaiting blueprint section 3)
├── Adapters/
│   ├── MpesaGatewayAdapter.php        # M-Pesa implementation (gateway-specific)
│   └── StripeGatewayAdapter.php       # Stripe implementation (gateway-specific)
├── Events/
│   ├── PaymentInitiated.php           # Payment initiation event
│   ├── PaymentConfirmed.php           # Payment success event
│   ├── PaymentFailed.php              # Payment failure event
│   └── PaymentCancelled.php           # Payment cancellation event
└── README.md
```

### Subscriptions Module (`app/Subscriptions/`)

Subscription domain logic and data access.

```
app/Subscriptions/
├── Repositories/
│   └── SubscriptionRepository.php     # Audited CRUD for subscriptions
├── Events/
│   ├── SubscriptionCreated.php        # Subscription creation event
│   ├── SubscriptionActivated.php      # Subscription activated event
│   ├── SubscriptionRenewed.php        # Subscription renewal event
│   ├── SubscriptionCancelled.php      # Subscription cancellation event
│   └── SubscriptionExpired.php        # Subscription expiration event
└── README.md
```

### Models (`app/Models/`)

Eloquent models for subscriptions and payments.

```
app/Models/
├── Subscription.php                    # New: Subscription model (awaiting blueprint)
├── Payment.php                         # Existing: Review and extend as needed
└── CompanySubscription.php             # Existing: Review integration
```

### Services (`app/Http/Services/` or `app/Services/`)

Orchestration and business logic services.

```
app/Http/Services/  (or app/Services/)
└── SubscriptionService.php            # Orchestration layer (awaiting blueprint)
```

### Controllers (`app/Http/Controllers/`)

HTTP controllers for subscription management.

```
app/Http/Controllers/
├── User/
│   └── SubscriptionController.php     # User-facing subscription management
└── Webhook/
    ├── MpesaWebhookController.php     # M-Pesa callback handler
    └── StripeWebhookController.php    # Stripe webhook handler
```

### Console Commands (`app/Console/Commands/`)

Scheduled tasks and commands.

```
app/Console/Commands/
└── RenewSubscriptionsCommand.php      # M-Pesa renewal processing (scheduled)
```

### Migrations (`database/migrations/`)

Database schema changes.

```
database/migrations/
├── YYYY_MM_DD_create_subscriptions_table.php
├── YYYY_MM_DD_create_payments_table.php (if extending existing)
└── (Platform fee removal migrations - see PLATFORM_FEE_REMOVAL_CHECKLIST.md)
```

## Integration Points

1. **Invoice Creation**: On `PaymentConfirmed` event, create invoice snapshot
2. **User Model**: May need `country` field addition (Phase 0 per guide)
3. **Company Model**: Integration with subscription status
4. **Routes**: Protected subscription routes, public webhook routes

## Implementation Phases (Per Guide)

### Phase 0: Foundations
- [ ] Add `country` field to User model (if missing)
- [ ] Create Subscription model
- [ ] Create Payment model (extend existing if needed)
- [ ] Define PaymentGatewayInterface
- [ ] Create adapter skeletons
- [ ] Set up domain events

### Phase 1: One-time Payments
- [ ] Implement MpesaGatewayAdapter
- [ ] Implement StripeGatewayAdapter
- [ ] Create SubscriptionService (orchestration)
- [ ] Set up routes/controllers
- [ ] Handle idempotency

### Phase 2: Subscriptions
- [ ] Implement state machines
- [ ] Add repositories
- [ ] Extend service (cancel, status updates)
- [ ] Integrate invoice creation
- [ ] Build frontend

### Phase 3: Renewals & Edge Cases
- [ ] M-Pesa renewal scheduler
- [ ] Stripe webhook listener
- [ ] Notifications
- [ ] Failure playbook
- [ ] Security validations

## Key Principles (From Guide)

1. **Financial Source of Truth**: InvoiceHub database is authoritative
2. **No Silent Defaults**: All values must be explicit constants
3. **Anti-Abstraction**: Gateway behaviors remain separate
4. **Mandatory Stop Conditions**: Pause if invariants cannot be enforced
5. **Acceptance Checklist**: All items must pass before merge

## Dependencies

- Laravel Cashier (for Stripe integration)
- M-Pesa Daraja API (for M-Pesa integration)
- Email/SMS setup (for notifications)

## Next Steps

1. ✅ Created directory structure
2. ✅ Created migration scripts for platform fee removal
3. ✅ Created removal checklist
4. ⏸️ Awaiting blueprint to create detailed implementation plan
5. ⏸️ Begin Phase 0 implementation after blueprint review

