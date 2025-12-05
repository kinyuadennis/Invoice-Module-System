<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice['invoice_number'] ?? 'INV-' . $invoice['id'] }}</title>
    
    @if(isset($template) && $template->css_file)
        <link rel="stylesheet" href="{{ asset("css/invoice-templates/{$template->css_file}") }}">
    @endif
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #2d3748;
            line-height: 1.7;
            background-color: #f7fafc;
        }
        .container { max-width: 800px; margin: 0 auto; padding: 40px; background: white; }
        .header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 20px;
            font-weight: 300;
            color: #2d3748;
            letter-spacing: 1px;
        }
        .invoice-number {
            font-size: 14px;
            color: #718096;
            margin-top: 5px;
        }
        .two-columns {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .column { flex: 1; }
        .column:first-child { margin-right: 30px; }
        .label {
            font-size: 10px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .value {
            color: #2d3748;
            font-weight: 400;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            padding: 10px 0;
            text-align: left;
            font-weight: 400;
            color: #718096;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 12px 0;
            border-bottom: 1px solid #f7fafc;
            color: #2d3748;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals {
            margin-top: 30px;
            margin-left: auto;
            width: 250px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f7fafc;
        }
        .total-row:last-child {
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 500;
            font-size: 14px;
            margin-top: 10px;
            padding-top: 15px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #a0aec0;
            font-size: 9px;
        }
    </style>
</head>
<body class="{{ isset($template) && $template->layout_class ? $template->layout_class : 'template-minimalist' }}">
    <div class="container">
        <div class="header">
            <div>
                @if(isset($invoice['company']['logo']) && $invoice['company']['logo'])
                    <img src="{{ public_path('storage/' . $invoice['company']['logo']) }}" alt="{{ $invoice['company']['name'] ?? 'Company' }}" style="max-width: 80px; max-height: 80px; margin-bottom: 10px;">
                @endif
                <div class="company-name">{{ $invoice['company']['name'] ?? 'Company Name' }}</div>
                @if(isset($invoice['company']['kra_pin']) && $invoice['company']['kra_pin'])
                    <div style="font-size: 10px; color: #718096; margin-top: 4px;">KRA PIN: {{ $invoice['company']['kra_pin'] }}</div>
                @endif
                @if(isset($invoice['company']['address']) && $invoice['company']['address'])
                    <div style="font-size: 10px; color: #718096; margin-top: 4px;">{{ $invoice['company']['address'] }}</div>
                @endif
                @if(isset($invoice['company']['phone']) && $invoice['company']['phone'])
                    <div style="font-size: 10px; color: #718096; margin-top: 2px;">{{ $invoice['company']['phone'] }}</div>
                @endif
                @if(isset($invoice['company']['email']) && $invoice['company']['email'])
                    <div style="font-size: 10px; color: #718096; margin-top: 2px;">{{ $invoice['company']['email'] }}</div>
                @endif
            </div>
            <div>
                <div class="invoice-number">{{ $invoice['invoice_number'] ?? 'INV-' . $invoice['id'] }}</div>
                <div class="label" style="margin-top: 10px;">Date</div>
                <div class="value">{{ $invoice['date'] ?? date('Y-m-d') }}</div>
                <div class="label" style="margin-top: 10px;">Due Date</div>
                <div class="value">{{ $invoice['due_date'] ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="two-columns">
            <div class="column">
                <div class="label">Bill From</div>
                <div class="value" style="font-weight: 500; margin-bottom: 5px;">{{ $invoice['company']['name'] ?? 'Company Name' }}</div>
                @if(isset($invoice['company']['address']) && $invoice['company']['address'])
                    <div class="value" style="color: #718096; font-size: 10px;">{{ $invoice['company']['address'] }}</div>
                @endif
                @if(isset($invoice['company']['phone']) && $invoice['company']['phone'])
                    <div class="value" style="color: #718096; font-size: 10px;">{{ $invoice['company']['phone'] }}</div>
                @endif
                @if(isset($invoice['company']['email']) && $invoice['company']['email'])
                    <div class="value" style="color: #718096; font-size: 10px;">{{ $invoice['company']['email'] }}</div>
                @endif
                @if(isset($invoice['company']['kra_pin']) && $invoice['company']['kra_pin'])
                    <div class="value" style="color: #718096; font-size: 10px;">KRA PIN: {{ $invoice['company']['kra_pin'] }}</div>
                @endif
            </div>
            <div class="column">
                <div class="label">Bill To</div>
                <div class="value" style="font-weight: 500; margin-bottom: 5px;">{{ $invoice['client']['name'] ?? 'Client Name' }}</div>
                @if(isset($invoice['client']['address']) && $invoice['client']['address'])
                    <div class="value" style="color: #718096; font-size: 10px;">{{ $invoice['client']['address'] }}</div>
                @endif
                @if(isset($invoice['client']['phone']) && $invoice['client']['phone'])
                    <div class="value" style="color: #718096; font-size: 10px;">{{ $invoice['client']['phone'] }}</div>
                @endif
                @if(isset($invoice['client']['email']) && $invoice['client']['email'])
                    <div class="value" style="color: #718096; font-size: 10px;">{{ $invoice['client']['email'] }}</div>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Price</th>
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
                <span>Subtotal</span>
                <span>KES {{ number_format($invoice['subtotal'] ?? 0, 2) }}</span>
            </div>
            <div class="total-row">
                <span>VAT (16%)</span>
                <span>KES {{ number_format($invoice['vat_amount'] ?? $invoice['tax'] ?? 0, 2) }}</span>
            </div>
            @if(isset($invoice['platform_fee']) && $invoice['platform_fee'] > 0)
                <div class="total-row">
                    <span>Platform Fee (3%)</span>
                    <span>KES {{ number_format($invoice['platform_fee'], 2) }}</span>
                </div>
            @endif
            <div class="total-row">
                <span>Total</span>
                <span>KES {{ number_format($invoice['grand_total'] ?? $invoice['total'] ?? 0, 2) }}</span>
            </div>
        </div>

        @if(isset($invoice['notes']) && $invoice['notes'])
            <div style="margin-top: 30px;">
                <div class="label">Notes</div>
                <div class="value" style="color: #718096; margin-top: 5px;">{{ $invoice['notes'] }}</div>
            </div>
        @endif

        <div class="footer">
            <div>{{ $invoice['company']['name'] ?? 'Company' }}</div>
        </div>
    </div>
</body>
</html>

