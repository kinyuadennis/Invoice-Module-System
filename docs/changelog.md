# Changelog

## [Unreleased] - 2026-01-05

### Architecture
- **Multi-Company Support**: Implemented session-based company switching. Users can now be part of multiple companies and switch context seamlessly.
- **Performance**:
    - Added database indexes for `company_id` on critical tables (`invoices`, `clients`, `payments`).
    - Implemented request-level static caching for `CurrentCompanyService`.
    - Dashboard data now cached for 5 minutes.

### Features
- **Subscriptions**: Added full subscription module with Stripe and M-Pesa integration.
- **Invoice Snapshots**: Immutable invoice state stored upon finalization to ensure audit integrity and consistent PDF regeneration.
- **PDF Generation**: Refactored to use `DomPDF` with optimized templates and header/footer partials.

### Developer Experience
- **Telescope**: Integrated Laravel Telescope for debugging and monitoring (tagged by company context).
- **Slow Query Logging**: Added listeners to log queries exceeding 50ms (configurable).

### Bug Fixes
- Fixed stale user object handling during company switching.
- Resolved N+1 query issues in Invoice listing and Dashboard.
- Fixed font loading issues in PDF generation.
