<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice['invoice_number'] ?? 'INV-' . $invoice['id'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo-img {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #059669;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .two-columns {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .column {
            flex: 1;
        }
        .column:first-child {
            margin-right: 20px;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #6b7280;
            display: inline-block;
            width: 100px;
        }
        .info-value {
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead {
            background-color: #f3f4f6;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            margin-top: 20px;
            margin-left: auto;
            width: 300px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .total-row:last-child {
            border-bottom: 2px solid #059669;
            font-weight: bold;
            font-size: 16px;
            color: #059669;
            margin-top: 10px;
            padding-top: 15px;
        }
        .total-label {
            color: #6b7280;
        }
        .total-value {
            color: #111827;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        .status-sent {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                @if(isset($invoice['company']['logo']) && $invoice['company']['logo'])
                    <img src="{{ public_path('storage/' . $invoice['company']['logo']) }}" alt="{{ $invoice['company']['name'] ?? 'Company' }}" class="logo-img">
                @endif
                <div>
                    <div class="logo-text">{{ $invoice['company']['name'] ?? 'Invoice Hub' }}</div>
                    @if(isset($invoice['company']['kra_pin']) && $invoice['company']['kra_pin'])
                        <div style="font-size: 10px; color: #6b7280; margin-top: 4px;">KRA PIN: {{ $invoice['company']['kra_pin'] }}</div>
                    @endif
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-number">{{ $invoice['invoice_number'] ?? 'INV-' . $invoice['id'] }}</div>
                <div class="info-row">
                    <span class="status-badge status-{{ strtolower($invoice['status'] ?? 'draft') }}">
                        {{ ucfirst($invoice['status'] ?? 'Draft') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Bill To / From -->
        <div class="two-columns">
            <div class="column">
                <div class="section-title">Bill From</div>
                <div class="info-row">
                    <div class="info-value" style="font-weight: bold; margin-bottom: 5px;">
                        {{ $invoice['company']['name'] ?? 'Your Business Name' }}
                    </div>
                    @if(isset($invoice['company']['email']) && $invoice['company']['email'])
                        <div class="info-value" style="color: #6b7280;">
                            {{ $invoice['company']['email'] }}
                        </div>
                    @endif
                    @if(isset($invoice['company']['phone']) && $invoice['company']['phone'])
                        <div class="info-value" style="color: #6b7280;">
                            {{ $invoice['company']['phone'] }}
                        </div>
                    @endif
                    @if(isset($invoice['company']['address']) && $invoice['company']['address'])
                        <div class="info-value" style="color: #6b7280; margin-top: 5px;">
                            {{ $invoice['company']['address'] }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="column">
                <div class="section-title">Bill To</div>
                <div class="info-row">
                    <div class="info-value" style="font-weight: bold; margin-bottom: 5px;">
                        {{ $invoice['client']['name'] ?? 'Client Name' }}
                    </div>
                    @if(isset($invoice['client']['email']) && $invoice['client']['email'])
                        <div class="info-value" style="color: #6b7280;">
                            {{ $invoice['client']['email'] }}
                        </div>
                    @endif
                    @if(isset($invoice['client']['phone']) && $invoice['client']['phone'])
                        <div class="info-value" style="color: #6b7280;">
                            {{ $invoice['client']['phone'] }}
                        </div>
                    @endif
                    @if(isset($invoice['client']['address']) && $invoice['client']['address'])
                        <div class="info-value" style="color: #6b7280; margin-top: 5px;">
                            {{ $invoice['client']['address'] }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="section">
            <div class="two-columns">
                <div class="column">
                    <div class="info-row">
                        <span class="info-label">Issue Date:</span>
                        <span class="info-value">{{ $invoice['date'] ?? date('Y-m-d') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Due Date:</span>
                        <span class="info-value">{{ $invoice['due_date'] ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="column">
                    @if(isset($invoice['payment_method']) && $invoice['payment_method'])
                        <div class="info-row">
                            <span class="info-label">Payment Method:</span>
                            <span class="info-value">{{ ucfirst(str_replace('_', ' ', $invoice['payment_method'])) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice['items'] ?? [] as $item)
                        <tr>
                            <td>{{ $item['description'] ?? 'Item' }}</td>
                            <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                            <td class="text-right">KES {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                            <td class="text-right">KES {{ number_format($item['total'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">KES {{ number_format($invoice['subtotal'] ?? 0, 2) }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">VAT (16%):</span>
                <span class="total-value">KES {{ number_format($invoice['vat_amount'] ?? $invoice['tax'] ?? 0, 2) }}</span>
            </div>
            @if(isset($invoice['platform_fee']) && $invoice['platform_fee'] > 0)
                <div class="total-row">
                    <span class="total-label">Platform Fee (3%):</span>
                    <span class="total-value">KES {{ number_format($invoice['platform_fee'], 2) }}</span>
                </div>
            @endif
            <div class="total-row">
                <span class="total-label">Total:</span>
                <span class="total-value">KES {{ number_format($invoice['grand_total'] ?? $invoice['total'] ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Notes -->
        @if(isset($invoice['notes']) && $invoice['notes'])
            <div class="section">
                <div class="section-title">Notes</div>
                <div style="color: #6b7280; white-space: pre-wrap;">{{ $invoice['notes'] }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            <div style="margin-top: 5px;">This is a computer-generated invoice. No signature required.</div>
            @if(isset($invoice['company']['name']))
                <div style="margin-top: 5px;">{{ $invoice['company']['name'] }}</div>
            @endif
        </div>
    </div>
</body>
</html>
