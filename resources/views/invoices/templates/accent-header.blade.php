<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice['invoice_number'] ?? 'INV-' . $invoice['id'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .container { max-width: 800px; margin: 0 auto; padding: 0; }
        .header {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: white;
            padding: 40px;
            margin-bottom: 30px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-number {
            font-size: 24px;
            font-weight: bold;
            text-align: right;
        }
        .content { padding: 0 40px 40px; }
        .two-columns {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .column { flex: 1; }
        .column:first-child { margin-right: 30px; }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead { background-color: #3B82F6; color: white; }
        th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
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
            border-top: 3px solid #3B82F6;
            font-weight: bold;
            font-size: 16px;
            color: #3B82F6;
            margin-top: 10px;
            padding-top: 15px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div>
                    <div class="company-name">{{ $invoice['company']['name'] ?? 'Company Name' }}</div>
                    @if(isset($invoice['company']['kra_pin']))
                        <div style="font-size: 11px; opacity: 0.9;">KRA PIN: {{ $invoice['company']['kra_pin'] }}</div>
                    @endif
                </div>
                <div class="invoice-number">{{ $invoice['invoice_number'] ?? 'INV-' . $invoice['id'] }}</div>
            </div>
        </div>

        <div class="content">
            <div class="two-columns">
                <div class="column">
                    <div class="section-title">Bill To</div>
                    <div style="font-weight: bold; margin-bottom: 5px;">{{ $invoice['client']['name'] ?? 'Client Name' }}</div>
                    @if(isset($invoice['client']['address']))
                        <div style="color: #666;">{{ $invoice['client']['address'] }}</div>
                    @endif
                </div>
                <div class="column">
                    <div class="section-title">Invoice Details</div>
                    <div>Issue Date: {{ $invoice['date'] ?? date('Y-m-d') }}</div>
                    <div>Due Date: {{ $invoice['due_date'] ?? 'N/A' }}</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
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

            <div class="totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>KES {{ number_format($invoice['subtotal'] ?? 0, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>VAT (16%):</span>
                    <span>KES {{ number_format($invoice['vat_amount'] ?? $invoice['tax'] ?? 0, 2) }}</span>
                </div>
                @if(isset($invoice['platform_fee']) && $invoice['platform_fee'] > 0)
                    <div class="total-row">
                        <span>Platform Fee (0.8%):</span>
                        <span>KES {{ number_format($invoice['platform_fee'], 2) }}</span>
                    </div>
                @endif
                <div class="total-row">
                    <span>Total:</span>
                    <span>KES {{ number_format($invoice['grand_total'] ?? $invoice['total'] ?? 0, 2) }}</span>
                </div>
            </div>

            @if(isset($invoice['notes']) && $invoice['notes'])
                <div style="margin-top: 30px;">
                    <div class="section-title">Notes</div>
                    <div style="color: #666;">{{ $invoice['notes'] }}</div>
                </div>
            @endif

            <div class="footer">
                <div>Thank you for your business!</div>
                <div style="margin-top: 5px;">{{ $invoice['company']['name'] ?? 'Company' }}</div>
            </div>
        </div>
    </div>
</body>
</html>

