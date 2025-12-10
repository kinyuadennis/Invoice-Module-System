<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Preview</title>
    <style>
        :root {
            --primary-color: {{ $branding['primary_color'] ?? '#2B6EF6' }};
            --secondary-color: {{ $branding['secondary_color'] ?? '#7C3AED' }};
            --font-family: {{ $branding['font_family'] ?? 'Inter' }}, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --border-color: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --bg-light: #f9fafb;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: var(--font-family);
            font-size: 14px;
            color: var(--text-primary);
            line-height: 1.6;
            background: white;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 50px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .logo-img {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }
        .company-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 4px;
        }
        .company-details {
            font-size: 12px;
            color: var(--text-secondary);
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            background: var(--bg-light);
            color: var(--text-secondary);
        }
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .section-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        .info-block {
            line-height: 1.8;
        }
        .info-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 15px;
        }
        .info-detail {
            color: var(--text-secondary);
            font-size: 13px;
        }
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 8px;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
        }
        .detail-label {
            color: var(--text-secondary);
            font-weight: 500;
        }
        .detail-value {
            color: var(--text-primary);
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        thead {
            background: var(--bg-light);
        }
        th {
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
        }
        th.text-right {
            text-align: right;
        }
        th.text-center {
            text-align: center;
        }
        td {
            padding: 16px 12px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        td.text-right {
            text-align: right;
        }
        td.text-center {
            text-align: center;
        }
        .totals {
            margin-left: auto;
            width: 350px;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .total-row:last-child {
            border-bottom: 2px solid var(--primary-color);
            font-weight: 700;
            font-size: 18px;
            color: var(--primary-color);
            margin-top: 8px;
            padding-top: 16px;
        }
        .total-label {
            color: var(--text-secondary);
            font-weight: 500;
        }
        .total-value {
            color: var(--text-primary);
            font-weight: 600;
        }
        .notes-section {
            margin-top: 40px;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 8px;
        }
        .notes-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .notes-content {
            color: var(--text-primary);
            white-space: pre-wrap;
            line-height: 1.8;
        }
        .footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-secondary);
            font-size: 11px;
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
                <div class="invoice-number">{{ $invoice['invoice_number'] ?? 'INV-0001' }}</div>
                <span class="status-badge">{{ ucfirst($invoice['status'] ?? 'Sent') }}</span>
            </div>
        </div>

        <!-- Bill To / From -->
        <div class="two-columns">
            <div>
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
            <div>
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
            <div class="detail-item">
                <span class="detail-label">Issue Date:</span>
                <span class="detail-value">{{ $invoice['issue_date'] ?? $invoice['date'] ?? date('Y-m-d') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Due Date:</span>
                <span class="detail-value">{{ $invoice['due_date'] ?? 'N/A' }}</span>
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
            <div style="margin-top: 8px;">This is a computer-generated invoice. No signature required.</div>
        </div>
    </div>
</body>
</html>

