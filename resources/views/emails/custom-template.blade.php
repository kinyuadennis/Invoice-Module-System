<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email from {{ $company->name ?? 'InvoiceHub' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    {!! $customBody !!}
    
    <div style="text-align: center; color: #999; font-size: 12px; margin-top: 30px;">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} {{ $company->name ?? 'InvoiceHub' }}. All rights reserved.</p>
    </div>
</body>
</html>

