# API & Data Flow

## Overview
While primarily a server-side rendered application, InvoiceHub exposes several endpoints for frontend interactivity (AJAX), webhooks, and public-facing customer portals.

## Internal API (AJAX)
These endpoints are secured by `auth` middleware and meant for use by the application frontend.

### Invoices
- **Preview Calculation**
    - `POST /api/calculate-invoice-preview`
    - **Input**: Line items, tax settings.
    - **Output**: Calculated totals (Subtotal, VAT, Grand Total).
- **Autosave**
    - `POST /app/invoices/autosave`
    - **Purpose**: Periodically save draft invoice state.

### Clients
- **Search**
    - `GET /app/clients/search?query={term}`
    - **Output**: JSON list of clients matching name/email/phone.
- **Quick Create**
    - `POST /app/clients`
    - **Purpose**: Create a client via modal without page reload.

## Customer Portal
These routes are public but secured via a unique signed `token` or hash.

### View Invoice
- `GET /invoice/{token}`
- **Purpose**: Client views their invoice.
- **Methods**: View PDF, Pay via Stripe/M-Pesa.

### Payments
- `POST /invoice/{token}/pay/stripe`
- `POST /invoice/{token}/pay/mpesa`

## Webhooks
These endpoints are public (no CSRF) but rate-limited.

### Stripe
- **URL**: `POST /webhooks/stripe`
- **Events Handled**: `payment_intent.succeeded`, `invoice.paid`.
- **Logic**: Verifies signature, updates Invoice/Subscription status.

### M-Pesa
- **URL**: `POST /webhooks/mpesa/callback`
- **Events Handled**: C2B Validation/Confirmation, STK Push Callback.
- **Logic**: Maps transaction ID to Payment record, completes payment.

## Request/Response Standards
- **AJAX Responses**: Standard JSON format.
    ```json
    {
        "success": true,
        "data": { ... },
        "message": "Operation successful"
    }
    ```
- **Error Handling**: Returns HTTP 422 for validation errors, 403 for authorization.
