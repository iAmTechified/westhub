<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnquirySubmittedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Appointment $enquiry,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your WestHub Healthcare enquiry',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enquiry-submitted',
        );
    }
}
