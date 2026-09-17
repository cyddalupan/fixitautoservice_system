<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Appointment;
use App\Models\BookingToken;

/**
 * Admin appointment routes under /admin prefix (blueprint requirement).
 * Red -> green TDD for the FixitRemake blueprint.
 */
class AdminAppointmentRoutesTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'email' => 'admin@fixit.test',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function admin_can_view_appointments_list_at_admin_prefix()
    {
        $this->actingAs($this->admin)
            ->get('/admin/appointments')
            ->assertStatus(200);
    }

    /** @test */
    public function admin_can_filter_appointments_by_status_query()
    {
        Appointment::factory()->create(['appointment_status' => 'scheduled']);
        Appointment::factory()->create(['appointment_status' => 'cancelled']);

        $this->actingAs($this->admin)
            ->get('/admin/appointments?status=scheduled')
            ->assertStatus(200)
            ->assertViewHas('appointments', function ($appointments) {
                return $appointments->count() === 1
                    && $appointments->first()->appointment_status === 'scheduled';
            });
    }

    /** @test */
    public function admin_can_view_cancelled_appointments_at_admin_prefix()
    {
        $this->actingAs($this->admin)
            ->get('/admin/appointments/cancelled')
            ->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_appointment_with_blueprint_fields()
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($this->admin)
            ->post('/admin/appointments', [
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'appointment_date' => now()->addDays(1)->format('Y-m-d'),
                'appointment_time' => '09:00',
            ])
            ->assertStatus(302);

        $this->assertDatabaseHas('appointments', [
            'customer_id' => $customer->id,
            'booking_source' => 'admin',
            'appointment_status' => 'scheduled',
        ]);
    }

    /** @test */
    public function admin_can_delete_appointment_at_admin_prefix()
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs($this->admin)
            ->delete('/admin/appointments/' . $appointment->id)
            ->assertStatus(302);

        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }

    /** @test */
    public function admin_created_appointment_gets_booking_token_for_client_flow()
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($this->admin)
            ->post('/admin/appointments', [
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'appointment_date' => now()->addDays(1)->format('Y-m-d'),
                'appointment_time' => '10:30',
            ])
            ->assertStatus(302);

        $appointment = Appointment::where('booking_source', 'admin')
            ->where('customer_id', $customer->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertDatabaseHas('booking_tokens', [
            'appointment_id' => $appointment->id,
        ]);

        $token = BookingToken::where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($token);
        $this->assertNotNull($token->token);
    }
}
