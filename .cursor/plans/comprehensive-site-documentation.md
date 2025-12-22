# InvoiceHub - Comprehensive Site Documentation

**Last Updated:** 2025-12-22  
**Application:** InvoiceHub - Professional Invoice Management System for Kenyan Businesses  
**Tech Stack:** Laravel 12, PHP 8.3, Alpine.js 3.15, Tailwind CSS 4, MySQL  
**Current Completion:** ~40% → Target: 80%

---

## Table of Contents

1. [Application Overview](#application-overview)
2. [User Section](#user-section)
3. [Admin Section](#admin-section)
4. [Landing/Public Section](#landingpublic-section)
5. [System Architecture](#system-architecture)
6. [Database Schema](#database-schema)
7. [Key Features & Workflows](#key-features--workflows)
8. [Implementation Roadmap (40% → 80%)](#implementation-roadmap)

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

**Steps:**
1. Welcome & Introduction
2. Company Setup (with KRA PIN validation)
3. Invoice Customization
4. Payment Methods Setup
5. First Invoice Creation (guided)
6. Completion & Dashboard Tour

### Dashboard (`/app/dashboard`)

**Main Features:**

#### KPI Cards
- **Total Revenue** - Sum of all paid invoices
- **Outstanding** - Unpaid invoice amounts
- **Total Invoices** - Count by status
- **Recent Activity** - Latest invoices, payments, clients

#### Quick Actions
- New Invoice
- Add Company
- View Reports
- Create Estimate (NEW - Phase 1)
- Add Expense (NEW - Phase 1)

#### Recent Invoices Table
- List of recent invoices with status
- Quick actions (view, edit, send, PDF)
- Filters by status, date range

#### Activity Feed
- Invoice created/sent/paid
- Payments received
- Client added
- Company settings changed

#### Feedback Button
- Fixed bottom-right button
- Modal for submitting feedback
- Anonymous option available

### Invoice Management (`/app/invoices`)

**Current Features:**
- ✅ List invoices with filters (status, date, search)
- ✅ Create invoice (wizard or one-page builder)
- ✅ Edit invoice
- ✅ Delete invoice
- ✅ View invoice details
- ✅ Generate PDF
- ✅ Send via Email
- ✅ Send via WhatsApp
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
POST   /app/invoices/{id}/send-whatsapp # Send WhatsApp
POST   /app/invoices/{id}/duplicate     # Duplicate
POST   /app/invoices/{id}/record-payment # Record payment
POST   /app/invoices/autosave           # Autosave draft
POST   /app/invoices/preview            # Preview
GET    /app/invoices/preview-frame      # Preview iframe
```

**NEW Features (To Implement):**
- ⏳ Partial payments tracking
- ⏳ Refund processing
- ⏳ Approval workflow
- ⏳ Invoice templates library
- ⏳ Bulk operations

### Estimates/Quotes (`/app/estimates`) - **NEW - Phase 1**

**Status:** 🚧 IN PROGRESS

**Features:**
- Create estimates/quotes
- Send to clients
- Track acceptance/rejection
- Convert accepted estimate to invoice
- Expiry date management
- PDF generation
- Email/WhatsApp sending

**Routes:**
```
GET    /app/estimates                   # List estimates
POST   /app/estimates                   # Create estimate
GET    /app/estimates/create            # Create form
GET    /app/estimates/{id}              # Show estimate
PUT    /app/estimates/{id}              # Update estimate
DELETE /app/estimates/{id}              # Delete estimate
POST   /app/estimates/{id}/convert      # Convert to invoice
POST   /app/estimates/{id}/send         # Send to client
GET    /app/estimates/{id}/pdf          # Generate PDF
```

**Database:**
- `estimates` table
- `estimate_items` table
- Status: draft, sent, accepted, rejected, expired, converted

### Expenses Tracking (`/app/expenses`) - **NEW - Phase 1**

**Status:** ⏳ PENDING

**Features:**
- Record expenses with categories
- Upload receipts (images/PDFs)
- Link expenses to clients/invoices
- Expense reports by category, date range
- Tax-deductible tracking
- Recurring expenses

**Routes:**
```
GET    /app/expenses                    # List expenses
POST   /app/expenses                    # Create expense
GET    /app/expenses/create             # Create form
GET    /app/expenses/{id}               # Show expense
PUT    /app/expenses/{id}               # Update expense
DELETE /app/expenses/{id}               # Delete expense
GET    /app/expenses/categories         # List categories
POST   /app/expenses/categories         # Create category
```

**Database:**
- `expenses` table
- `expense_categories` table

### Credit Notes (`/app/credit-notes`) - **NEW - Phase 2**

**Status:** ⏳ PENDING

**Features:**
- Issue credit notes for refunds/adjustments
- Link to original invoice
- eTIMS-compliant reversals
- Apply credit to future invoices
- PDF generation
- Email to client

### Client Management (`/app/clients`)

**Current Features:**
- ✅ Search clients (AJAX)
- ✅ Create client (inline modal)
- ✅ Client scoping per company
- ✅ KRA PIN validation
- ✅ Phone number normalization

**Routes:**
```
POST   /app/clients                     # Create client
GET    /app/clients/search              # Search clients
```

**NEW Features (To Implement):**
- ⏳ Client notes & tags
- ⏳ Activity logs
- ⏳ Communication history
- ⏳ Import/Export (CSV/Excel)
- ⏳ Client value scoring

### Company Management (`/app/companies`)

**Current Features:**
- ✅ Create multiple companies
- ✅ Switch between companies
- ✅ Company settings
- ✅ Invoice customization
- ✅ Payment methods configuration
- ✅ Branding & logo
- ✅ Invoice templates

**Routes:**
```
GET    /app/companies                   # List companies
POST   /app/companies                  # Create company
GET    /app/companies/create            # Create form
GET    /app/companies/{id}              # Show company
PUT    /app/companies/{id}              # Update company
DELETE /app/companies/{id}              # Delete company
POST   /app/company/switch              # Switch active company
GET    /app/company/settings            # Company settings
PUT    /app/company                    # Update company
GET    /app/company/invoice-customization # Invoice customization
POST   /app/company/invoice-format      # Update invoice format
POST   /app/company/invoice-template     # Update template
POST   /app/company/branding             # Update branding
POST   /app/company/payment-methods      # Manage payment methods
```

**NEW Features (To Implement):**
- ⏳ Multi-user roles & permissions
- ⏳ User invitations
- ⏳ Role-based access control

### Recurring Invoices (`/app/recurring-invoices`)

**Current Features:**
- ✅ Create recurring invoice templates
- ✅ Set frequency (daily, weekly, monthly, yearly)
- ✅ Auto-generate invoices
- ✅ Pause/resume/cancel
- ✅ Manual generation

**Routes:**
```
GET    /app/recurring-invoices
POST   /app/recurring-invoices
GET    /app/recurring-invoices/create
GET    /app/recurring-invoices/{id}
PUT    /app/recurring-invoices/{id}
DELETE /app/recurring-invoices/{id}
POST   /app/recurring-invoices/{id}/pause
POST   /app/recurring-invoices/{id}/resume
POST   /app/recurring-invoices/{id}/cancel
POST   /app/recurring-invoices/{id}/generate
```

### Reports (`/app/reports`)

**Current Features:**
- ✅ Revenue reports
- ✅ Invoice reports
- ✅ Payment reports
- ✅ CSV export

**Routes:**
```
GET    /app/reports                     # Reports dashboard
GET    /app/reports/revenue             # Revenue report
GET    /app/reports/invoices            # Invoice report
GET    /app/reports/payments            # Payment report
GET    /app/reports/export/invoices-csv # Export invoices CSV
GET    /app/reports/export/revenue-csv  # Export revenue CSV
```

**NEW Features (To Implement):**
- ⏳ Aging reports (overdue analysis)
- ⏳ Profit & Loss statements
- ⏳ Expense breakdowns
- ⏳ Cash flow reports
- ⏳ Client analysis reports
- ⏳ Product/service analysis

### Payments (`/app/payments`)

**Current Features:**
- ✅ List all payments
- ✅ View payment details
- ✅ Payment status tracking
- ✅ M-PESA & Stripe integration

**Routes:**
```
GET    /app/payments                    # List payments
GET    /app/payments/{id}               # Show payment
```

**NEW Features (To Implement):**
- ⏳ Partial payments
- ⏳ Refund processing
- ⏳ Payment allocation
- ⏳ Bank reconciliation

### Profile Management (`/app/profile`)

**Current Features:**
- ✅ View profile
- ✅ Update profile information
- ✅ Change password
- ✅ Upload profile photo
- ✅ Delete profile photo

**Routes:**
```
GET    /app/profile                     # View profile
PUT    /app/profile                     # Update profile
PUT    /app/profile/password            # Update password
DELETE /app/profile/photo               # Delete photo
```

### Customer Portal (`/invoice/{token}`)

**Features:**
- View invoice via secure token
- Download PDF
- Pay via Stripe
- Pay via M-PESA
- Check payment status

**Routes:**
```
GET    /invoice/{token}                  # View invoice
GET    /invoice/{token}/pdf              # Download PDF
POST   /invoice/{token}/pay/stripe      # Pay via Stripe
POST   /invoice/{token}/pay/mpesa       # Pay via M-PESA
GET    /invoice/{token}/payment-status  # Check payment status
```

---

## Admin Section (`/admin/*`)

### Admin Dashboard (`/admin/dashboard`)

**Features:**
- System overview metrics
- Recent activity
- User statistics
- Company statistics
- Invoice statistics
- Payment statistics

**Routes:**
```
GET    /admin/dashboard                 # Admin dashboard
```

### User Management (`/admin/users`)

**Features:**
- ✅ List all users
- ✅ View user details
- ✅ Edit user
- ✅ Delete user
- ⏳ Bulk actions
- ⏳ Role templates

**Routes:**
```
GET    /admin/users                     # List users
GET    /admin/users/{id}                # Show user
PUT    /admin/users/{id}                # Update user
DELETE /admin/users/{id}                # Delete user
```

### Company Management (`/admin/companies`)

**Features:**
- ✅ List all companies
- ✅ View company details
- ✅ Edit company
- ✅ Delete company
- ⏳ Compliance audits
- ⏳ Custom limits per plan

**Routes:**
```
GET    /admin/companies                 # List companies
GET    /admin/companies/{id}            # Show company
PUT    /admin/companies/{id}            # Update company
DELETE /admin/companies/{id}            # Delete company
GET    /admin/companies/{id}/edit       # Edit form
```

### Invoice Management (`/admin/invoices`)

**Features:**
- ✅ View all invoices
- ✅ View invoice details
- ⏳ Global search
- ⏳ Dispute resolution

**Routes:**
```
GET    /admin/invoices                  # List invoices
GET    /admin/invoices/{id}             # Show invoice
```

### Payment Management (`/admin/payments`)

**Features:**
- ✅ View all payments
- ✅ View payment details
- ⏳ Refund processing
- ⏳ Fraud detection logs

**Routes:**
```
GET    /admin/payments                  # List payments
GET    /admin/payments/{id}             # Show payment
```

### Client Management (`/admin/clients`)

**Features:**
- ✅ List all clients
- ✅ Create client
- ✅ View client
- ✅ Edit client
- ✅ Delete client
- ⏳ Data deduplication

**Routes:**
```
GET    /admin/clients                   # List clients
POST   /admin/clients                   # Create client
GET    /admin/clients/create            # Create form
GET    /admin/clients/{id}              # Show client
PUT    /admin/clients/{id}              # Update client
DELETE /admin/clients/{id}              # Delete client
GET    /admin/clients/{id}/edit         # Edit form
```

### Review Management (`/admin/reviews`)

**Features:**
- ✅ List reviews
- ✅ Approve reviews
- ✅ Edit reviews
- ✅ Delete reviews

**Routes:**
```
GET    /admin/reviews                   # List reviews
POST   /admin/reviews                   # Create review
GET    /admin/reviews/create            # Create form
GET    /admin/reviews/{id}              # Show review
PUT    /admin/reviews/{id}              # Update review
DELETE /admin/reviews/{id}              # Delete review
POST   /admin/reviews/{id}/approve      # Approve review
GET    /admin/reviews/{id}/edit         # Edit form
```

### Platform Fees (`/admin/platform-fees`)

**Features:**
- ✅ View platform fee collection
- ✅ Fee analytics
- ✅ Fee reports

**Routes:**
```
GET    /admin/platform-fees             # Platform fees dashboard
```

### Billing Management (`/admin/billing`)

**Features:**
- ✅ View subscription plans
- ✅ View company subscriptions
- ✅ View billing history
- ⏳ Promo codes
- ⏳ Churn reports

**Routes:**
```
GET    /admin/billing/plans             # List plans
GET    /admin/billing/subscriptions    # List subscriptions
GET    /admin/billing/subscriptions/{id} # Show subscription
GET    /admin/billing/history          # Billing history
```

### System Settings (`/admin/system-settings`)

**Features:**
- ✅ View system settings
- ✅ Update system settings
- ⏳ AI feature toggles
- ⏳ Integration marketplace

**Routes:**
```
GET    /admin/system-settings           # View settings
PUT    /admin/system-settings          # Update settings
```

### Audit Logs (`/admin/audit-logs`)

**Features:**
- ✅ View audit logs
- ✅ View audit log details
- ⏳ Export for compliance

**Routes:**
```
GET    /admin/audit-logs                # List audit logs
GET    /admin/audit-logs/{id}           # Show audit log
```

### Support Ticketing (`/admin/support`) - **NEW**

**Status:** ⏳ PENDING

**Features:**
- View user support tickets
- Respond to tickets
- Ticket status tracking
- Knowledge base integration

---

## Landing/Public Section (`/`)

### Home Page (`/`)

**Features:**
- Hero section with CTA
- Features showcase
- Product screenshots
- How it works section
- Testimonials
- Case studies
- FAQ section
- Sticky CTA bar (mobile)
- Demo walkthrough

**Components:**
- `<x-hero.hero-split>` - Hero section
- `<x-trust.social-proof-bar>` - Social proof
- `<x-trust.customer-logos>` - Customer logos
- `<x-product-screenshots>` - Product preview
- `<x-demo-walkthrough>` - Interactive demo
- `<x-testimonials.testimonials-grid>` - Testimonials
- `<x-case-studies>` - Case studies

### About Page (`/about`)

**Features:**
- Company story
- Mission & vision
- Team information
- Values

### Pricing Page (`/pricing`)

**Features:**
- Subscription plans
- Feature comparison
- Pricing tiers
- CTA buttons

### Testimonials Page (`/testimonials`)

**Features:**
- Public testimonials
- Review grid
- Filter by rating

### Authentication Pages

**Login (`/login`):**
- Email/password login
- Remember me
- Forgot password link

**Register (`/register`):**
- User registration form
- Email verification required
- Terms acceptance

**Password Reset (`/forgot-password`):**
- Email-based reset
- Token-based reset link

---

## System Architecture

### Backend Stack
- **Laravel 12.39.0** - PHP framework
- **PHP 8.3.28** - Language version
- **MySQL** - Database
- **Alpine.js 3.15.2** - Frontend reactivity
- **Tailwind CSS 4.1.17** - Styling

### Key Services
- `InvoiceService` - Invoice business logic
- `PlatformFeeService` - Fee calculations
- `PhoneNumberService` - Phone normalization
- `InvoicePrefixService` - Invoice numbering
- `InvoiceSnapshotService` - Invoice snapshots
- `PdfInvoiceRenderer` - PDF generation
- `CurrentCompanyService` - Company scoping

### Key Models
- `Invoice` - Invoices
- `InvoiceItem` - Line items
- `Client` - Clients
- `Company` - Companies
- `Payment` - Payments
- `RecurringInvoice` - Recurring templates
- `Estimate` - Estimates (NEW)
- `EstimateItem` - Estimate items (NEW)

---

## Database Schema

### Core Tables

**Invoices:**
- `invoices` - Main invoice table
- `invoice_items` - Line items
- `invoice_templates` - Template library
- `invoice_access_tokens` - Customer portal tokens
- `invoice_snapshots` - Invoice history
- `invoice_reminder_logs` - Reminder tracking
- `invoice_audit_logs` - Audit trail
- `invoice_prefixes` - Numbering prefixes

**Estimates (NEW):**
- `estimates` - Main estimate table
- `estimate_items` - Estimate line items

**Expenses (NEW - To Implement):**
- `expenses` - Expense records
- `expense_categories` - Expense categories

**Credit Notes (NEW - To Implement):**
- `credit_notes` - Credit note records
- `credit_note_items` - Credit note items

**Companies:**
- `companies` - Company information
- `company_payment_methods` - Payment methods
- `company_subscriptions` - Subscription management

**Users:**
- `users` - User accounts
- `email_verifications` - Email verification

**Clients:**
- `clients` - Client information

**Payments:**
- `payments` - Payment records
- `payment_transactions` - Gateway transactions
- `platform_fees` - Platform fee tracking

**Other:**
- `items` - Reusable items library
- `services` - Service library
- `recurring_invoices` - Recurring templates
- `reviews` - Customer reviews
- `feedback` - User feedback
- `alerts` - System alerts
- `audit_logs` - System audit trail
- `subscription_plans` - Subscription plans
- `billing_history` - Billing records
- `user_invoice_templates` - User saved templates

---

## Key Features & Workflows

### Invoice Creation Workflow

1. **Select Client** - Search or create new
2. **Invoice Details** - Dates, reference, PO number
3. **Add Line Items** - Services or custom items
4. **Review Summary** - Totals, VAT, fees
5. **Payment Method** - Select configured method
6. **Save & Send** - Draft, PDF, Email, WhatsApp

### Payment Processing Workflow

1. **Invoice Sent** - Client receives invoice
2. **Payment Initiated** - Via M-PESA or Stripe
3. **Payment Processing** - Gateway handles transaction
4. **Webhook Received** - Payment confirmed
5. **Invoice Updated** - Status changed to paid
6. **Notifications** - User and client notified

### eTIMS Integration Workflow

1. **Invoice Created** - With all required fields
2. **eTIMS Validation** - Pre-submission checks
3. **QR Code Generation** - For invoice
4. **Submission** - To KRA eTIMS system
5. **Confirmation** - Control number received
6. **Invoice Updated** - eTIMS metadata stored

### Recurring Invoice Workflow

1. **Create Template** - Set frequency and items
2. **Schedule** - System schedules generation
3. **Auto-Generate** - Invoices created automatically
4. **Auto-Send** - If enabled, sent to client
5. **Tracking** - Generation history tracked

### Estimate Workflow (NEW)

1. **Create Estimate** - Similar to invoice creation
2. **Send to Client** - Via email/WhatsApp
3. **Client Response** - Accept, reject, or expires
4. **Convert to Invoice** - If accepted, one-click conversion
5. **Invoice Created** - From estimate data

---

## Implementation Roadmap (40% → 80%)

### Phase 1: Core Business Features (Week 1-2) - **+15% → 55%**

1. ✅ **Estimates/Quotes System** (IN PROGRESS)
   - Database migrations ✅
   - Models ✅
   - Controller (IN PROGRESS)
   - Routes (PENDING)
   - Views (PENDING)

2. **Expenses Tracking** (PENDING)
   - Database migrations
   - Models
   - Controller
   - Routes
   - Views

3. **Enhanced Dashboard KPIs** (PENDING)
   - Expense overview widget
   - Cash flow indicators
   - Quick insights

### Phase 2: Financial Management (Week 3-4) - **+10% → 65%**

4. **Credit Notes** (PENDING)
5. **Partial Payments & Refunds** (PENDING)
6. **Advanced Reports** (PENDING)

### Phase 3: Operations & Workflow (Week 5-6) - **+10% → 75%**

7. **Multi-User Roles & Permissions** (PENDING)
8. **Approval Workflows** (PENDING)
9. **Inventory Management** (PENDING)

### Phase 4: Integration & Automation (Week 7-8) - **+5% → 80%**

10. **Bank Reconciliation** (PENDING)
11. **Enhanced eTIMS** (PENDING)
12. **Client CRM Features** (PENDING)
13. **Data Import/Export** (PENDING)
14. **Support Ticketing** (PENDING)

---

## Current Status Summary

### ✅ Completed (40%)
- Basic invoice management
- Client management
- Payment processing
- Recurring invoices
- Basic reports
- eTIMS integration (basic)
- Company management
- User dashboard
- Admin panel (basic)

### 🚧 In Progress (5%)
- Estimates/Quotes system (models, migrations done)

### ⏳ Pending (35%)
- Expenses tracking
- Credit notes
- Advanced reports
- Multi-user roles
- Approval workflows
- Inventory management
- Partial payments
- Bank reconciliation
- Enhanced eTIMS
- Client CRM
- Import/Export
- Support ticketing

**Total Progress: 40% → Target: 80%**

---

## Next Immediate Steps

1. **Complete Estimates System** (Current Priority)
   - Finish EstimateController
   - Add routes
   - Create views
   - Add conversion to invoice feature

2. **Implement Expenses Tracking**
   - Create migrations
   - Build models
   - Create controller
   - Add routes & views

3. **Enhance Dashboard**
   - Add expense KPIs
   - Add cash flow indicators
   - Improve insights

4. **Continue with Phase 2-4 features** in priority order

---

**Document Status:** Active Implementation  
**Last Updated:** 2025-12-22  
**Next Review:** After Phase 1 completion

