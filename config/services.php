<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sms' => [
        'provider' => env('SMS_PROVIDER', 'log'), // 'log', 'africastalking', 'twilio'
        'africastalking' => [
            'api_key' => env('AFRICASTALKING_API_KEY'),
            'username' => env('AFRICASTALKING_USERNAME'),
            'from' => env('AFRICASTALKING_FROM', 'INVOICE'),
        ],
        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'mpesa' => [
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        'environment' => env('MPESA_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'production'
        // Default to subscription callback route. For invoice payments, use /webhooks/mpesa/callback
        // In production, set MPESA_CALLBACK_URL in .env with your ngrok/public URL
        'callback_url' => env('MPESA_CALLBACK_URL', env('APP_URL').'/webhooks/subscriptions/mpesa/callback'),
    ],

    'etims' => [
        'api_url' => env('ETIMS_API_URL'),
        'api_key' => env('ETIMS_API_KEY'),
        'mode' => env('ETIMS_MODE', 'local'), // 'local' or 'api' - local generates QR codes, api submits to KRA
    ],

];
