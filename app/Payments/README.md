# Payments & Subscriptions Module

This directory contains the payment gateway adapters and subscription payment orchestration logic.

**Status**: Structure prepared - awaiting blueprint for implementation.

## Directory Structure

```
Payments/
├── Contracts/              # Payment gateway interfaces (awaiting blueprint definition)
├── Adapters/              # Gateway-specific implementations (M-Pesa, Stripe)
├── Events/                # Domain events (PaymentConfirmed, PaymentFailed, etc.)
└── README.md             # This file
```

## Architecture Notes

Based on the implementation guide, this module follows:

1. **Gateway Contract First**: Interfaces define the contract (awaiting blueprint section 3)
2. **Gateway-Specific Adapters**: M-Pesa and Stripe adapters remain separate (no over-abstraction)
3. **Domain Events**: Events for payment lifecycle (PaymentConfirmed, PaymentFailed, etc.)
4. **Financial Source of Truth**: InvoiceHub database is authoritative, not gateway data

## Implementation Status

- [ ] PaymentGatewayInterface defined (awaiting blueprint)
- [ ] MpesaGatewayAdapter implemented (awaiting blueprint)
- [ ] StripeGatewayAdapter implemented (awaiting blueprint)
- [ ] Domain events created (awaiting blueprint)
- [ ] Integration with SubscriptionService (awaiting blueprint)

## Important Rules

- **No Silent Defaults**: All configurable values must be explicit constants
- **Anti-Abstraction**: Gateway behaviors remain separate and explicit
- **Idempotency**: All payment operations must handle duplicate callbacks/webhooks
- **Immutability**: Historical payment data cannot be mutated

