<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Mail\BookingConfirmation;
use App\Mail\BookingMagicLink;
use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class BookingMailablesRenderTest extends TestCase
{
    /**
     * Prerequisite for "Test that emails actually send": the two booking
     * mailables (BookingConfirmation, BookingMagicLink) must build, render,
     * and be queuable so that once real SMTP credentials are configured in
     * production, outgoing booking emails have nothing blocking them.
     *
     * This test uses the (in-memory sqlite + array mailer) test environment;
     * it does NOT require a live SMTP server. It verifies mail readiness,
     * not delivery.
     */
    public function test_booking_confirmation_mailable_renders_and_is_sendable(): void
    {
        Mail::fake();

        $customer = new Customer([
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'email'      => 'juan@example.com',
        ]);

        $appointment = new Appointment([
            'appointment_number' => 'APT-2026-0001',
            'appointment_type'   => 'oil_change',
            'customer_notes'     => 'Please check brakes too.',
        ]);
        // Mimic the model's date cast used by the confirmation view.
        $appointment->appointment_date = Carbon::parse('2026-08-15');
        $appointment->appointment_time = '09:30';

        Mail::to($customer->email)->send(new BookingConfirmation($appointment, $customer));

        // A matching mailable was sent to the customer.
        Mail::assertSent(BookingConfirmation::class, fn ($mail) => $mail->hasTo('juan@example.com'));

        // Render the mailable's HTML to prove the view compiles without error.
        $html = (new BookingConfirmation($appointment, $customer))->render();
        $this->assertStringContainsString('Appointment Confirmed', $html);
        $this->assertStringContainsString('APT-2026-0001', $html);
        $this->assertStringContainsString('Juan', $html);
    }

    public function test_booking_magic_link_mailable_renders_and_is_sendable(): void
    {
        Mail::fake();

        $customer = new Customer([
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
            'email'      => 'maria@example.com',
        ]);

        Mail::to($customer->email)->send(
            new BookingMagicLink($customer, 'https://app.fixitautoservices.com/booking/auth?token=abc123')
        );

        Mail::assertSent(BookingMagicLink::class, fn ($mail) => $mail->hasTo('maria@example.com'));

        $html = (new BookingMagicLink($customer, 'https://app.fixitautoservices.com/booking/auth?token=abc123'))->render();
        $this->assertStringContainsString('Your Booking Login Link', $html);
        $this->assertStringContainsString('Maria', $html);
        $this->assertStringContainsString('token=abc123', $html);
    }
}
