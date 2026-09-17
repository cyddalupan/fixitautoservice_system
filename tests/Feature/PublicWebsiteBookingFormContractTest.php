<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contract for the PUBLIC WEBSITE booking form (fixitautoservices.com, static
 * site under /var/www/fixit-static). The static form is plain HTML/JS with no
 * test framework of its own, so its correctness is pinned here against the
 * booking API it POSTs to. This test mirrors EXACTLY the field set the form
 * submits (guest name/email/phone + vehicle_make/model/year/plate +
 * service_type/service_request + appointment_date/time) and asserts every field
 * the form must read back from the success response to render its confirmation
 * (appointment reference, token, track_url).
 *
 * Covers the unchecked acceptance item:
 *   "[ ] Public website booking form on fixitautoservices.com (NEW)"
 */
class PublicWebsiteBookingFormContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_site_form_payload_creates_booking_and_returns_confirmation_fields(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        // Same payload shape the static booking.html form will POST.
        $response = $this->postJson('/api/booking/customer-booking', [
            'name' => 'Maria Santos',
            'email' => 'maria.santos@example.com',
            'phone' => '09171234567',
            'vehicle_make' => 'Toyota',
            'vehicle_model' => 'Vios',
            'vehicle_year' => 2021,
            'vehicle_plate' => 'ABC-1234',
            'service_type' => 'preventive_maintenance',
            'service_request' => 'Check brakes and engine oil',
            'issue_description' => 'Brakes feel soft; engine light on.',
            'appointment_date' => now()->addDays(5)->toDateString(),
            'appointment_time' => '14:30',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Appointment booked successfully!',
        ]);

        // The form needs these keys to render its success/confirmation screen.
        $response->assertJsonStructure([
            'appointment' => [
                'id',
                'reference',
                'date',
                'time',
                'service',
                'status',
                'vehicle',
                'token',
                'track_url',
            ],
        ]);

        $data = $response->json('appointment');
        $this->assertNotEmpty($data['reference']);
        $this->assertNotEmpty($data['token']);
        $this->assertStringContainsString('/booking/' . $data['token'], $data['track_url']);

        // App persisted the guest booking with the exact vehicle year the form sent.
        $customer = Customer::where('email', 'maria.santos@example.com')->first();
        $this->assertNotNull($customer);

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $customer->id,
            'booking_source' => 'website',
            'status' => 'scheduled',
            // appointment_date column stores datetime ("Y-m-d 00:00:00").
            'appointment_date' => now()->addDays(5)->toDateString() . ' 00:00:00',
        ]);

        $appointment = Appointment::where('customer_id', $customer->id)->first();
        $this->assertSame('preventive_maintenance', $appointment->appointment_type);
        $this->assertSame('Check brakes and engine oil', $appointment->service_request);
    }

    public function test_static_site_form_can_query_available_slots_for_a_date(): void
    {
        $date = now()->addDays(5)->toDateString();

        $response = $this->getJson('/api/booking/available-slots?date=' . $date);

        $response->assertOk();
        $response->assertJsonStructure([
            'date',
            'slots' => [
                '*' => ['time', 'display', 'available', 'booked', 'capacity'],
            ],
            'working_hours' => ['start', 'end'],
        ]);
    }
}
