<?php

namespace App\Mail;

use App\Models\PromoCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PromoCodeAnnouncement extends Mailable
{
    use Queueable, SerializesModels;

    public PromoCode $promo;

    public function __construct(PromoCode $promo)
    {
        $this->promo = $promo;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Exclusive Promo Code: ' . $this->promo->Code . ' – Save Now on BiteHub!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.promo_code_announcement',
        );
    }
}
