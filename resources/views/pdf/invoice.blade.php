<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice['full_number'] }}</title>
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            width: 100%;
            margin-bottom: 40px;
        }
        .header-left {
            float: left;
            width: 60%;
        }
        .header-right {
            float: right;
            width: 35%;
            text-align: right;
        }
        .logo {
            max-width: 150px;
            max-height: 80px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin-bottom: 10px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2px 0;
            text-align: right;
        }
        .meta-label {
            font-weight: bold;
            color: #666;
        }
        .bill-to {
            margin-bottom: 30px;
            clear: both;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            text-align: left;
            padding: 10px 5px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 10px 5px;
            border-bottom: 1px solid #eee;
        }
        .items-table .numeric {
            text-align: right;
        }
        .totals-block {
            float: right;
            width: 40%;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 0;
            text-align: right;
        }
        .totals-table .label {
            color: #666;
        }
        .totals-table .amount {
            font-weight: bold;
        }
        .totals-table .grand-total {
            font-size: 12pt;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 5px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            text-align: center;
            font-size: 8pt;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .compliance-note {
            margin-top: 10px;
            font-style: italic;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="header-left">
                @if(!empty($seller['logo']))
                    <img src="{{ $seller['logo'] }}" class="logo" alt="Logo">
                @endif
                <div class="company-name">{{ $seller['name'] }}</div>
                <div>{{ $seller['address'] }}</div>
                <div>{{ $seller['email'] }}</div>
                <div>{{ $seller['phone'] }}</div>
                @if(!empty($seller['kra_pin']))
                    <div>KRA PIN: {{ $seller['kra_pin'] }}</div>
                @endif
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <table class="meta-table" align="right">
                    <tr>
                        <td class="meta-label">Invoice #:</td>
                        <td>{{ $invoice['full_number'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Date:</td>
                        <td>{{ $invoice['issue_date'] }}</td>
                    </tr>
                    @if(!empty($invoice['due_date']))
                    <tr>
                        <td class="meta-label">Due Date:</td>
                        <td>{{ $invoice['due_date'] }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="meta-label">Status:</td>
                        <td style="text-transform: capitalize;">{{ $invoice['status'] }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Bill To -->
        <div class="bill-to">
            <div class="section-title">Bill To</div>
            <div style="font-weight: bold;">{{ $client['name'] }}</div>
            <div>{{ $client['address'] }}</div>
            @if(!empty($client['email']))
                <div>{{ $client['email'] }}</div>
            @endif
            @if(!empty($client['phone']))
                <div>{{ $client['phone'] }}</div>
            @endif
            @if(!empty($client['kra_pin']))
                <div>KRA PIN: {{ $client['kra_pin'] }}</div>
            @endif
        </div>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="40%">Description</th>
                    <th width="15%" class="numeric">Qty</th>
                    <th width="20%" class="numeric">Unit Price</th>
                    <th width="25%" class="numeric">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td class="numeric">{{ $item['quantity'] }}</td>
                    <td class="numeric">{{ number_format($item['unit_price'], 2) }}</td>
                    <td class="numeric">{{ number_format($item['total'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="clearfix">
            <div class="totals-block">
                <table class="totals-table">
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="amount">{{ $invoice['currency'] }} {{ number_format($totals['subtotal'], 2) }}</td>
                    </tr>
                    @if($totals['discount'] > 0)
                    <tr>
                        <td class="label">Discount</td>
                        <td class="amount">- {{ $invoice['currency'] }} {{ number_format($totals['discount'], 2) }}</td>
                    </tr>
                    @endif
                    @if($totals['tax'] > 0)
                    <tr>
                        <td class="label">VAT (16%)</td>
                        <td class="amount">{{ $invoice['currency'] }} {{ number_format($totals['tax'], 2) }}</td>
                    </tr>
                    @endif
                    @if($totals['platform_fee'] > 0)
                    <tr>
                        <td class="label">Platform Fee</td>
                        <td class="amount">{{ $invoice['currency'] }} {{ number_format($totals['platform_fee'], 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="2"><div style="border-bottom: 1px solid #eee; margin: 5px 0;"></div></td>
                    </tr>
                    <tr>
                        <td class="label grand-total">TOTAL</td>
                        <td class="amount grand-total">{{ $invoice['currency'] }} {{ number_format($totals['grand_total'], 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            @if(!empty($seller['payment_terms']))
                <div style="margin-bottom: 10px;">
                    <strong>Payment Terms:</strong> {{ $seller['payment_terms'] }}
                </div>
            @endif
            
            <div style="margin-bottom: 10px;">
                Thank you for your business!
            </div>

            @if(!empty($compliance['etims_control_number']))
                <div class="compliance-note">
                    <strong>KRA e-TIMS Compliant</strong><br>
                    Control No: {{ $compliance['etims_control_number'] }}
                    @if(!empty($compliance['etims_submitted_at']))
                         | Date: {{ \Carbon\Carbon::parse($compliance['etims_submitted_at'])->format('Y-m-d H:i:s') }}
                    @endif
                </div>
            @endif
            
            <div style="margin-top: 10px; font-size: 7pt; color: #999;">
                Generated on {{ \Carbon\Carbon::parse($metadata['generated_at'])->format('Y-m-d H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
