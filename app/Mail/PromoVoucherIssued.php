<?php

namespace App\Mail;

use App\Models\PromoClaim;
use App\Support\PromoOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PromoVoucherIssued extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public PromoClaim $claim,
        public PromoOffer $offer,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your free ' . \Illuminate\Support\Str::lower($this->offer->offerAmount) . ' of WestHub healthcare services',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.promo-voucher-issued',
        );
    }
}
