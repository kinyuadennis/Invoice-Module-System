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
        @page {
            margin: 43px; /* Standard margins - no header/footer space needed */
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .container { max-width: 800px; margin: 0 auto; padding: 0; }
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
<body class="{{ isset($template) && $template->layout_class ? $template->layout_class : 'template-bold-modern' }}">
    <div class="container">
        <div class="content" style="padding: 0 40px;">
            <div class="two-columns">
                <div class="column">
                    <div class="section-title">Bill From</div>
                    <div style="font-weight: bold; margin-bottom: 5px;">{{ $invoice['company']['name'] ?? 'Company Name' }}</div>
                    @if(isset($invoice['company']['address']) && $invoice['company']['address'])
                        <div style="color: #666;">{{ $invoice['company']['address'] }}</div>
                    @endif
                    @if(isset($invoice['company']['phone']) && $invoice['company']['phone'])
                        <div style="color: #666;">{{ $invoice['company']['phone'] }}</div>
                    @endif
                    @if(isset($invoice['company']['email']) && $invoice['company']['email'])
                        <div style="color: #666;">{{ $invoice['company']['email'] }}</div>
                    @endif
                    @if(isset($invoice['company']['kra_pin']) && $invoice['company']['kra_pin'])
                        <div style="color: #666;">KRA PIN: {{ $invoice['company']['kra_pin'] }}</div>
                    @endif
                </div>
                <div class="column">
                    <div class="section-title">Bill To</div>
                    <div style="font-weight: bold; margin-bottom: 5px;">{{ $invoice['client']['name'] ?? 'Client Name' }}</div>
                    @if(isset($invoice['client']['address']) && $invoice['client']['address'])
                        <div style="color: #666;">{{ $invoice['client']['address'] }}</div>
                    @endif
                    @if(isset($invoice['client']['phone']) && $invoice['client']['phone'])
                        <div style="color: #666;">{{ $invoice['client']['phone'] }}</div>
                    @endif
                    @if(isset($invoice['client']['email']) && $invoice['client']['email'])
                        <div style="color: #666;">{{ $invoice['client']['email'] }}</div>
                    @endif
                </div>
            </div>
            <div style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="section-title">Invoice Details</div>
                    <div>Issue Date: {{ $invoice['date'] ?? date('Y-m-d') }}</div>
                    <div>Due Date: {{ $invoice['due_date'] ?? 'N/A' }}</div>
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
                            <td class="text-right">KES {{ number_format($item['total_price'] ?? $item['total'] ?? 0, 2) }}</td>
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
                        <span>Platform Fee (3%):</span>
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

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 10px;">
                <div>Thank you for your business!</div>
            </div>
        </div>
    </div>
</body>
</html>

