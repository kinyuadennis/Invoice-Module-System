# Subscriptions Module

This directory contains subscription management domain logic, repositories, and orchestration services.

**Status**: Structure prepared - awaiting blueprint for implementation.

## Directory Structure

```
Subscriptions/
├── Repositories/          # Subscription data access layer (audited CRUD)
├── Events/                # Subscription domain events (SubscriptionActivated, etc.)
└── README.md             # This file
```

## Integration Points

- **Payments Module** (`app/Payments/`): Handles payment gateway interactions
- **SubscriptionService**: Orchestration layer (location TBD - may be in `app/Http/Services/` or here)
- **Models**: Existing models in `app/Models/`:
  - `Subscription` (to be created per blueprint)
  - `CompanySubscription` (already exists - review integration)
  - `Payment` (already exists - review integration)

## Architecture Notes

Based on the implementation guide:

1. **State Machines**: Subscription states enforced via enums/attributes (awaiting blueprint section 4)
2. **Repositories**: Audited CRUD operations for subscriptions
3. **Events**: Integration with invoice creation on PaymentConfirmed
4. **Gateway Routing**: Based on user country (KE → M-Pesa, else → Stripe)

## Implementation Status

- [ ] Subscription model created (awaiting blueprint)
- [ ] SubscriptionRepository implemented (awaiting blueprint)
- [ ] SubscriptionService orchestration (awaiting blueprint)
- [ ] State machine implementation (awaiting blueprint)
- [ ] Integration with invoice creation (awaiting blueprint)

## Important Rules

- **Financial Immutability**: Historical subscription data cannot be mutated
- **Audit Trail**: All subscription state changes must be logged
- **Idempotency**: All state transitions must handle duplicate events
- **Gateway-Specific Logic**: M-Pesa and Stripe behaviors remain separate

