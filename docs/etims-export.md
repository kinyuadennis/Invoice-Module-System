# ETIMS Export Guide

## Overview

InvoiceHub supports exporting finalized invoices in ETIMS (Electronic Tax Invoice Management System) compatible format. This allows you to export invoice data for submission to KRA eTIMS.

## Important Note

InvoiceHub is **ETIMS-ready**, meaning it can generate export files in a format compatible with ETIMS requirements. This does not constitute full ETIMS compliance or integration. Full ETIMS integration requires official API access from KRA.

## Requirements

To export an invoice for ETIMS:

1. **Invoice must be finalized** - Draft invoices cannot be exported
2. **Company KRA PIN required** - Your company must have a valid KRA PIN
3. **Client KRA PIN recommended** - Client KRA PIN is recommended for complete export
4. **Invoice must have line items** - At least one line item is required
5. **Invoice must have totals** - Subtotal and grand total must be present

## How to Export

### Step 1: Finalize Your Invoice

Before exporting, ensure your invoice is finalized:

1. Go to your invoice
2. Review all details
3. Click "Finalize" (this locks the invoice and creates a snapshot)

### Step 2: Export for ETIMS

Once finalized:

1. Open the finalized invoice detail page
2. Click the **"Export for ETIMS"** button (green button next to PDF download)
3. The export file will download automatically as JSON

### Export Formats

**JSON Format** (Primary):
- File extension: `.json`
- Format: ETIMS-compliant JSON structure
- Contains all invoice data, company/client information, line items, and totals

**XML Format** (Optional):
- File extension: `.xml`
- Format: ETIMS-compliant XML structure
- Access via: `/app/invoices/{id}/export/etims/xml`

## Export File Contents

The ETIMS export includes:

### Invoice Information
- Invoice number
- Issue date
- Due date
- Currency
- PO number (if provided)

### Seller (Your Company)
- KRA PIN
- Company name
- Email
- Phone
- Address
- Registration number

### Buyer (Client)
- KRA PIN (if provided)
- Client name
- Email
- Phone
- Address

### Line Items
- Description
- Quantity
- Unit price
- Total price
- VAT information (rate, amount, included status)

### Totals
- Subtotal
- Discount (if any)
- VAT amount
- Platform fee
- Grand total

## Validation

Before export, the system validates:

- Invoice number format (alphanumeric, max 50 characters)
- Company KRA PIN format (e.g., P051234567A)
- Client KRA PIN format (if provided)
- All required fields are present

If validation fails, you'll see an error message indicating what's missing.

## Troubleshooting

### "Invoice must be finalized to export"

**Solution**: Finalize your invoice first. Draft invoices cannot be exported.

### "Company KRA PIN is missing"

**Solution**: Add your company's KRA PIN in company settings.

### "Invoice snapshot not found"

**Solution**: This should not happen for finalized invoices. Contact support if this occurs.

### Export file is empty or incomplete

**Solution**: Ensure all required fields are filled:
- Company KRA PIN
- Invoice number
- Issue date
- At least one line item
- Totals calculated

## Best Practices

1. **Verify KRA PINs**: Ensure both company and client KRA PINs are correct before finalizing
2. **Review before finalizing**: Once finalized, invoices cannot be edited
3. **Keep export files**: Store exported files for your records
4. **Validate format**: Check exported JSON/XML structure before submitting to ETIMS

## Technical Details

- Export reads from immutable invoice snapshots (created at finalization)
- Export is read-only - never modifies invoice data
- Export format follows ETIMS field mapping requirements
- All monetary values are rounded to 2 decimal places
- Dates are formatted as YYYY-MM-DD

## Future Enhancements

- Direct ETIMS API integration (when available)
- Batch export (multiple invoices)
- Export history/audit log
- Automated export scheduling

## Support

For questions or issues with ETIMS export, contact support with:
- Invoice number
- Export error message (if any)
- Screenshot of the issue

