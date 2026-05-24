<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingMagicLink extends Mailable
{
    use Queueable, SerializesModels;

    public Customer $customer;
    public string $magicUrl;

    public function __construct(Customer $customer, string $magicUrl)
    {
        $this->customer = $customer;
        $this->magicUrl = $magicUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Booking Login Link – Fix-It Auto Services',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'booking.emails.magic-link',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
