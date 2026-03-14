<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class BookingCreatedMail extends Mailable
{
    use SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function envelope(): Envelope
    {
        // Set locale for email (fallback to 'id' if locale column doesn't exist yet)
        $locale = isset($this->booking->locale) ? $this->booking->locale : 'id';
        App::setLocale($locale);
        
        return new Envelope(
            subject: __('Booking Successfully Created') . ' - ' . $this->booking->booking_code,
        );
    }

    public function content(): Content
    {
        // Set locale for email content (fallback to 'id' if locale column doesn't exist yet)
        $locale = isset($this->booking->locale) ? $this->booking->locale : 'id';
        App::setLocale($locale);
        
        return new Content(
            view: 'emails.booking.created',
            with: [
                'booking' => $this->booking,
                'user' => $this->booking->user,
                'package' => $this->booking->tourPackage,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
