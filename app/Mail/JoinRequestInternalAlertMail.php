<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JoinRequestInternalAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public array $payload,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New WestHub join application: '.data_get($this->payload, 'full_name', 'Applicant'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.join_request_internal_alert',
            with: [
                'payload' => $this->payload,
            ],
        );
    }
}
