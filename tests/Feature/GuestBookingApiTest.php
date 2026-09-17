<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingToken;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P2 Booking API: guest booking without an account (blueprint = email + phone
 * only, no login). The public website form posts name/email/phone + vehicle
 * details + service request + date/time; the API must create the guest customer
 * + vehicle on the fly and record the appointment with status: scheduled.
 */
class GuestBookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_booking_creates_customer_vehicle_and_appointment(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/api/booking/customer-booking', [
            // guest identity (no account)
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '09181234567',
            // guest vehicle details
            'vehicle_make' => 'Honda',
            'vehicle_model' => 'Civic',
            'vehicle_year' => 2019,
            'vehicle_plate' => 'XYZ-999',
            // service request + schedule
            'service_type' => 'aircon_service',
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '11:00',
            'notes' => 'AC not cooling',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('customers', [
            'email' => 'jane@example.com',
            'phone' => '09181234567',
        ]);

        $customer = Customer::where('email', 'jane@example.com')->first();
        $this->assertNotNull($customer);

        $this->assertDatabaseHas('vehicles', [
            'customer_id' => $customer->id,
            'make' => 'Honda',
            'license_plate' => 'XYZ-999',
        ]);

        $vehicle = Vehicle::where('customer_id', $customer->id)
            ->where('license_plate', 'XYZ-999')->first();
        $this->assertNotNull($vehicle);

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'scheduled',
            'booking_source' => 'website',
        ]);

        $appointment = Appointment::where('customer_id', $customer->id)->first();
        $this->assertNotNull($appointment);

        // A booking token must be generated and returned for this guest booking.
        $this->assertNotNull($response->json('appointment.token'));
        $this->assertNotNull(
            BookingToken::where('appointment_id', $appointment->id)->first()
        );
    }

    public function test_guest_booking_requires_email_and_phone(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/api/booking/customer-booking', [
            'name' => 'No Contact',
            'service_type' => 'engine_service',
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '09:00',
        ]);

        // Missing email + phone = guest lead cannot be formed.
        $this->assertTrue(
            in_array($response->status(), [422, 400, 401]),
            'Guest booking without email+phone must be rejected (got ' . $response->status() . ')'
        );
    }
}
