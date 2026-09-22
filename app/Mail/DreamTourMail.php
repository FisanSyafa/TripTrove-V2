<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DreamTourMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dreamTourRequest;
    public $customMessage;
    public $emailSubject;

    public function __construct($dreamTourRequest, $customMessage, $emailSubject = 'Dream Tour Request Update')
    {
        $this->dreamTourRequest = $dreamTourRequest;
        $this->customMessage = $customMessage;
        $this->emailSubject = $emailSubject;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.dream_tour',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
