<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Vouchers stop being redeemable on their own date regardless; this keeps the
// stored status honest so the admin list and the Google Sheet stay accurate.
// Needs the cPanel cron entry that runs `php artisan schedule:run` every minute.
Schedule::command('westhub:expire-promo-claims')->dailyAt('01:30');
