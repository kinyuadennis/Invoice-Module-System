<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice['invoice_number'] ?? 'INV-' . $invoice['id'] }}</title>
    <style>
        @page {
            margin: 43px; /* Standard margins - no header/footer space needed */
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            font-size: 12pt;
            color: #333333;
            line-height: 1.5;
        }
        .container {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }
        
        /* Invoice Header Section */
        .invoice-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #E0E0E0;
        }
        .invoice-header-top {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 20px;
        }
        .invoice-header-left {
            display: table-cell;
            vertical-align: top;
        }
        .invoice-header-right {
            display: table-cell;
            vertical-align: top;
            text-align: right;
        }
        .invoice-title {
            font-size: 28pt;
            font-weight: bold;
            color: #1A73E8;
            margin-bottom: 8pt;
        }
        .invoice-number {
            font-size: 18pt;
            font-weight: bold;
            color: #1A73E8;
            margin-bottom: 8pt;
        }
        .invoice-reference {
            font-size: 11pt;
            color: #666;
            margin-bottom: 6pt;
            font-weight: normal;
        }
        .company-logo {
            max-width: 120pt;
            max-height: 60pt;
            margin-bottom: 12pt;
        }
        .company-info-header {
            margin-bottom: 8pt;
        }
        .company-name-header {
            font-size: 16pt;
            font-weight: bold;
            color: #333333;
            margin-bottom: 4pt;
        }
        .company-registration {
            font-size: 9pt;
            color: #666;
            margin-bottom: 2pt;
        }
        .invoice-status {
            display: inline-block;
            padding: 4pt 12pt;
            border-radius: 4pt;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 8pt;
        }
        .invoice-status.draft {
            background-color: #888;
            color: #FFF;
        }
        .invoice-status.paid {
            background-color: #28a745;
            color: #FFF;
        }
        .invoice-status.pending {
            background-color: #fd7e14;
            color: #FFF;
        }
        .invoice-status.overdue {
            background-color: #dc3545;
            color: #FFF;
        }
        .invoice-status.sent {
            background-color: #1A73E8;
            color: #FFF;
        }
        .invoice-meta {
            font-size: 11pt;
            color: #666;
            margin-bottom: 4pt;
        }
        .invoice-meta-label {
            color: #888;
            font-size: 10pt;
        }
        .invoice-meta-value {
            color: #333;
            font-weight: 600;
            font-size: 11pt;
        }
        
        /* Dates Section */
        .dates-section {
            margin: 25px 0;
            padding: 15px 20px;
            background-color: #F8F9FA;
            border: 1px solid #E0E0E0;
            border-radius: 4px;
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .dates-column {
            display: table-cell;
            vertical-align: top;
            padding-right: 30px;
        }
        .dates-column:last-child {
            padding-right: 0;
        }
        .date-label {
            font-size: 9pt;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-bottom: 4pt;
        }
        .date-value {
            font-size: 12pt;
            color: #333;
            font-weight: 600;
        }
        .po-number-section {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #E0E0E0;
        }
        
        /* Typography Hierarchy */
        .title-large {
            font-size: 24px;
            font-weight: bold;
            color: #1A73E8;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .body-text {
            font-size: 12px;
            color: #333333;
        }
        .label-text {
            font-size: 10px;
            color: #888888;
        }
        
        /* BILL FROM / BILL TO Sections */
        .billing-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #E0E0E0;
        }
        .billing-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0 0;
        }
        .billing-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 24px;
            border: 1px solid #E0E0E0;
            background-color: #FAFAFA;
            border-radius: 4px;
        }
        .billing-column:first-child {
            border-right: none;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .billing-column:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .billing-column-title {
            font-size: 14pt;
            font-weight: bold;
            color: #333333;
            text-transform: uppercase;
            margin-bottom: 16pt;
            letter-spacing: 0.5pt;
        }
        .billing-field {
            margin-bottom: 10px;
        }
        .billing-field:last-child {
            margin-bottom: 0;
        }
        .billing-label {
            font-size: 9pt;
            color: #888888;
            margin-bottom: 2pt;
        }
        .billing-value {
            font-size: 11pt;
            color: #333333;
            font-weight: normal;
        }
        .billing-value-bold {
            font-size: 11pt;
            color: #333333;
            font-weight: bold;
        }
        
        /* Invoice Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            page-break-inside: avoid;
            table-layout: fixed;
            border-top: 1px solid #E0E0E0;
        }
        .items-table td.text-right,
        .items-table th.text-right {
            white-space: nowrap;
        }
        .items-table thead {
            background-color: #1A73E8;
        }
        .items-table th {
            padding: 12pt 10pt;
            text-align: left;
            font-weight: bold;
            font-size: 12pt;
            color: #FFFFFF;
            border: none;
        }
        .items-table th.text-right {
            text-align: right;
        }
        .items-table th.text-center {
            text-align: center;
        }
        .items-table tbody tr {
            background-color: #FFFFFF;
            page-break-inside: avoid;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #F9F9F9;
        }
        .items-table td {
            padding: 12pt 10pt;
            border: 1px solid #E0E0E0;
            font-size: 11pt;
            color: #333333;
            word-wrap: break-word;
            vertical-align: top;
        }
        .items-table td.text-right {
            text-align: right;
        }
        .items-table td.text-center {
            text-align: center;
        }
        
        /* Totals Panel */
        .totals-panel {
            width: 320px;
            margin-left: auto;
            margin-top: 25px;
            margin-bottom: 30px;
            border: 2px solid #E0E0E0;
            background-color: #F8F9FA;
            padding: 24px;
            page-break-inside: avoid;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .totals-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 10px;
        }
        .totals-label {
            display: table-cell;
            font-size: 11pt;
            color: #333333;
            text-align: left;
            padding-right: 10pt;
        }
        .totals-value {
            display: table-cell;
            font-size: 11pt;
            color: #333333;
            text-align: right;
            font-weight: normal;
        }
        .totals-separator {
            border-top: 1px solid #E0E0E0;
            margin: 15px 0;
        }
        .grand-total-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-top: 15px;
            padding: 15px;
            border-top: 3px solid #1A73E8;
            background-color: #FFFFFF;
            border-radius: 4px;
        }
        .grand-total-label {
            display: table-cell;
            font-size: 18pt;
            font-weight: bold;
            color: #333333;
            text-align: left;
            padding-right: 10pt;
        }
        .grand-total-value {
            display: table-cell;
            font-size: 18pt;
            font-weight: bold;
            color: #1A73E8;
            text-align: right;
        }
        .amount-in-words {
            margin-top: 12pt;
            padding-top: 12pt;
            border-top: 1px solid #E0E0E0;
            font-size: 10pt;
            color: #555;
            font-style: italic;
            text-align: center;
        }
        
        /* Payment Instructions */
        .payment-instructions {
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid #E0E0E0;
            page-break-inside: avoid;
        }
        .payment-title {
            font-size: 13pt;
            font-weight: bold;
            color: #333333;
            margin-bottom: 12pt;
            text-transform: uppercase;
        }
        .payment-content {
            font-size: 11pt;
            color: #333333;
            line-height: 1.6;
        }
        .payment-content p {
            margin-bottom: 8px;
        }
        
        /* Notes Section */
        .notes-section {
            margin-top: 35px;
            padding: 20px;
            background-color: #FAFAFA;
            border: 1px solid #E0E0E0;
            page-break-inside: avoid;
            border-radius: 4px;
        }
        
        /* Terms & Conditions Section */
        .terms-section {
            margin-top: 35px;
            padding: 20px;
            background-color: #F8F9FA;
            border: 1px solid #E0E0E0;
            page-break-inside: avoid;
            border-radius: 4px;
        }
        .terms-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #333333;
            margin-bottom: 12px;
        }
        .terms-content {
            font-size: 11px;
            color: #555555;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        
        /* Footer Messages */
        .footer-messages {
            margin-top: 40pt;
            padding-top: 25pt;
            border-top: 1px solid #E0E0E0;
            text-align: center;
            page-break-inside: avoid;
        }
        .footer-message {
            font-size: 9pt;
            color: #888888;
            margin-bottom: 6pt;
            line-height: 1.5;
        }
        .footer-message:last-child {
            margin-bottom: 0;
        }
        
        /* Footer Metadata */
        .footer-metadata {
            margin-top: 20pt;
            padding-top: 15pt;
            border-top: 1px solid #E0E0E0;
            font-size: 8pt;
            color: #999999;
            text-align: center;
            page-break-inside: avoid;
        }
        .metadata-row {
            margin-bottom: 4pt;
        }
        .metadata-label {
            font-weight: 600;
            color: #777;
        }
        .metadata-value {
            color: #999;
        }
        .notes-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #333333;
            margin-bottom: 12px;
        }
        .notes-content {
            font-size: 12px;
            color: #333333;
            font-style: italic;
            line-height: 1.6;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="invoice-header-top">
                <div class="invoice-header-left">
                    @if(isset($invoice['company']['logo_path']) && $invoice['company']['logo_path'])
                        <img src="{{ $invoice['company']['logo_path'] }}" alt="{{ $invoice['company']['name'] ?? 'Company' }}" class="company-logo">
                    @endif
                    <div class="company-info-header">
                        <div class="company-name-header">{{ $invoice['company']['name'] ?? 'Company Name' }}</div>
                        @if(isset($invoice['company']['kra_pin']) && $invoice['company']['kra_pin'])
                            <div class="company-registration">KRA PIN: {{ $invoice['company']['kra_pin'] }}</div>
                        @endif
                        @if(isset($invoice['company']['registration_number']) && $invoice['company']['registration_number'])
                            <div class="company-registration">Reg. No: {{ $invoice['company']['registration_number'] }}</div>
                        @endif
                        @if(isset($invoice['company']['address']) && $invoice['company']['address'])
                            <div class="company-registration" style="margin-top: 4pt;">{{ explode("\n", $invoice['company']['address'])[0] }}</div>
                        @endif
                    </div>
                </div>
                <div class="invoice-header-right">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">{{ $invoice['invoice_number'] ?? 'INV-' . ($invoice['id'] ?? '') }}</div>
                    @if(isset($invoice['invoice_reference']) && $invoice['invoice_reference'] && $invoice['invoice_reference'] !== ($invoice['invoice_number'] ?? 'INV-' . ($invoice['id'] ?? '')))
                        <div class="invoice-reference">Ref: {{ $invoice['invoice_reference'] }}</div>
                    @endif
                    @if(isset($invoice['issue_date']) || isset($invoice['date']))
                        <div class="invoice-meta">
                            <span class="invoice-meta-label">Issue Date: </span>
                            <span class="invoice-meta-value">{{ $invoice['issue_date'] ?? $invoice['date'] ?? '' }}</span>
                        </div>
                    @endif
                    @if(isset($invoice['due_date']) && $invoice['due_date'])
                        <div class="invoice-meta">
                            <span class="invoice-meta-label">Due Date: </span>
                            <span class="invoice-meta-value">{{ $invoice['due_date'] }}</span>
                        </div>
                    @endif
                    <div class="invoice-status {{ strtolower($invoice['status'] ?? 'draft') }}">
                        {{ ucfirst($invoice['status'] ?? 'draft') }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- BILL FROM / BILL TO Sections -->
        <div class="billing-section">
            <div class="billing-grid">
                <!-- BILL FROM Column -->
                <div class="billing-column">
                    <div class="billing-column-title">Bill From</div>
                    @if(isset($invoice['company']['name']) && $invoice['company']['name'])
                        <div class="billing-field">
                            <div class="billing-value-bold">{{ $invoice['company']['name'] }}</div>
                        </div>
                    @endif
                    @if(isset($invoice['company']['address']) && $invoice['company']['address'])
                        <div class="billing-field">
                            <div class="billing-value">{{ $invoice['company']['address'] }}</div>
                        </div>
                    @endif
                    @if(isset($invoice['company']['phone']) && $invoice['company']['phone'])
                        <div class="billing-field">
                            <div class="billing-value">{{ $invoice['company']['phone'] }}</div>
                        </div>
                    @endif
                    @if(isset($invoice['company']['email']) && $invoice['company']['email'])
                        <div class="billing-field">
                            <div class="billing-value">{{ $invoice['company']['email'] }}</div>
                        </div>
                    @endif
                    @if(isset($invoice['company']['kra_pin']) && $invoice['company']['kra_pin'])
                        <div class="billing-field">
                            <div class="billing-label">KRA PIN:</div>
                            <div class="billing-value">{{ $invoice['company']['kra_pin'] }}</div>
                        </div>
                    @endif
                </div>
                
                <!-- BILL TO Column -->
                <div class="billing-column">
                    <div class="billing-column-title">Bill To</div>
                    @if(isset($invoice['client']['name']) && $invoice['client']['name'])
                        <div class="billing-field">
                            <div class="billing-value-bold">{{ $invoice['client']['name'] }}</div>
                        </div>
                    @endif
                    @if(isset($invoice['client']['email']) && $invoice['client']['email'])
                        <div class="billing-field">
                            <div class="billing-value">{{ $invoice['client']['email'] }}</div>
                        </div>
                    @endif
                    @if(isset($invoice['client']['phone']) && $invoice['client']['phone'])
                        <div class="billing-field">
                            <div class="billing-value">{{ $invoice['client']['phone'] }}</div>
                        </div>
                    @endif
                    @if(isset($invoice['client']['address']) && $invoice['client']['address'])
                        <div class="billing-field">
                            <div class="billing-value">{{ $invoice['client']['address'] }}</div>
                        </div>
                    @endif
                    @if(isset($invoice['client']['business_name']) && $invoice['client']['business_name'])
                        <div class="billing-field">
                            <div class="billing-label">Business Name:</div>
                            <div class="billing-value">{{ $invoice['client']['business_name'] }}</div>
                        </div>
                    @endif
                    @if(isset($invoice['client']['kra_pin']) && $invoice['client']['kra_pin'])
                        <div class="billing-field">
                            <div class="billing-label">Tax PIN:</div>
                            <div class="billing-value">{{ $invoice['client']['kra_pin'] }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Dates and PO Number Section -->
        <div class="dates-section">
            <div class="dates-column">
                @if(isset($invoice['issue_date']) || isset($invoice['date']))
                    <div class="date-label">Invoice Date</div>
                    <div class="date-value">{{ $invoice['issue_date'] ?? $invoice['date'] ?? '' }}</div>
                @endif
            </div>
            <div class="dates-column">
                @if(isset($invoice['due_date']) && $invoice['due_date'])
                    <div class="date-label">Due Date</div>
                    <div class="date-value">{{ $invoice['due_date'] }}</div>
                @endif
            </div>
            @if(isset($invoice['po_number']) && $invoice['po_number'])
                <div class="dates-column">
                    <div class="date-label">PO Number</div>
                    <div class="date-value">{{ $invoice['po_number'] }}</div>
                </div>
            @endif
        </div>

        <!-- Invoice Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 300px;">Description</th>
                    <th class="text-center" style="width: 80px;">Quantity</th>
                    <th class="text-right" style="width: 100px;">Unit Price</th>
                    @if((isset($invoice['vat_registered']) && $invoice['vat_registered']) || (isset($invoice['vat_amount']) && $invoice['vat_amount'] > 0))
                        <th class="text-right" style="width: 90px;">Tax</th>
                    @endif
                    @if(isset($invoice['discount']) && $invoice['discount'] > 0)
                        <th class="text-right" style="width: 90px;">Discount</th>
                    @endif
                    <th class="text-right" style="width: 110px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice['items'] ?? [] as $item)
                    <tr>
                        <td>{{ $item['description'] ?? 'Item' }}</td>
                        <td class="text-center">{{ number_format($item['quantity'] ?? 1, 0) }}</td>
                        <td class="text-right">{{ $invoice['company']['currency'] ?? 'KES' }} {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                        @if((isset($invoice['vat_registered']) && $invoice['vat_registered']) || (isset($invoice['vat_amount']) && $invoice['vat_amount'] > 0))
                            <td class="text-right">
                                @php
                                    $itemTotal = $item['total'] ?? $item['total_price'] ?? 0;
                                    $invoiceSubtotal = $invoice['subtotal'] ?? 1;
                                    $invoiceTax = $invoice['vat_amount'] ?? $invoice['tax'] ?? 0;
                                    $itemTax = $invoiceSubtotal > 0 ? ($itemTotal / $invoiceSubtotal) * $invoiceTax : 0;
                                @endphp
                                {{ $invoice['company']['currency'] ?? 'KES' }} {{ number_format($itemTax, 2) }}
                            </td>
                        @endif
                        @if(isset($invoice['discount']) && $invoice['discount'] > 0)
                            <td class="text-right">
                                @php
                                    // Discount per item pre-calculated in formatter (no calculations in view)
                                    $itemDiscount = $item['discount'] ?? 0;
                                @endphp
                                {{ $invoice['company']['currency'] ?? 'KES' }} {{ number_format($itemDiscount, 2) }}
                            </td>
                        @endif
                        <td class="text-right">{{ $invoice['company']['currency'] ?? 'KES' }} {{ number_format($item['total'] ?? $item['total_price'] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Panel -->
        <div class="totals-panel">
            <div class="totals-row">
                <span class="totals-label">Subtotal:</span>
                <span class="totals-value">{{ $invoice['company']['currency'] ?? 'KES' }} {{ number_format($invoice['subtotal'] ?? 0, 2) }}</span>
            </div>
            @if(isset($invoice['vat_amount']) && $invoice['vat_amount'] > 0)
                <div class="totals-row">
                    <span class="totals-label">VAT ({{ $invoice['tax_rate'] ?? 16 }}%):</span>
                    <span class="totals-value">{{ $invoice['company']['currency'] ?? 'KES' }} {{ number_format($invoice['vat_amount'] ?? $invoice['tax'] ?? 0, 2) }}</span>
                </div>
            @endif
            @if(isset($invoice['discount']) && $invoice['discount'] > 0)
                <div class="totals-row">
                    <span class="totals-label">Discount:</span>
                    <span class="totals-value">- {{ $invoice['company']['currency'] ?? 'KES' }} {{ number_format($invoice['discount'], 2) }}</span>
                </div>
            @endif
            @if(isset($invoice['platform_fee']) && $invoice['platform_fee'] > 0)
                <div class="totals-row">
                    <span class="totals-label">Platform Fee (3%):</span>
                    <span class="totals-value">{{ $invoice['company']['currency'] ?? 'KES' }} {{ number_format($invoice['platform_fee'], 2) }}</span>
                </div>
            @endif
            <div class="totals-separator"></div>
            <div class="grand-total-row">
                <span class="grand-total-label">Total:</span>
                <span class="grand-total-value">{{ $invoice['company']['currency'] ?? 'KES' }} {{ number_format($invoice['grand_total'] ?? $invoice['total'] ?? 0, 2) }}</span>
            </div>
            @if(isset($invoice['amount_in_words']) && $invoice['amount_in_words'])
                <div class="amount-in-words">
                    {{ $invoice['amount_in_words'] }}
                </div>
            @endif
        </div>

        <!-- Payment Instructions Section -->
        <div class="payment-instructions">
            <div class="payment-title">Payment Instructions</div>
            <div class="payment-content">
                <p><strong>Make all payments to {{ $invoice['company']['name'] ?? 'InvoiceHub' }}</strong></p>
                @if(isset($invoice['company']['phone']) && $invoice['company']['phone'])
                    <p><strong>M-Pesa Paybill / Till Number:</strong> {{ $invoice['company']['phone'] }}</p>
                @endif
                @if(isset($invoice['payment_method']) && $invoice['payment_method'])
                    <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $invoice['payment_method'])) }}</p>
                @endif
                @if(isset($invoice['payment_details']) && $invoice['payment_details'])
                    <p>{{ $invoice['payment_details'] }}</p>
                @endif
                @if(isset($invoice['payment_terms']) && $invoice['payment_terms'])
                    <p><strong>Payment Terms:</strong> {{ $invoice['payment_terms'] }}</p>
                @endif
            </div>
        </div>

        <!-- Notes Section -->
        @if(isset($invoice['notes']) && $invoice['notes'])
            <div class="notes-section">
                <div class="notes-title">Additional Notes</div>
                <div class="notes-content">{{ $invoice['notes'] }}</div>
            </div>
        @endif

        <!-- Terms & Conditions Section -->
        @if(isset($invoice['terms_and_conditions']) && $invoice['terms_and_conditions'])
            <div class="terms-section">
                <div class="terms-title">Terms & Conditions</div>
                <div class="terms-content">{{ $invoice['terms_and_conditions'] }}</div>
            </div>
        @endif

        <!-- Footer Messages -->
        <div class="footer-messages">
            <div class="footer-message">Thank you for your business!</div>
            <div class="footer-message">This is a computer-generated Invoice. No signature required.</div>
        </div>

        <!-- Footer Metadata -->
        <div class="footer-metadata">
            @if(isset($invoice['uuid']) && $invoice['uuid'])
                <div class="metadata-row">
                    <span class="metadata-label">Invoice UUID:</span>
                    <span class="metadata-value"> {{ $invoice['uuid'] }}</span>
                </div>
            @endif
            @if(isset($invoice['company']['kra_pin']) && $invoice['company']['kra_pin'])
                <div class="metadata-row">
                    <span class="metadata-label">Merchant VAT PIN:</span>
                    <span class="metadata-value"> {{ $invoice['company']['kra_pin'] }}</span>
                </div>
            @endif
            @if(isset($invoice['company']['registration_number']) && $invoice['company']['registration_number'])
                <div class="metadata-row">
                    <span class="metadata-label">Registration Number:</span>
                    <span class="metadata-value"> {{ $invoice['company']['registration_number'] }}</span>
                </div>
            @endif
            @if(isset($invoice['generated_at']) && $invoice['generated_at'])
                <div class="metadata-row">
                    <span class="metadata-label">Generated:</span>
                    <span class="metadata-value"> {{ \Carbon\Carbon::parse($invoice['generated_at'])->format('Y-m-d H:i:s') }}</span>
                </div>
            @endif
            <div class="metadata-row">
                <span class="metadata-label">Page:</span>
                <span class="metadata-value"> <script type="text/php">echo $PAGE_NUM . ' of ' . $PAGE_COUNT;</script></span>
            </div>
        </div>
    </div>
</body>
</html>
