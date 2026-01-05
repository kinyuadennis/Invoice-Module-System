# Component Documentation

## Overview
The frontend interacts using server-rendered Blade templates enhanced with Alpine.js for interactivity. We utilize a component-driven architecture to ensure consistency.

## Core Invoice Components

### `InvoiceWizard`
**Path**: `resources/views/components/invoice-wizard.blade.php`
- **Purpose**: A step-by-step guided flow for creating invoices.
- **Steps**: Client logic -> Details -> Items -> Payment -> Summary.
- **State**: Alpine.js manages step transitions and temporary form state.

### `OnePageInvoiceBuilder`
**Path**: `resources/views/components/one-page-invoice-builder.blade.php`
- **Purpose**: A modern, single-page experience for rapid invoice creation.
- **Features**: 
    - Real-time preview.
    - Inline editing of client and line items.
    - Autosave functionality (hooks into `/invoices/autosave`).

## UI Atoms

### `Button`
**Path**: `resources/views/components/button.blade.php`
- **Props**:
    - `variant`: `primary`, `secondary`, `danger`, `outline`.
    - `size`: `sm`, `md`, `lg`.
    - `icon`: Optional icon name (Heroicons).
- **Usage**: Used universally for actions. Supports loading states.

### `Input`
**Path**: `resources/views/components/input.blade.php`
- **Props**: `label`, `name`, `type`, `error` (bag).
- **Behavior**: Includes error message rendering and focus states.

### `Badge`
**Path**: `resources/views/components/badge.blade.php`
- **Purpose**: Status indicators (e.g., Invoice Status: `Paid`, `Overdue`).
- **Variants**: Colors map to status types (Green for Paid, Red for Overdue).

## Modals

### `ClientCreateModal`
**Path**: `resources/views/components/client-create-modal.blade.php`
- **Triggers**: Can be opened from the Invoice Builder.
- **Behavior**: Submits via AJAX, returns the created client object, and auto-selects it in the parent form.

### `PaymentMethodModal`
**Path**: `resources/views/components/payment-method-modal.blade.php`
- **Purpose**: Configuring M-Pesa or Bank details for a company.

## Layout Components

### `AppLayout`
- **Path**: `resources/views/layouts/app.blade.php`
- **Purpose**: Main wrapper for authenticated pages. Includes Navigation and Sidebar.

### `GuestLayout`
- **Path**: `resources/views/layouts/guest.blade.php`
- **Purpose**: Wrapper for Login/Register pages.
