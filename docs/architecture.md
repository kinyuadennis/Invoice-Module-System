# Architecture Overview

## System Overview
InvoiceHub is a comprehensive SaaS E-invoicing platform designed for Kenyan businesses. It supports multi-company management, subscription-based billing, and compliant invoice generation.

## Technology Stack

### Backend
- **Framework**: Laravel 12.x
- **Language**: PHP 8.3+
- **Database**: MySQL 8.0+
- **PDF Generation**: DomPDF

### Frontend
- **Logic**: Alpine.js (Lightweight reactivity)
- **Styling**: Tailwind CSS 4.x (Utility-first)
- **Templating**: Laravel Blade Components

## Core Modules

### 1. Multi-Company Architecture
The system treats "Company" as a first-class citizen. A single user (User model) can belong to or own multiple companies.
- **Middleware**: `EnsureActiveCompany` guarantees a valid company context exists in the session.
- **Service**: `CurrentCompanyService` handles context switching and session persistence.
- **Data Isolation**: All major models (`Invoice`, `Client`, `Payment`) are scoped by `company_id`.

### 2. Invoicing Engine
The core of the platform is the invoicing lifecycle.
- **Lifecycle States**: `Draft` -> `Finalized` -> `Sent` -> `Paid` / `Overdue`.
- **Snapshots**: When finalized, an `InvoiceSnapshot` is created to preserve the exact state of valid invoices for immutable audit trails.
- **Calculations**: Handled server-side (for reliability) and mirrored client-side (for preview) via `InvoiceService`.

### 3. Subscriptions & Payments
- **Models**: `Subscription`, `Payment`, `PaymentAttempt`.
- **Gateways**: 
    - **Stripe**: For international card payments.
    - **M-Pesa**: For local mobile money payments (STK Push & C2B).
- **Webhooks**: Dedicated controllers in `app/Http/Controllers/Webhook/` handle asynchronous payment confirmations.

## Directory Structure

### Backend (`app/`)
- **`Http/Controllers/User`**: User-facing application logic.
- **`Http/Controllers/Admin`**: System administration logic.
- **`Http/Services`**: Business logic encapsulation (e.g., `InvoiceService`, `SubscriptionService`).
- **`Models`**: Eloquent models with strict typing and relationships.

### Frontend (`resources/`)
- **`views/components`**: Reusable UI components (Buttons, Modals, Forms).
- **`css/app.css`**: Tailwind CSS configuration and custom variables.

## Security
- **Authentication**: Laravel Auth.
- **Authorization**: Role-based access control (RBAC) for Admin vs User.
- **Validation**: Strict FormRequests for all write operations.
