<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Appointments card — "Appointment generates a LEAD (email + contact number required)"
 * + "Form fields: name, email, contact number, vehicle info, service/issue description, date/time".
 *
 * Blueprint lead rule: an appointment booking is a guest lead formed from
 * email + contact number. The public booking API must (1) enforce BOTH email
 * and phone are present (strict 422), and (2) persist all public form fields
 * (name, email, phone, vehicle, service/issue description, date/time) onto the
 * appointment/lead.
 */
class AppointmentLeadContractTest extends TestCase
{
    use RefreshDatabase;

    private function bookingPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ramon Bautista',
            'email' => 'ramon@example.com',
            'phone' => '09175551234',
            'vehicle_make' => 'Mitsubishi',
            'vehicle_model' => 'L300',
            'vehicle_year' => 2018,
            'vehicle_plate' => 'PLT-101',
            'service_type' => 'engine_service',
            'service_request' => 'Engine knocking and rough idle since last week',
            'appointment_date' => now()->addDays(4)->toDateString(),
            'appointment_time' => '10:30',
        ], $overrides);
    }

    public function test_missing_contact_number_rejected_as_strict_422(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/api/booking/customer-booking', [
            'name' => 'No Phone',
            'email' => 'nophone@example.com',
            'vehicle_make' => 'Honda',
            'vehicle_model' => 'City',
            'vehicle_plate' => 'NP-001',
            'service_type' => 'engine_service',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'appointment_time' => '09:00',
        ]);
        
        // Strict contract: missing contact number => validation 422 (not 200).
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('phone');
    }

    public function test_missing_email_rejected_as_strict_422(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/api/booking/customer-booking', [
            'name' => 'No Email',
            'phone' => '09170001111',
            'vehicle_make' => 'Toyota',
            'vehicle_model' => 'Innova',
            'vehicle_plate' => 'NE-002',
            'service_type' => 'aircon_service',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'appointment_time' => '14:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_all_public_form_fields_persist_onto_lead_and_appointment(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Testing\WithoutMiddleware::class);
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $payload = $this->bookingPayload();

        $response = $this->postJson('/api/booking/customer-booking', $payload);
        $response->assertOk();
        $response->assertJson(['success' => true]);

        // 1) Lead/customer formed from email + contact number.
        $this->assertDatabaseHas('customers', [
            'email' => 'ramon@example.com',
            'phone' => '09175551234',
        ]);

        $customer = Customer::where('email', 'ramon@example.com')->first();
        $this->assertNotNull($customer);

        // 2) Vehicle info persisted on the vehicle.
        $this->assertDatabaseHas('vehicles', [
            'customer_id' => $customer->id,
            'make' => 'Mitsubishi',
            'model' => 'L300',
            'license_plate' => 'PLT-101',
        ]);

        $appointment = Appointment::where('customer_id', $customer->id)->first();
        $this->assertNotNull($appointment);

        // 3) Service/issue description persisted (blueprint: user describes issue upfront).
        $this->assertSame('Engine knocking and rough idle since last week', $appointment->service_request);

        // 4) Preferred date/time persisted.
        $this->assertSame($payload['appointment_date'], $appointment->appointment_date->format('Y-m-d'));
        $this->assertSame('10:30', substr($appointment->appointment_time, 0, 5));

        // 5) Booking source + blueprint status.
        $this->assertSame('website', $appointment->booking_source);
        $this->assertSame('scheduled', $appointment->status);
    }
}
