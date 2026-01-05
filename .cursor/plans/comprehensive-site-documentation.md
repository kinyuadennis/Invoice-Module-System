# InvoiceHub - Comprehensive Site Documentation

**Last Updated:** 2026-01-08  
**Application:** InvoiceHub - Professional Invoice Management System for Kenyan Businesses  
**Tech Stack:** Laravel 12, PHP 8.3, Alpine.js 3.15, Tailwind CSS 4, MySQL  
**Current Completion:** ~75% → Target: 100%

---

## Table of Contents

1. [Application Overview](#application-overview)
2. [User Section](#user-section)
3. [Admin Section](#admin-section)
4. [Landing/Public Section](#landingpublic-section)
5. [System Architecture](#system-architecture)
6. [Database Schema](#database-schema)
7. [Key Features & Workflows](#key-features--workflows)
8. [Implementation Roadmap](#implementation-roadmap)
9. [Future Integrations](#future-integrations)

---

## Application Overview

### What is InvoiceHub?

InvoiceHub is a comprehensive, KRA eTIMS-compliant invoice management platform specifically designed for Kenyan SMEs. It enables businesses to create professional invoices, accept M-PESA payments, manage clients, track expenses, and stay tax-compliant.

### Core Value Propositions

1. **KRA eTIMS Compliance** - All invoices meet Kenyan tax authority requirements
2. **M-PESA Integration** - Native support for Kenya's most popular payment method
3. **Multi-Company Support** - Users can manage multiple businesses from one account
4. **Dual Invoice Creation** - Both wizard and one-page builder interfaces
5. **Automated Workflows** - Reminders, recurring invoices, approvals
6. **Professional Templates** - Multiple customizable templates
7. **Real-time Calculations** - VAT (16%), platform fees (3%), discounts

### Target Audience

- Kenyan SMEs and freelancers
- Businesses requiring KRA-compliant invoicing
- Companies needing M-PESA payment integration
- Organizations managing multiple clients and invoices

---

## User Section (`/app/*`)

### Authentication & Access

**Routes:**
- `/login` - User login
- `/register` - User registration
- `/forgot-password` - Password reset
- `/email/verify` - Email verification

**Features:**
- Email verification required
- Password reset via email
- Session-based authentication
- Remember me functionality

### Onboarding Flow (`/onboarding`)

**Status:** ✅ COMPLETED

**Steps:**
1. Welcome & Introduction
2. Company Setup (with KRA PIN validation)
3. Invoice Customization
4. Payment Methods Setup
5. First Invoice Creation (guided)
6. Completion & Dashboard Tour

### Dashboard (`/app/dashboard`)

**Status:** ✅ COMPLETED (Enhanced)

**Main Features:**

#### KPI Cards
- **Total Revenue** - Sum of all paid invoices (with trend)
- **Outstanding** - Unpaid invoice amounts
- **Total Invoices** - Count by status
- **Recent Activity** - Latest invoices, payments, clients

#### Visual Analytics
- **Revenue Chart** (Last 6 months)
- **Status Distribution** (Doughnut chart)
- **Top Clients** (Bar chart)

#### Quick Actions
- New Invoice
- New Estimate
- New Client
- Record Payment
- Add Company
- View Reports

#### Recent Invoices Table
- List of recent invoices with status
- Quick actions (view, edit, send, PDF)
- Filters by status, date range

________________________________________

### Invoice Management (`/app/invoices`)

**Current Features:**
- ✅ List invoices with filters (status, date, search)
- ✅ Create invoice (wizard or one-page builder)
- ✅ Edit invoice
- ✅ Delete invoice
- ✅ View invoice details
- ✅ Generate PDF
- ✅ Send via Email & WhatsApp
- ✅ Duplicate invoice
- ✅ Record payment
- ✅ Autosave drafts
- ✅ Preview invoice
- ✅ eTIMS export/submit

**Routes:**
```
GET    /app/invoices                    # List invoices
POST   /app/invoices                    # Create invoice
GET    /app/invoices/create             # Create form
GET    /app/invoices/{id}               # Show invoice
PUT    /app/invoices/{id}               # Update invoice
DELETE /app/invoices/{id}               # Delete invoice
GET    /app/invoices/{id}/pdf           # Generate PDF
POST   /app/invoices/{id}/send-email    # Send email
POST   /app/invoices/preview            # Preview
```

**Features In Progress:**
- 🚧 Bulk operations (Delete, Send, Change Status)

________________________________________

### Estimates/Quotes (`/app/estimates`)

**Status:** ✅ COMPLETED

**Features:**
- Create estimates/quotes
- Send to clients
- Track acceptance/rejection
- Convert accepted estimate to invoice
- Expiry date management
- PDF generation

________________________________________

### Expenses Tracking (`/app/expenses`)

**Status:** ✅ COMPLETED

**Features:**
- Record expenses with categories
- Upload receipts (images/PDFs)
- Link expenses to clients/invoices
- Expense reports by category, date range
- Tax-deductible tracking
- Recurring expenses

________________________________________

### Credit Notes (`/app/credit-notes`)

**Status:** ✅ COMPLETED

**Features:**
- Issue credit notes for refunds/adjustments
- Link to original invoice
- eTIMS-compliant reversals
- Apply credit to future invoices
- PDF generation
- Email to client

________________________________________

### Refunds (`/app/refunds`)

**Status:** ✅ COMPLETED

**Features:**
- Process refunds for paid invoices
- Link refunds to payments
- Generate refund receipts

________________________________________

### Client Management (`/app/clients`)

**Current Features:**
- ✅ Search clients (Global Search & AJAX)
- ✅ Create client (Inline & Full form)
- ✅ Client scoping per company
- ✅ KRA PIN validation
- ✅ Phone number normalization

________________________________________

### Recurring Invoices (`/app/recurring-invoices`)

**Status:** ✅ COMPLETED

**Features:**
- Create recurring invoice templates
- Set frequency (daily, weekly, monthly, yearly)
- Auto-generate invoices
- Pause/resume/cancel
- Manual generation

________________________________________

### Reports (`/app/reports`)

**Current Features:**
- ✅ Revenue reports
- ✅ Invoice reports
- ✅ Payment reports
- ✅ CSV export

________________________________________

### Payments (`/app/payments`)

**Current Features:**
- ✅ List all payments
- ✅ View payment details
- ✅ Payment status tracking
- ✅ M-PESA & Stripe integration
- ✅ Bank Reconciliation (Controller exists)

________________________________________

## Admin Section (`/admin/*`)

### Admin Dashboard (`/admin/dashboard`)

**Features:**
- System overview metrics
- Recent activity
- User statistics
- Company statistics

### Management Modules
- **User Management**
- **Company Management**
- **Invoice Management**
- **Payment Management**
- **Client Management**
- **Review Management**
- **Platform Fees**
- **Billing Management**
- **System Settings**
- **Audit Logs**

---

## Landing/Public Section (`/`)

**Features:**
- Hero section with CTA
- Features showcase
- Pricing Page
- About Page
- Testimonials
- Login/Register/Forgot Password

---

## Database Schema

**Core Tables:**
- `invoices`, `invoice_items`
- `estimates`, `estimate_items`
- `expenses`, `expense_categories`
- `credit_notes`, `credit_note_items`
- `companies`, `company_payment_methods`
- `users`, `clients`, `payments`
- `recurring_invoices`, `refunds`

---

## Implementation Roadmap

### Phase 1: Core Features (Completed)
- ✅ User Authentication & Onboarding
- ✅ Company Management
- ✅ Invoice CRUD (Create, Read, Update, Delete)
- ✅ Estimates & Quotes
- ✅ Expenses Tracking
- ✅ Client Management

### Phase 2: Enhanced Functionality (Completed/In Progress)
- ✅ Dashboard Visuals (Revenue Chart, Quick Actions)
- ✅ Global Search
- ✅ Credit Notes & Refunds
- ✅ Recurring Invoices
- ✅ Reports & Export
- 🚧 Bulk Actions for Invoices (Current Task)

### Phase 3: Advanced Integrations (Future)
- ⏳ Advanced AI Insights
- ⏳ Inventory Management
- ⏳ Multi-currency improvements

---

## Future Integrations

### Modern Invoicing Platforms Integration
**Goal:** Seamlessly connect InvoiceHub with external modern platforms to expand capabilities.

**Planned Integrations:**
1.  **Payment Gateways:**
    *   **Direct Bank Feeds:** PSD2/Open Banking integration for real-time reconciliation.
    *   **Crypto Payments:** Accept stablecoins (USDC/USDT) for international clients.
2.  **Accounting Software:**
    *   **Xero/QuickBooks/SAGE:** Two-way sync for accountants.
3.  **E-Commerce:**
    *   **Shopify/WooCommerce:** Auto-generate invoices from store orders.
4.  **Communication:**
    *   **Slack/Microsoft Teams:** Notifications for paid invoices or approvals.
