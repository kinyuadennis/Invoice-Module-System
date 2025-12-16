<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoiceNumber }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h1 style="color: #2B6EF6; margin-top: 0;">Invoice #{{ $invoiceNumber }}</h1>
        <p style="margin: 0; color: #666;">From: {{ $company->name ?? 'InvoiceHub' }}</p>
    </div>

    <div style="background-color: #fff; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 20px;">
        <p>Dear {{ $client->name ?? 'Valued Client' }},</p>
        
        <p>We hope this email finds you well. Please find attached invoice #{{ $invoiceNumber }} for your records.</p>

        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 20px 0;">
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
                    <td style="padding: 8px 0; text-align: right;">{{ \Carbon\Carbon::parse($dueDate)->format('F d, Y') }}</td>
                </tr>
                @endif
                <tr style="border-top: 2px solid #2B6EF6;">
                    <td style="padding: 8px 0; font-weight: bold; font-size: 18px;">Total Amount:</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: bold; font-size: 18px; color: #2B6EF6;">KES {{ number_format($total, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($dueDate)
        <p style="color: #666; font-size: 14px;">
            <strong>Payment is due by:</strong> {{ \Carbon\Carbon::parse($dueDate)->format('F d, Y') }}
        </p>
        @endif

        @if(isset($accessUrl) && $accessUrl)
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $accessUrl }}" style="display: inline-block; background-color: #2B6EF6; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                View & Pay Invoice Online
            </a>
        </div>
        @endif

        <p>If you have any questions about this invoice, please don't hesitate to contact us.</p>

        <p>Thank you for your business!</p>

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

