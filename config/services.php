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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth
    |--------------------------------------------------------------------------
    |
    | Credentials for Google OAuth authentication. Enable Google login
    | by setting GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env
    | or configure in admin settings.
    |
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
        'enabled' => env('GOOGLE_AUTH_ENABLED', !empty(env('GOOGLE_CLIENT_ID')) && !empty(env('GOOGLE_CLIENT_SECRET'))),
    ],

    /*
    |--------------------------------------------------------------------------
    | TurboSMTP
    |--------------------------------------------------------------------------
    |
    | Credentials for TurboSMTP email delivery service.
    | Configure in admin settings or .env file.
    |
    */
    'turbosmtp' => [
        'server' => env('TURBOSMTP_SERVER', 'pro.turbo-smtp.com'),
        'port' => env('TURBOSMTP_PORT', 587),
        'username' => env('TURBOSMTP_USERNAME'),
        'password' => env('TURBOSMTP_PASSWORD'),
        'from_address' => env('TURBOSMTP_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
        'from_name' => env('TURBOSMTP_FROM_NAME', env('MAIL_FROM_NAME')),
        'enabled' => env('TURBOSMTP_ENABLED', !empty(env('TURBOSMTP_USERNAME')) && !empty(env('TURBOSMTP_PASSWORD'))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Credentials for payment gateway integrations (Paystack, Kora, Stripe).
    | These can be configured via admin settings UI or .env file.
    |
    */
    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'enabled' => env('PAYSTACK_ENABLED', false),
    ],

    'kora' => [
        'public_key' => env('KORA_PUBLIC_KEY'),
        'secret_key' => env('KORA_SECRET_KEY'),
        'enabled' => env('KORA_ENABLED', false),
    ],

    'stripe' => [
        'public_key' => env('STRIPE_PUBLIC_KEY'),
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'enabled' => env('STRIPE_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway - Local Development Settings
    |--------------------------------------------------------------------------
    |
    | Settings for local development and testing without real payment gateways.
    |
    */
    'payment' => [
        // Enable mock payment mode - simulates successful payments without calling real gateways
        'mock_enabled' => env('PAYMENT_MOCK_ENABLED', false),

        // Custom callback URL - useful when using ngrok or tunnel for local testing
        // Example: https://abc123.ngrok-free.app
        'callback_url' => env('PAYMENT_CALLBACK_URL'),

        // Auto-enable sandbox mode when running on local environment
        'sandbox_auto' => env('PAYMENT_SANDBOX_AUTO', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | VAPID Keys — Web Push Notifications
    |--------------------------------------------------------------------------
    |
    | Generate a key pair once with:  php artisan webpush:vapid
    | Then set the two env variables below.
    |
    */
    'vapid' => [
        'subject'     => env('VAPID_SUBJECT') ?: 'mailto:' . (env('MAIL_FROM_ADDRESS') ?: 'admin@swiftkudi.com'),
        'public_key'  => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

];
