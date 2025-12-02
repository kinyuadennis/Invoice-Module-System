<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #ffffff; border-radius: 8px; padding: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="color: #1e293b; font-size: 24px; margin-bottom: 20px;">Verify Your Email Address</h1>
        
        <p style="color: #64748b; font-size: 16px; margin-bottom: 20px;">
            Hello {{ $user->name }},
        </p>
        
        <p style="color: #64748b; font-size: 16px; margin-bottom: 30px;">
            Thank you for signing up! Please click the button below to verify your email address and complete your registration.
        </p>
        
        <div style="text-align: center; margin: 40px 0;">
            <a href="{{ $verificationUrl }}" style="display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 12px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                Verify Email Address
            </a>
        </div>
        
        <p style="color: #94a3b8; font-size: 14px; margin-top: 30px; margin-bottom: 10px;">
            If you did not create an account, no further action is required.
        </p>
        
        <p style="color: #94a3b8; font-size: 14px; margin-top: 20px;">
            This verification link will expire in 24 hours.
        </p>
        
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        
        <p style="color: #94a3b8; font-size: 12px; margin: 0;">
            If the button doesn't work, copy and paste this link into your browser:<br>
            <a href="{{ $verificationUrl }}" style="color: #3b82f6; word-break: break-all;">{{ $verificationUrl }}</a>
        </p>
    </div>
</body>
</html>
