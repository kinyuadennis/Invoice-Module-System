<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice['invoice_number'] ?? 'INV-' . $invoice['id'] }}</title>
    <style>
        @page {
            margin: 160px 43px 130px 43px; /* Adjusted for new header/footer heights */
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.5;
        }
        .container {
            max-width: 100%;
            margin: 0;
            padding: 24px 0;
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
            margin-bottom: 30px;
        }
        .billing-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
        }
        .billing-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 20px;
            border: 1px solid #E0E0E0;
            background-color: #FFFFFF;
        }
        .billing-column:first-child {
            border-right: none;
        }
        .billing-column-title {
            font-size: 16px;
            font-weight: bold;
            color: #333333;
            text-transform: uppercase;
            margin-bottom: 16px;
            letter-spacing: 0.5px;
        }
        .billing-field {
            margin-bottom: 8px;
        }
        .billing-label {
            font-size: 10px;
            color: #888888;
            margin-bottom: 2px;
        }
        .billing-value {
            font-size: 12px;
            color: #333333;
            font-weight: normal;
        }
        .billing-value-bold {
            font-size: 12px;
            color: #333333;
            font-weight: bold;
        }
        
        /* Invoice Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            page-break-inside: avoid;
            table-layout: fixed;
        }
        .items-table thead {
            background-color: #1A73E8;
        }
        .items-table th {
            padding: 12px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 13px;
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
            padding: 10px;
            border: 1px solid #E0E0E0;
            font-size: 12px;
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
            width: 300px;
            margin-left: auto;
            margin-top: 25px;
            margin-bottom: 30px;
            border: 1px solid #E0E0E0;
            background-color: #FAFAFA;
            padding: 20px;
            page-break-inside: avoid;
        }
        .totals-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 10px;
        }
        .totals-label {
            display: table-cell;
            font-size: 12px;
            color: #333333;
            text-align: left;
            padding-right: 10px;
        }
        .totals-value {
            display: table-cell;
            font-size: 12px;
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
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #E0E0E0;
        }
        .grand-total-label {
            display: table-cell;
            font-size: 20px;
            font-weight: bold;
            color: #333333;
            text-align: left;
            padding-right: 10px;
        }
        .grand-total-value {
            display: table-cell;
            font-size: 20px;
            font-weight: bold;
            color: #1A73E8;
            text-align: right;
        }
        
        /* Payment Instructions */
        .payment-instructions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E0E0E0;
            page-break-inside: avoid;
        }
        .payment-title {
            font-size: 14px;
            font-weight: bold;
            color: #333333;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .payment-content {
            font-size: 12px;
            color: #333333;
            line-height: 1.6;
        }
        .payment-content p {
            margin-bottom: 8px;
        }
        
        /* Notes Section */
        .notes-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #FAFAFA;
            border: 1px solid #E0E0E0;
            page-break-inside: avoid;
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
    @include('pdf.partials.header')
    @include('pdf.partials.footer')
    
    <div class="container">
        
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

        <!-- Invoice Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Description</th>
                    <th class="text-center" style="width: 10%;">Quantity</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    @if((isset($invoice['vat_registered']) && $invoice['vat_registered']) || (isset($invoice['vat_amount']) && $invoice['vat_amount'] > 0))
                        <th class="text-right" style="width: 10%;">Tax</th>
                    @endif
                    @if(isset($invoice['discount']) && $invoice['discount'] > 0)
                        <th class="text-right" style="width: 10%;">Discount</th>
                    @endif
                    <th class="text-right" style="width: 15%;">Total</th>
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
                                    $itemDiscount = 0;
                                    $itemTotal = $item['total'] ?? $item['total_price'] ?? 0;
                                    if (isset($invoice['discount_type']) && $invoice['discount_type'] === 'percentage') {
                                        $itemDiscount = ($invoice['discount'] / 100) * $itemTotal;
                                    } else {
                                        $itemCount = count($invoice['items'] ?? [1]);
                                        $itemDiscount = $itemCount > 0 ? ($invoice['discount'] / $itemCount) : 0;
                                    }
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
            </div>
        </div>

        <!-- Notes Section -->
        @if(isset($invoice['notes']) && $invoice['notes'])
            <div class="notes-section">
                <div class="notes-title">Additional Notes</div>
                <div class="notes-content">{{ $invoice['notes'] }}</div>
            </div>
        @endif
    </div>
</body>
</html>
