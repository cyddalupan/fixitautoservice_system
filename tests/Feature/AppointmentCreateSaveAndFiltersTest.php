<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD (red -> green) for the three reported regressions:
 *
 * 1. /appointments/create must SAVE properly (currently validation always
 *    fails because the admin form schema — customer_id/vehicle_make/priority/
 *    assigned_to — doesn't match store()'s guest-booking schema of
 *    client_name/contact_no/vehicle_brand) and redirect to /appointments.
 * 2. The save button (sticky save bar) must never disappear.
 * 3. /appointments must filter by status, date range, customer, and vehicle.
 */
class AppointmentCreateSaveAndFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    private function makeAppointment(array $overrides = []): Appointment
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

        return Appointment::factory()->create(array_merge([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_number' => 'APT-' . random_int(1000, 9999),
        ], $overrides));
    }

    /** @test */
    public function admin_form_saves_appointment_and_redirects_to_appointments()
    {
        $customer = Customer::factory()->create();
        $technician = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->post('/appointments', [
                'customer_id' => $customer->id,
                'vehicle_make' => 'Honda',
                'vehicle_model' => 'Civic',
                'vehicle_year' => 2020,
                'appointment_date' => now()->addDays(2)->toDateString(),
                'appointment_time' => '10:30',
                'priority' => 'high',
                'service_type' => ['preventive_maintenance'],
                'assigned_to' => $technician->id,
                'description' => 'PMS check and oil change',
                'estimated_cost' => 2500,
                'notes' => 'internal admin note',
            ])
            ->assertRedirect(route('appointments.index'));

        $saved = Appointment::where('customer_id', $customer->id)->first();
        $this->assertNotNull($saved, 'appointment was not saved');
        $this->assertEquals(now()->addDays(2)->toDateString(), $saved->appointment_date->toDateString());
        $this->assertEquals('10:30', $saved->appointment_time);
        $this->assertEquals('high', $saved->priority);
        $this->assertEquals($technician->id, $saved->assigned_technician_id);
        $this->assertEquals('scheduled', $saved->appointment_status);
        $this->assertEquals('admin_panel', $saved->booking_source);

        $this->assertDatabaseHas('vehicles', [
            'customer_id' => $customer->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2020,
        ]);
    }

    /** @test */
    public function guest_booking_schema_still_works_after_admin_schema_change()
    {
        $this->actingAs($this->admin)
            ->post('/appointments', [
                'client_name' => 'Jane Doe',
                'contact_no' => '09181234567',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'Civic',
                'vehicle_year' => 2019,
                'plate_number' => 'XYZ-999',
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '11:00',
                'service_type' => ['preventive_maintenance'],
            ])
            ->assertRedirect(route('appointments.index'));

        $this->assertDatabaseHas('customers', ['phone' => '09181234567']);
        $saved = Appointment::whereDate('appointment_date', now()->addDays(3)->toDateString())->first();
        $this->assertNotNull($saved, 'guest appointment was not saved');
        $this->assertEquals('admin_panel', $saved->booking_source);
    }

    /** @test */
    public function create_page_renders_sticky_save_bar_with_submit_button()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('sticky-save-bar', false)
            ->assertSee('Create Appointment', false);
    }

    /** @test */
    public function index_filters_appointments_by_status()
    {
        $scheduled = $this->makeAppointment(['appointment_status' => 'scheduled']);
        $cancelled = $this->makeAppointment(['appointment_status' => 'cancelled']);

        $this->actingAs($this->admin)
            ->get('/appointments?status=cancelled')
            ->assertOk()
            ->assertSee($cancelled->appointment_number, false)
            ->assertDontSee($scheduled->appointment_number, false);
    }

    /** @test */
    public function index_filters_appointments_by_date_range()
    {
        $inRange = $this->makeAppointment([
            'appointment_status' => 'scheduled',
            'appointment_date' => now()->addDays(3)->toDateString(),
        ]);
        $outOfRange = $this->makeAppointment([
            'appointment_status' => 'scheduled',
            'appointment_date' => now()->addDays(6)->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->get('/appointments?' . http_build_query([
                'date_from' => now()->addDays(2)->toDateString(),
                'date_to' => now()->addDays(4)->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($inRange->appointment_number, false)
            ->assertDontSee($outOfRange->appointment_number, false);
    }

    /** @test */
    public function index_filters_appointments_by_customer()
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();
        $vehicleA = Vehicle::factory()->create(['customer_id' => $customerA->id]);
        $vehicleB = Vehicle::factory()->create(['customer_id' => $customerB->id]);

        $forA = $this->makeAppointment([
            'customer_id' => $customerA->id,
            'vehicle_id' => $vehicleA->id,
            'appointment_status' => 'scheduled',
        ]);
        $forB = $this->makeAppointment([
            'customer_id' => $customerB->id,
            'vehicle_id' => $vehicleB->id,
            'appointment_status' => 'scheduled',
        ]);

        $this->actingAs($this->admin)
            ->get('/appointments?customer_id=' . $customerA->id)
            ->assertOk()
            ->assertSee($forA->appointment_number, false)
            ->assertDontSee($forB->appointment_number, false);
    }

    /** @test */
    public function index_filters_appointments_by_vehicle()
    {
        $customer = Customer::factory()->create();
        $vehicleA = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $vehicleB = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $withA = $this->makeAppointment([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicleA->id,
            'appointment_status' => 'scheduled',
        ]);
        $withB = $this->makeAppointment([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicleB->id,
            'appointment_status' => 'scheduled',
        ]);

        $this->actingAs($this->admin)
            ->get('/appointments?vehicle_id=' . $vehicleA->id)
            ->assertOk()
            ->assertSee($withA->appointment_number, false)
            ->assertDontSee($withB->appointment_number, false);
    }
}
