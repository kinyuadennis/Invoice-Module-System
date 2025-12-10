<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Preview</title>
    <style>
        :root {
            --primary-color: {{ $branding['primary_color'] ?? '#1F2937' }};
            --secondary-color: {{ $branding['secondary_color'] ?? '#4B5563' }};
            --font-family: {{ $branding['font_family'] ?? 'Inter' }}, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --border-color: #d1d5db;
            --text-primary: #111827;
            --text-secondary: #4B5563;
            --bg-header: #f3f4f6;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: var(--font-family);
            font-size: 13px;
            color: var(--text-primary);
            line-height: 1.6;
            background: white;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border: 2px solid var(--border-color);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid var(--primary-color);
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .logo-img {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
            border: 1px solid var(--border-color);
            padding: 4px;
        }
        .company-name {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 6px;
        }
        .company-details {
            font-size: 11px;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        .invoice-info {
            text-align: right;
            border: 2px solid var(--primary-color);
            padding: 16px 20px;
            background: var(--bg-header);
        }
        .invoice-label {
            font-size: 10px;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 4px;
            font-weight: 600;
        }
        .invoice-number {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            background: var(--primary-color);
            color: white;
        }
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .section-box {
            border: 1px solid var(--border-color);
            padding: 16px;
            background: var(--bg-header);
        }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-color);
            text-transform: uppercase;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--primary-color);
        }
        .info-block {
            line-height: 1.8;
        }
        .info-name {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-detail {
            color: var(--text-secondary);
            font-size: 12px;
        }
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 30px;
        }
        .detail-box {
            border: 1px solid var(--border-color);
            padding: 12px;
            background: white;
        }
        .detail-label {
            font-size: 10px;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 4px;
            font-weight: 600;
        }
        .detail-value {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }
        thead {
            background: var(--primary-color);
        }
        th {
            padding: 14px 12px;
            text-align: left;
            font-weight: 700;
            color: white;
            font-size: 11px;
            text-transform: uppercase;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }
        th:last-child {
            border-right: none;
        }
        th.text-right {
            text-align: right;
        }
        th.text-center {
            text-align: center;
        }
        td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        td:last-child {
            border-right: none;
        }
        td.text-right {
            text-align: right;
        }
        td.text-center {
            text-align: center;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .totals {
            margin-left: auto;
            width: 320px;
            border: 1px solid var(--border-color);
            padding: 16px;
            background: var(--bg-header);
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .total-row:last-child {
            border-bottom: 2px solid var(--primary-color);
            font-weight: 700;
            font-size: 16px;
            color: var(--primary-color);
            margin-top: 8px;
            padding-top: 12px;
        }
        .total-label {
            color: var(--text-secondary);
            font-weight: 600;
        }
        .total-value {
            color: var(--text-primary);
            font-weight: 700;
        }
        .notes-section {
            margin-top: 30px;
            border: 1px solid var(--border-color);
            padding: 16px;
            background: var(--bg-header);
        }
        .notes-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-color);
            text-transform: uppercase;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border-color);
        }
        .notes-content {
            color: var(--text-primary);
            white-space: pre-wrap;
            line-height: 1.8;
            font-size: 12px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid var(--border-color);
            text-align: center;
            color: var(--text-secondary);
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                @if(isset($invoice['company']['logo']) && $invoice['company']['logo'])
                    <img src="{{ $invoice['company']['logo'] }}" alt="{{ $invoice['company']['name'] ?? 'Company' }}" class="logo-img">
                @endif
                <div>
                    <div class="company-name">{{ $invoice['company']['name'] ?? 'Your Company Name' }}</div>
                    @if(isset($invoice['company']['kra_pin']) && $invoice['company']['kra_pin'])
                        <div class="company-details">KRA PIN: {{ $invoice['company']['kra_pin'] }}</div>
                    @endif
                    @if(isset($invoice['company']['address']) && $invoice['company']['address'])
                        <div class="company-details">{{ $invoice['company']['address'] }}</div>
                    @endif
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-label">Invoice Number</div>
                <div class="invoice-number">{{ $invoice['invoice_number'] ?? 'INV-0001' }}</div>
                <span class="status-badge">{{ ucfirst($invoice['status'] ?? 'Sent') }}</span>
            </div>
        </div>

        <!-- Bill To / From -->
        <div class="two-columns">
            <div class="section-box">
                <div class="section-title">Bill From</div>
                <div class="info-block">
                    <div class="info-name">{{ $invoice['company']['name'] ?? 'Your Company Name' }}</div>
                    @if(isset($invoice['company']['email']) && $invoice['company']['email'])
                        <div class="info-detail">{{ $invoice['company']['email'] }}</div>
                    @endif
                    @if(isset($invoice['company']['phone']) && $invoice['company']['phone'])
                        <div class="info-detail">{{ $invoice['company']['phone'] }}</div>
                    @endif
                </div>
            </div>
            <div class="section-box">
                <div class="section-title">Bill To</div>
                <div class="info-block">
                    <div class="info-name">{{ $invoice['client']['name'] ?? 'Client Name' }}</div>
                    @if(isset($invoice['client']['email']) && $invoice['client']['email'])
                        <div class="info-detail">{{ $invoice['client']['email'] }}</div>
                    @endif
                    @if(isset($invoice['client']['phone']) && $invoice['client']['phone'])
                        <div class="info-detail">{{ $invoice['client']['phone'] }}</div>
                    @endif
                    @if(isset($invoice['client']['address']) && $invoice['client']['address'])
                        <div class="info-detail">{{ $invoice['client']['address'] }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="detail-box">
                <div class="detail-label">Issue Date</div>
                <div class="detail-value">{{ $invoice['issue_date'] ?? $invoice['date'] ?? date('Y-m-d') }}</div>
            </div>
            <div class="detail-box">
                <div class="detail-label">Due Date</div>
                <div class="detail-value">{{ $invoice['due_date'] ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Line Items -->
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
                        <td class="text-right">KES {{ number_format($item['total_price'] ?? $item['total'] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">KES {{ number_format($invoice['subtotal'] ?? 0, 2) }}</span>
            </div>
            @if(isset($invoice['vat_amount']) && $invoice['vat_amount'] > 0)
                <div class="total-row">
                    <span class="total-label">VAT (16%):</span>
                    <span class="total-value">KES {{ number_format($invoice['vat_amount'] ?? 0, 2) }}</span>
                </div>
            @endif
            @if(isset($invoice['platform_fee']) && $invoice['platform_fee'] > 0)
                <div class="total-row">
                    <span class="total-label">Platform Fee:</span>
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
            <div class="notes-section">
                <div class="notes-title">Notes</div>
                <div class="notes-content">{{ $invoice['notes'] }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            <div style="margin-top: 6px;">This is a computer-generated invoice. No signature required.</div>
        </div>
    </div>
</body>
</html>

