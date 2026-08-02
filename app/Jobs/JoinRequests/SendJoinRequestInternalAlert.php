<?php

namespace App\Jobs\JoinRequests;

use App\Mail\JoinRequestInternalAlertMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendJoinRequestInternalAlert implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public array $payload,
    ) {
    }

    public function handle(): void
    {
        $recipients = config('services.westhub_join_requests.hiring_emails', []);
        if (empty($recipients)) {
            $appEmail = \App\Support\SiteSettings::applicationsEmail();
            if (filled($appEmail)) {
                $recipients = [$appEmail];
            }
        }

        if ($recipients === []) {
            return;
        }

        try {
            $fromEmail = \App\Support\SiteSettings::applicationsEmail();
            $fromName = \App\Support\SiteSettings::fromName();

            $mailable = new JoinRequestInternalAlertMail($this->payload);
            if (filled($fromEmail)) {
                $mailable->from($fromEmail, $fromName);
            }

            Mail::to($recipients)->send($mailable);
        } catch (\Throwable $exception) {
            Log::error('Join request internal alert email failed.', [
                'join_request_id' => data_get($this->payload, 'westhub_id'),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
