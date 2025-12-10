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
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --bg-accent: {{ $branding['primary_color'] ?? '#2B6EF6' }};
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
        }
        .header-bar {
            background: var(--bg-accent);
            color: white;
            padding: 30px 40px;
            margin-bottom: 40px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            background: white;
            padding: 8px;
            border-radius: 8px;
        }
        .company-name {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 6px;
        }
        .company-details {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-label {
            font-size: 11px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 6px;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .invoice-number {
            font-size: 26px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .content {
            padding: 0 40px 40px;
        }
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--bg-accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--bg-accent);
        }
        .info-block {
            line-height: 1.9;
        }
        .info-name {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
            font-size: 16px;
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
            padding: 24px;
            background: #f9fafb;
            border-radius: 12px;
            border-left: 4px solid var(--bg-accent);
        }
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--text-secondary);
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .detail-value {
            color: var(--text-primary);
            font-weight: 700;
            font-size: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        thead {
            background: var(--bg-accent);
        }
        th {
            padding: 16px 14px;
            text-align: left;
            font-weight: 700;
            color: white;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        th.text-right {
            text-align: right;
        }
        th.text-center {
            text-align: center;
        }
        td {
            padding: 16px 14px;
            border-bottom: 1px solid #e5e7eb;
            color: var(--text-primary);
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
            width: 360px;
            margin-top: 20px;
            padding: 24px;
            background: #f9fafb;
            border-radius: 12px;
            border: 2px solid var(--bg-accent);
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .total-row:last-child {
            border-bottom: 3px solid var(--bg-accent);
            font-weight: 700;
            font-size: 20px;
            color: var(--bg-accent);
            margin-top: 12px;
            padding-top: 16px;
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
            margin-top: 40px;
            padding: 24px;
            background: #f9fafb;
            border-radius: 12px;
            border-left: 4px solid var(--bg-accent);
        }
        .notes-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--bg-accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .notes-content {
            color: var(--text-primary);
            white-space: pre-wrap;
            line-height: 1.8;
        }
        .footer {
            margin-top: 50px;
            padding: 30px 40px;
            background: #f9fafb;
            text-align: center;
            color: var(--text-secondary);
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header Bar -->
        <div class="header-bar">
            <div class="header-content">
                <div class="logo-section">
                    @if(isset($invoice['company']['logo']) && $invoice['company']['logo'])
                        <img src="{{ $invoice['company']['logo'] }}" alt="{{ $invoice['company']['name'] ?? 'Company' }}" class="logo-img">
                    @endif
                    <div>
                        <div class="company-name">{{ $invoice['company']['name'] ?? 'Your Company Name' }}</div>
                        @if(isset($invoice['company']['kra_pin']) && $invoice['company']['kra_pin'])
                            <div class="company-details">KRA PIN: {{ $invoice['company']['kra_pin'] }}</div>
                        @endif
                    </div>
                </div>
                <div class="invoice-info">
                    <div class="invoice-label">Invoice Number</div>
                    <div class="invoice-number">{{ $invoice['invoice_number'] ?? 'INV-0001' }}</div>
                    <span class="status-badge">{{ ucfirst($invoice['status'] ?? 'Sent') }}</span>
                </div>
            </div>
        </div>

        <div class="content">
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
                        @if(isset($invoice['company']['address']) && $invoice['company']['address'])
                            <div class="info-detail">{{ $invoice['company']['address'] }}</div>
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
                    <div class="detail-label">Issue Date</div>
                    <div class="detail-value">{{ $invoice['issue_date'] ?? $invoice['date'] ?? date('Y-m-d') }}</div>
                </div>
                <div class="detail-item">
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
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            <div style="margin-top: 8px;">This is a computer-generated invoice. No signature required.</div>
        </div>
    </div>
</body>
</html>

