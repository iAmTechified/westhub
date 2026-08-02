<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('This is a test email from Westhub locally.', function ($message) {
        $message->to('westhub@obeleat50.com')->subject('Local Mail Test');
    });
    echo "SUCCESS: Mail sent successfully.\n";
} catch (\Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
