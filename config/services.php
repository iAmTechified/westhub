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

    /*
    | These are FALLBACKS only. The live values are read from the shared
    | `settings` table via App\Support\SiteSettings so they can be changed in
    | the admin without a deploy. Env is used when a setting has never been
    | saved.
    */

    'appointments' => [
        'provider' => env('APPOINTMENT_PROVIDER', 'calendly'),
    ],

    'calendly' => [
        'appointment_url' => env('CALENDLY_APPOINTMENT_URL'),
    ],

    'google_booking_page' => [
        'url' => env('GOOGLE_BOOKING_PAGE_URL'),
    ],

    'google_calendar' => [
        'calendar_id' => env('GOOGLE_CALENDAR_ID'),
        'service_account_email' => env('GOOGLE_SERVICE_ACCOUNT_EMAIL'),
        'private_key' => env('GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY'),
        'timezone' => env('GOOGLE_CALENDAR_TIMEZONE', env('APP_TIMEZONE', 'UTC')),
        'default_event_duration' => (int) env('GOOGLE_CALENDAR_EVENT_DURATION_MINUTES', 60),
    ],

    'google_sheets' => [
        'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
        'sheet_name' => env('GOOGLE_SHEETS_SHEET_NAME', 'Join Requests'),
        'service_account_email' => env('GOOGLE_SHEETS_SERVICE_ACCOUNT_EMAIL'),
        'private_key' => env('GOOGLE_SHEETS_PRIVATE_KEY'),
        // "service_account" (default) or "apps_script".
        'method' => env('GOOGLE_SHEETS_METHOD'),
        'apps_script_url' => env('GOOGLE_SHEETS_APPS_SCRIPT_URL'),
        'apps_script_secret' => env('GOOGLE_SHEETS_APPS_SCRIPT_SECRET'),
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
