# Phase 1: Financial Truth Definition

## Purpose

This document explicitly defines what "financial truth" means for invoice snapshots. The snapshot must contain everything required to reproduce the invoice exactly as it was at finalization time, without touching live, mutable data.

**Rule:** If the PDF would need it, the snapshot must contain it.

---

## Invoice-Level Financial Truth

### Identity & Reference
- `invoice_number` - The unique invoice identifier
- `invoice_reference` - Reference number
- `po_number` - Purchase order number (if any)
- `uuid` - System UUID

### Dates
- `issue_date` - Date invoice was issued
- `due_date` - Payment due date

### Status
- `status` - Status at finalization time (should be 'finalized')

### Currency
- `currency` - Currency code (e.g., 'KES')

### Company Identity (Snapshot)
- `company_id` - Company database ID
- `company_name` - Company legal name at finalization
- `company_email` - Company email at finalization
- `company_phone` - Company phone at finalization
- `company_address` - Company address at finalization
- `company_kra_pin` - Company KRA PIN at finalization
- `company_registration_number` - Company registration number at finalization
- `company_logo` - Company logo path at finalization (if any)

### Client Identity (Snapshot)
- `client_id` - Client database ID
- `client_name` - Client name at finalization
- `client_email` - Client email at finalization
- `client_phone` - Client phone at finalization
- `client_address` - Client address at finalization
- `client_kra_pin` - Client KRA PIN at finalization (if any)

### VAT Configuration (Used at Finalization)
- `vat_registered` - Boolean: was VAT registered at finalization
- `vat_rate_used` - VAT rate percentage used (e.g., 16.00)
- `vat_enabled` - Was VAT enabled for company at finalization

### Platform Fee Configuration (Used at Finalization)
- `platform_fee_rate_used` - Platform fee rate used (e.g., 0.03 for 3%)
- `platform_fee_enabled` - Was platform fee enabled at finalization

### Payment Configuration
- `payment_method` - Payment method at finalization
- `payment_details` - Payment details at finalization
- `payment_terms` - Payment terms at finalization

### Notes & Terms
- `notes` - Invoice notes at finalization
- `terms_and_conditions` - Terms and conditions at finalization

---

## Line-Item-Level Financial Truth

Each line item must include:

- `description` - Item description/name
- `quantity` - Quantity
- `unit_price` - Unit price at finalization
- `total_price` - Line total (quantity * unit_price)
- `vat_included` - Was VAT included in price
- `vat_rate` - VAT rate for this item (if item-specific)
- `vat_amount` - Explicit VAT amount for this line (not derived)

**Note:** Line item VAT amount must be stored explicitly, not calculated from totals.

---

## Totals (Financial Truth)

### Subtotal
- `subtotal` - Sum of all line items before discounts

### Discount
- `discount` - Discount amount
- `discount_type` - 'fixed' or 'percentage'
- `subtotal_after_discount` - Subtotal after discount applied

### VAT
- `vat_amount` - Total VAT amount (explicit, not derived)
- `tax` - Alias for vat_amount (for backward compatibility)

### Platform Fee
- `platform_fee` - Platform fee amount
- `platform_fee_calculation_base` - What the fee was calculated on (subtotal + VAT)

### Grand Total
- `total` - Total before platform fee (subtotal + VAT)
- `grand_total` - Final total including platform fee

---

## Template & Branding (For PDF Rendering)

- `template_id` - Template ID used at finalization
- `template_view_path` - Template view path at finalization
- `company_branding` - Company branding settings at finalization (JSON)
  - Logo path
  - Colors
  - PDF settings
  - Footer settings

---

## Metadata

- `snapshot_taken_at` - Timestamp when snapshot was created
- `snapshot_taken_by` - User ID who finalized (if available)
- `legacy_snapshot` - Boolean: true if created retroactively for existing invoice

---

## What Is NOT Included

- User who created invoice (not needed for financial truth)
- Invoice creation timestamp (not needed for financial truth)
- Updated timestamps (snapshot is immutable)
- Relationships to live models (snapshot is self-contained)

---

## Snapshot Structure (JSON Payload)

The snapshot will be stored as a JSON structure:

```json
{
  "invoice": {
    "invoice_number": "INV-001",
    "issue_date": "2025-12-15",
    "due_date": "2026-01-15",
    "status": "finalized",
    "currency": "KES",
    "po_number": null,
    "notes": "Payment terms: Net 30",
    "terms_and_conditions": null
  },
  "company": {
    "id": 1,
    "name": "Acme Corp",
    "email": "info@acme.com",
    "phone": "+254700000000",
    "address": "Nairobi, Kenya",
    "kra_pin": "P123456789A",
    "registration_number": "C.123456",
    "logo": "storage/logos/acme.png"
  },
  "client": {
    "id": 1,
    "name": "Client Name",
    "email": "client@example.com",
    "phone": "+254711111111",
    "address": "Client Address",
    "kra_pin": null
  },
  "configuration": {
    "vat_registered": true,
    "vat_rate_used": 16.00,
    "vat_enabled": true,
    "platform_fee_rate_used": 0.03,
    "platform_fee_enabled": true,
    "payment_method": "mpesa",
    "payment_details": "Paybill: 123456",
    "payment_terms": "Net 30"
  },
  "items": [
    {
      "description": "Web Development",
      "quantity": 10,
      "unit_price": 5000.00,
      "total_price": 50000.00,
      "vat_included": false,
      "vat_rate": 16.00,
      "vat_amount": 8000.00
    }
  ],
  "totals": {
    "subtotal": 50000.00,
    "discount": 0.00,
    "discount_type": null,
    "subtotal_after_discount": 50000.00,
    "vat_amount": 8000.00,
    "tax": 8000.00,
    "platform_fee": 1740.00,
    "platform_fee_calculation_base": 58000.00,
    "total": 58000.00,
    "grand_total": 59740.00
  },
  "template": {
    "id": 1,
    "view_path": "invoices.templates.modern-clean",
    "name": "Modern Clean"
  },
  "branding": {
    "logo_path": "storage/logos/acme.png",
    "show_software_credit": true,
    "pdf_settings": {
      "show_software_credit": true
    }
  },
  "metadata": {
    "snapshot_taken_at": "2025-12-15T10:30:00Z",
    "snapshot_taken_by": 1,
    "legacy_snapshot": false
  }
}
```

---

## Key Principles

1. **Self-Contained:** Snapshot must contain all data needed to render invoice
2. **Explicit Values:** Store calculated values, not formulas
3. **Historical Accuracy:** Capture what was used, not what should be used
4. **No Dependencies:** Snapshot must not require live database queries
5. **Immutable:** Once created, snapshot never changes

---

## Implementation Notes

- JSON structure allows flexibility for future additions
- All monetary values stored as decimals (not floats)
- All dates stored as ISO 8601 strings
- Company and client data snapshotted to prevent changes affecting historical invoices
- Configuration values captured to enable reproduction even if company settings change

