<?php

namespace App\Jobs\Mail;

use App\Mail\JoinDecisionMail;
use App\Models\JoinRequest;
use App\Models\OutboundMessage;
use App\Support\Admin\JoinDecisionTemplateFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendJoinDecisionMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $joinRequestId,
        public string $templateKey,
    ) {
    }

    public function handle(): void
    {
        $joinRequest = JoinRequest::query()->findOrFail($this->joinRequestId);
        $template = JoinDecisionTemplateFactory::make($this->templateKey, $joinRequest->full_name);

        $message = OutboundMessage::create([
            'join_request_id' => $joinRequest->id,
            'recipient_email' => $joinRequest->email,
            'template_key' => $this->templateKey,
            'provider' => config('mail.default'),
            'status' => 'queued',
            'subject' => $template['subject'],
            'body' => $template['body'],
        ]);

        try {
            $fromEmail = \App\Support\SiteSettings::applicationsEmail();
            $fromName = \App\Support\SiteSettings::fromName();

            $mailable = new JoinDecisionMail(
                $template['subject'],
                $template['body'],
                $joinRequest->full_name,
                $this->templateKey,
            );

            if (filled($fromEmail)) {
                $mailable->from($fromEmail, $fromName);
            }

            Mail::to($joinRequest->email)->send($mailable);

            $message->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $message->update([
                'status' => 'failed',
                'failed_at' => now(),
                'provider_response' => ['error' => $exception->getMessage()],
            ]);

            throw $exception;
        }
    }
}
