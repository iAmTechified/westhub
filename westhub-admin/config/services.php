<?php

return [

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

    'ga4' => [
        'property_id' => env('GA4_PROPERTY_ID'),
        'credentials_json' => env('GA4_CREDENTIALS_JSON'),
    ],

    'search_console' => [
        'site_url' => env('SEARCH_CONSOLE_SITE_URL'),
        'credentials_json' => env('SEARCH_CONSOLE_CREDENTIALS_JSON'),
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
    ],

];
