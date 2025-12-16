<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminder - Invoice #{{ $invoiceNumber }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: {{ $reminderType === 'overdue' ? '#dc3545' : '#ffc107' }}; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h1 style="color: #fff; margin-top: 0;">
            @if($reminderType === 'overdue')
                Payment Overdue
            @else
                Payment Reminder
            @endif
        </h1>
        <p style="margin: 0; color: #fff;">Invoice #{{ $invoiceNumber }}</p>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 20px;">
        <p>Dear {{ $client->name ?? 'Valued Client' }},</p>
        
        @if($reminderType === 'overdue')
            <p style="color: #dc3545; font-weight: bold;">This is a friendly reminder that your invoice payment is now overdue.</p>
            @if($daysOverdue > 0)
                <p>The payment for invoice #{{ $invoiceNumber }} was due {{ $daysOverdue }} day(s) ago.</p>
            @endif
        @else
            <p>This is a friendly reminder that your invoice payment is due soon.</p>
            @if($dueDate)
                <p>The payment for invoice #{{ $invoiceNumber }} is due on {{ \Carbon\Carbon::parse($dueDate)->format('F d, Y') }}.</p>
            @endif
        @endif

        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid {{ $reminderType === 'overdue' ? '#dc3545' : '#ffc107' }};">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Invoice Number:</td>
                    <td style="padding: 8px 0; text-align: right;">{{ $invoiceNumber }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Issue Date:</td>
                    <td style="padding: 8px 0; text-align: right;">{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('F d, Y') : 'N/A' }}</td>
                </tr>
                @if($dueDate)
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Due Date:</td>
                    <td style="padding: 8px 0; text-align: right; {{ $reminderType === 'overdue' ? 'color: #dc3545; font-weight: bold;' : '' }}">{{ \Carbon\Carbon::parse($dueDate)->format('F d, Y') }}</td>
                </tr>
                @endif
                <tr style="border-top: 2px solid #2B6EF6;">
                    <td style="padding: 8px 0; font-weight: bold; font-size: 18px;">Amount Due:</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: bold; font-size: 18px; color: #2B6EF6;">KES {{ number_format($total, 2) }}</td>
                </tr>
            </table>
        </div>

        <p>Please arrange payment at your earliest convenience. If you have already made the payment, please disregard this reminder.</p>

        <p>If you have any questions or concerns about this invoice, please don't hesitate to contact us.</p>

        <p>Thank you for your prompt attention to this matter.</p>

        <p style="margin-top: 30px;">
            Best regards,<br>
            <strong>{{ $company->name ?? 'InvoiceHub Team' }}</strong>
        </p>
    </div>

    <div style="text-align: center; color: #999; font-size: 12px; margin-top: 30px;">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} {{ $company->name ?? 'InvoiceHub' }}. All rights reserved.</p>
    </div>
</body>
</html>

