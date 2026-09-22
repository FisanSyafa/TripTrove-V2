<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingAvailabilityMail extends Mailable
{
    public $booking;
    public $customMessage;
    public $emailSubject;
    public $buttonText;

    /**
     * Create a new message instance.
     */
    public function __construct($booking, $customMessage = null, $emailSubject = 'Booking Availability Update - TripTrove', $buttonText = 'Lanjutkan ke Pembayaran')
    {
        $this->booking = $booking;
        $this->customMessage = $customMessage;
        $this->emailSubject = $emailSubject;
        $this->buttonText = $buttonText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking_availability',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
