# Frontend Structure - Invoice Module Management System

## View Directory Structure

```
resources/views/
├── layouts/
│   ├── app.blade.php          # Main application layout (sidebar + navbar)
│   └── guest.blade.php        # Guest layout for auth pages
├── components/
│   ├── alert.blade.php        # Alert/notification component
│   ├── badge.blade.php        # Status badge component
│   ├── button.blade.php       # Button component
│   ├── card.blade.php          # Card container component
│   ├── input.blade.php         # Input field component
│   ├── modal.blade.php         # Modal dialog component
│   ├── nav-link.blade.php      # Navigation link component
│   ├── select.blade.php        # Select dropdown component
│   └── table.blade.php         # Table component
├── auth/
│   ├── login.blade.php         # Login page
│   ├── register.blade.php      # Registration page
│   └── verify-email.blade.php  # Email verification page
├── dashboard/
│   └── index.blade.php         # Dashboard with KPIs and recent invoices
├── invoices/
│   ├── index.blade.php         # Invoice list with filters
│   ├── create.blade.php        # Create invoice form (with Alpine.js)
│   ├── edit.blade.php          # Edit invoice form (with Alpine.js)
│   └── show.blade.php          # Invoice detail view
├── clients/
│   ├── index.blade.php         # Client list
│   ├── create.blade.php        # Create client form
│   └── edit.blade.php          # Edit client form
├── payments/
│   └── index.blade.php         # Payment list
├── settings/
│   └── index.blade.php         # Settings page
└── profile/
    └── index.blade.php         # User profile page
```

## Key Features

### 1. Layouts
- **app.blade.php**: Main layout with responsive sidebar, top navbar, user menu, and flash message display
- **guest.blade.php**: Minimal layout for authentication pages

### 2. Reusable Components
All components use Laravel's component system (`<x-component-name>`) and are styled with Tailwind CSS:
- **Card**: Container with padding options (none, sm, default, lg)
- **Button**: Multiple variants (primary, secondary, outline, danger, ghost) and sizes
- **Badge**: Status indicators with color variants
- **Alert**: Flash message display with icons
- **Input/Select**: Form fields with validation error display
- **Table**: Responsive table with header slot
- **Modal**: Alpine.js-powered modal dialogs
- **Nav-link**: Navigation links with active state

### 3. Pages

#### Authentication
- Login and registration forms with validation
- Email verification page
- All use `guest.blade.php` layout

#### Dashboard
- KPI cards (Total Revenue, Outstanding, Overdue, Paid Invoices)
- Recent invoices table
- Alert banners for overdue/outstanding invoices
- Empty state with CTA

#### Invoices
- **Index**: Filterable table with search, status filter, date range filter
- **Create/Edit**: Dynamic line items with Alpine.js, live total calculation
- **Show**: Detailed invoice view with client info, line items, totals, payments

#### Clients
- **Index**: Client list with invoice count
- **Create/Edit**: Simple form for client information

#### Payments
- Payment list with invoice and client information

#### Settings
- Profile information
- Platform fee configuration (admin only)
- Account actions

### 4. Alpine.js Integration
- Dynamic line items in invoice forms (add/remove rows)
- Live calculation of subtotals, tax, and totals
- Modal dialogs
- Dropdown menus
- Mobile sidebar toggle

### 5. Responsive Design
- Mobile-first approach with Tailwind breakpoints
- Collapsible sidebar on mobile
- Responsive tables with horizontal scroll
- Grid layouts that adapt to screen size

### 6. UX Features
- Flash message notifications (success, error, info, warning)
- Empty states with helpful CTAs
- Loading states and disabled buttons
- Confirmation dialogs for destructive actions
- Color-coded status badges
- Form validation with inline error messages

## Routes

All routes are defined in `routes/web.php`:
- `/` - Home (redirects to dashboard)
- `/login`, `/register` - Authentication
- `/dashboard` - Main dashboard (requires verified email)
- `/invoices/*` - Invoice CRUD operations
- `/clients/*` - Client management (admin/staff only)
- `/payments` - Payment listing
- `/settings` - Settings page
- `/profile` - User profile

## Styling

- **Tailwind CSS v4**: Utility-first CSS framework
- **Color Palette**: Indigo primary, gray neutrals, semantic colors (green, yellow, red)
- **Typography**: System font stack with clear hierarchy
- **Spacing**: Consistent 4px-based spacing scale
- **Shadows**: Subtle layered shadows for depth

## JavaScript

- **Alpine.js**: Lightweight framework for interactivity
- **Axios**: HTTP client for AJAX requests (via bootstrap.js)
- **Vanilla JS**: Minimal custom JavaScript

## Next Steps

1. Test all pages with actual data
2. Add pagination styling if needed
3. Implement PDF generation links
4. Add more advanced filters if needed
5. Enhance mobile experience
6. Add loading states for async operations
7. Implement real-time notifications if needed

