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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'calendly' => [
        'appointment_url' => env('CALENDLY_APPOINTMENT_URL'),
    ],

    'google_sheets' => [
        'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
        'sheet_name' => env('GOOGLE_SHEETS_SHEET_NAME', 'Join Requests'),
        'service_account_email' => env('GOOGLE_SHEETS_SERVICE_ACCOUNT_EMAIL'),
        'private_key' => env('GOOGLE_SHEETS_PRIVATE_KEY'),
    ],

    'westhub_admin' => [
        'base_url' => env('WESTHUB_ADMIN_BASE_URL', env('APP_URL')),
    ],

    'westhub_join_requests' => [
        'hiring_emails' => array_values(array_filter(array_map(
            static fn (string $value): string => trim($value),
            explode(',', (string) env('WESTHUB_JOIN_HIRING_EMAILS', env('WESTHUB_SUPPORT_EMAIL', '')))
        ))),
    ],

];
