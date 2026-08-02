<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JoinRequestReceiptMail extends Mailable
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
            subject: 'We received your WestHub Healthcare application',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.join-request-receipt',
            with: [
                'payload' => $this->payload,
            ],
        );
    }
}
