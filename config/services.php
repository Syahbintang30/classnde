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
        'token' => env('POSTMARK_TOKEN', null),
    ],

    'resend' => [
        'key' => env('RESEND_KEY', null),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID', null),
        'secret' => env('AWS_SECRET_ACCESS_KEY', null),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN', null),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL', null),
        ],
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID', null),
        'auth_token' => env('TWILIO_AUTH_TOKEN', null),
        'api_key_sid' => env('TWILIO_API_KEY_SID', null),
        'api_key_secret' => env('TWILIO_API_KEY_SECRET', null),
        'service_sid' => env('TWILIO_SERVICE_SID', null),
        // Optional: overrideable IP ranges for webhook validation (comma-separated CIDRs)
        // Example env: TWILIO_WEBHOOK_IP_RANGES="54.172.60.0/23,54.244.51.0/24"
        'webhook_ip_ranges' => array_values(array_filter(array_map('trim', explode(',', env('TWILIO_WEBHOOK_IP_RANGES', ''))))),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', null),
    ],

];
