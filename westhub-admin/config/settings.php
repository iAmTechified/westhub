<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shared Settings Encryption Key
    |--------------------------------------------------------------------------
    |
    | The public site and the admin app run as two separate Laravel apps with
    | their own APP_KEY, but they share one `settings` table. Any setting that
    | is stored encrypted therefore cannot be read across apps unless both use
    | the same encryption key.
    |
    | Set SETTINGS_ENCRYPTION_KEY to the SAME value in both apps. Generate one
    | with `php artisan westhub:settings-key`. When it is not set, encryption
    | falls back to APP_KEY, which means encrypted settings written by one app
    | are unreadable by the other.
    |
    */

    'encryption_key' => env('SETTINGS_ENCRYPTION_KEY'),

    'cipher' => env('SETTINGS_ENCRYPTION_CIPHER', 'aes-256-cbc'),

];
