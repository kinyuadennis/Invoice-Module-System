<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Estimate {{ $estimate['full_number'] }}</title>
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
        .estimate-title {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .disclaimer {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 9pt;
            font-style: italic;
            color: #92400e;
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
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Disclaimer -->
        <div class="disclaimer">
            <strong>ESTIMATE - NOT A TAX INVOICE</strong><br>
            This is a non-binding estimate. Prices and terms are subject to change until accepted. This document does not constitute a tax invoice.
        </div>

        <!-- Header -->
        <div class="header clearfix">
            <div class="header-left">
                @if(!empty($seller['logo']))
                    <img src="{{ $seller['logo'] }}" class="logo" alt="Logo">
                @endif
                <div class="company-name">{{ $seller['name'] }}</div>
                <div>{{ $seller['address'] }}</div>
                @if(!empty($seller['email']))
                    <div>{{ $seller['email'] }}</div>
                @endif
                @if(!empty($seller['phone']))
                    <div>{{ $seller['phone'] }}</div>
                @endif
                @if(!empty($seller['kra_pin']))
                    <div>KRA PIN: {{ $seller['kra_pin'] }}</div>
                @endif
            </div>
            <div class="header-right">
                <div class="estimate-title">ESTIMATE</div>
                <table class="meta-table" align="right">
                    <tr>
                        <td class="meta-label">Estimate #:</td>
                        <td>{{ $estimate['full_number'] ?? $estimate['estimate_number'] ?? $estimate['estimate_reference'] }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Date:</td>
                        <td>{{ $estimate['issue_date']->format('Y-m-d') }}</td>
                    </tr>
                    @if(!empty($estimate['expiry_date']))
                    <tr>
                        <td class="meta-label">Valid Until:</td>
                        <td>{{ $estimate['expiry_date']->format('Y-m-d') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="meta-label">Status:</td>
                        <td style="text-transform: capitalize;">{{ $estimate['status'] }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Bill To -->
        @if($client)
        <div class="bill-to">
            <div class="section-title">Estimate For</div>
            <div style="font-weight: bold;">{{ $client['name'] }}</div>
            @if(!empty($client['address']))
                <div>{{ $client['address'] }}</div>
            @endif
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
        @endif

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
                    <td class="numeric">{{ number_format($item['quantity'], 2) }}</td>
                    <td class="numeric">KES {{ number_format($item['unit_price'], 2) }}</td>
                    <td class="numeric">KES {{ number_format($item['total_price'], 2) }}</td>
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
                        <td class="amount">KES {{ number_format($totals['subtotal'], 2) }}</td>
                    </tr>
                    @if($totals['discount'] > 0)
                    <tr>
                        <td class="label">Discount @if($totals['discount_type'] === 'percentage')({{ $totals['discount_type'] }})@endif</td>
                        <td class="amount">- KES {{ number_format($totals['discount'], 2) }}</td>
                    </tr>
                    @endif
                    @if($totals['vat_registered'] && $totals['vat_amount'] > 0)
                    <tr>
                        <td class="label">VAT (16%)</td>
                        <td class="amount">KES {{ number_format($totals['vat_amount'], 2) }}</td>
                    </tr>
                    @endif
                    @if($totals['platform_fee'] > 0)
                    <tr>
                        <td class="label">Platform Fee</td>
                        <td class="amount">KES {{ number_format($totals['platform_fee'], 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="2"><div style="border-bottom: 1px solid #eee; margin: 5px 0;"></div></td>
                    </tr>
                    <tr>
                        <td class="label grand-total">ESTIMATED TOTAL</td>
                        <td class="amount grand-total">KES {{ number_format($totals['grand_total'], 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Notes & Terms -->
        @if(!empty($estimate['notes']))
        <div style="margin-top: 30px; margin-bottom: 20px;">
            <div class="section-title">Notes</div>
            <div style="white-space: pre-wrap;">{{ $estimate['notes'] }}</div>
        </div>
        @endif

        @if(!empty($estimate['terms_and_conditions']))
        <div style="margin-bottom: 120px;">
            <div class="section-title">Terms & Conditions</div>
            <div style="white-space: pre-wrap; font-size: 9pt;">{{ $estimate['terms_and_conditions'] }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div style="margin-bottom: 10px; font-weight: bold;">
                This is an estimate and not a binding contract until accepted.
            </div>
            <div style="margin-bottom: 10px;">
                Please review and contact us if you have any questions.
            </div>
            <div style="margin-top: 10px; font-size: 7pt; color: #999;">
                Generated on {{ now()->format('Y-m-d H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>

