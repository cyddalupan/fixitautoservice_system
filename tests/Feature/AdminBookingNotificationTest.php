<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Mail\NewBookingAdminNotification;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class AdminBookingNotificationTest extends TestCase
{
    /**
     * Blueprint P2 requirement: when a client books an appointment, the
     * system notifies the admin (email) of the new booking.
     *
     * Test uses Mail::fake() (array transport) so no SMTP server is needed.
     * Verifies the notification is triggered (sendability), not live delivery.
     */
    public function test_guest_booking_sends_admin_notification_email(): void
    {
        Config::set('mail.admin_recipients', ['admin@fixitautoservices.com']);
        Mail::fake();

        $response = $this->postJson('/api/booking/customer-booking', [
            'name'             => 'Juan Dela Cruz',
            'email'            => 'juan@example.com',
            'phone'            => '09171234567',
            'vehicle_make'     => 'Toyota',
            'vehicle_model'    => 'Vios',
            'vehicle_year'     => 2020,
            'vehicle_plate'    => 'ABC 1234',
            'service_type'     => 'preventive_maintenance',
            'service_request'  => 'Please check brakes too.',
            'appointment_date' => '2026-08-15',
            'appointment_time' => '09:30',
        ]);

        $response->assertOk();

        // An admin-notification mailable was sent to the admin recipient(s).
        Mail::assertSent(NewBookingAdminNotification::class, function ($mail) {
            return $mail->hasTo('admin@fixitautoservices.com');
        });
    }

    /**
     * The admin notification mailable renders without error and carries the
     * appointment reference so the admin can click through.
     */
    public function test_admin_notification_mailable_renders_with_appointment_details(): void
    {
        $customer = new Customer([
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'email'      => 'juan@example.com',
            'phone'      => '09171234567',
        ]);

        $appointment = new Appointment([
            'appointment_number' => 'APT-2026-0001',
            'appointment_type'   => 'preventive_maintenance',
            'status'             => 'scheduled',
        ]);

        $html = (new NewBookingAdminNotification($appointment, $customer))->render();

        $this->assertStringContainsString('New Appointment', $html);
        $this->assertStringContainsString('APT-2026-0001', $html);
        $this->assertStringContainsString('Juan', $html);
    }
}
