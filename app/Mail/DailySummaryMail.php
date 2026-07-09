<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfContent;
    public $dateString;

    public function __construct($pdfContent, $dateString)
    {
        $this->pdfContent = $pdfContent;
        $this->dateString = $dateString;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Executive Summary - BiteHub (' . $this->dateString . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_summary',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'Daily_Summary_' . $this->dateString . '.pdf')
                    ->withMime('application/pdf'),
        ];
    }
}
