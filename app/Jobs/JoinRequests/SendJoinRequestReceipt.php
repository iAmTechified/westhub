<?php

namespace App\Jobs\JoinRequests;

use App\Mail\JoinRequestReceiptMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendJoinRequestReceipt implements ShouldQueue
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
        try {
            $fromEmail = \App\Support\SiteSettings::applicationsEmail();
            $fromName = \App\Support\SiteSettings::fromName();

            $mailable = new JoinRequestReceiptMail($this->payload);
            if (filled($fromEmail)) {
                $mailable->from($fromEmail, $fromName);
            }

            Mail::to((string) data_get($this->payload, 'email'))
                ->send($mailable);
        } catch (\Throwable $exception) {
            Log::error('Join request receipt email failed.', [
                'join_request_id' => data_get($this->payload, 'westhub_id'),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
