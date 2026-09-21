<?php

/**
 * Send one test email through whatever MAIL_* settings are live.
 *
 *   php scratch/test_mail.php you@example.com
 *
 * A file avoids the shell quoting that makes `tinker --execute` painful on
 * PowerShell, where $m inside double quotes is eaten before PHP sees it.
 */
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

$to = $argv[1] ?? config('mail.from.address');

echo 'mailer:  ', config('mail.default'), PHP_EOL;
echo 'host:    ', config('mail.mailers.smtp.host'), ':', config('mail.mailers.smtp.port'),
     ' (', config('mail.mailers.smtp.encryption') ?: 'no encryption', ')', PHP_EOL;
echo 'from:    ', config('mail.from.address'), PHP_EOL;
echo 'to:      ', $to, PHP_EOL, PHP_EOL;

try {
    Mail::raw('This is a test email from WestHub, sent at '.now()->toDateTimeString().'.', function ($message) use ($to) {
        $message->to($to)->subject('WestHub mail test');
    });

    echo 'SUCCESS: the mail server accepted the message. Check the inbox, then spam.', PHP_EOL;
} catch (Throwable $e) {
    echo 'FAILURE: ', $e->getMessage(), PHP_EOL;
}
