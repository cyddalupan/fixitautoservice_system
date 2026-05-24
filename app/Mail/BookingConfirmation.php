<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;
    public Customer $customer;

    public function __construct(Appointment $appointment, Customer $customer)
    {
        $this->appointment = $appointment;
        $this->customer = $customer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Appointment Confirmed – Fix-It Auto Services',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'booking.emails.confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
