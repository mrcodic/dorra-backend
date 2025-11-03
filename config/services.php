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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT_URI'),
        'team_id' => env('APPLE_TEAM_ID'),
        'key_id' => env('APPLE_KEY_ID'),
        'private_key' => env('APPLE_PRIVATE_KEY_PATH'),
    ],

    'editor_url' => env('FRONT_END_EDITOR_URL'),
    'site_url' => env('FRONT_END_URL'),
    'paymob' => [
        'base_url' => env('PAYMOB_BASE_URL'),
        'redirection_url' => env('PAYMOB_REDIRECTION_URL'),
        'notification_url' => env('PAYMOB_NOTIFICATION_URL'),
        'api_key' => env('PAYMOB_API_KEY'),
        'public_key' => env('PAYMOB_PUBLIC_API_KEY'),
        'secret_key' => env('PAYMOB_SECRET_KEY'),
        'kiosk_integration_id' => env('PAYMOB_KIOSK_INTEGRATION_ID'),
        'card_integration_id' => env('PAYMOB_CARD_INTEGRATION_ID'),
        'wallet_integration_id' => env('PAYMOB_WALLET_INTEGRATION_ID'),
        'hmac' => env('PAYMOB_HMAC'),
        'currency' => 'EGP',
    ],
    'google_maps' => [
        'key' => env('GOOGLE_MAPS_KEY'),
    ],
    'shipblu' => [
        'key'  => env('SHIPBLU_API_KEY'),
        'base' => rtrim(env('SHIPBLU_BASE_URL', 'https://api.shipblu.com'), '/'),
        'webhook_secret' => env('SHIPBLU_WEBHOOK_SECRET'),
    ],



];
