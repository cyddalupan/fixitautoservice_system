<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Appointment;
use App\Models\Customer;

/**
 * Notifies admin when a client books a new appointment via the public
 * website / booking flow. Recipients come from config('mail.admin_recipients')
 * (per ADMIN NAV DECISION: cydmdalupan@gmail.com + andrewacecontreras@gmail.com).
 */
class NewBookingAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $customer;

    public function __construct(Appointment $appointment, ?Customer $customer = null)
    {
        $this->appointment = $appointment;
        $this->customer = $customer;
    }

    public function build(): self
    {
        return $this
            ->subject('New Appointment: ' . $this->appointment->appointment_number)
            ->view('emails.new-booking-admin-notification');
    }
}
