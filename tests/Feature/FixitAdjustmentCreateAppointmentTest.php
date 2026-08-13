<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD (red -> green) for "Fixit Adjustment - Create Appointment" card (top 3 items):
 *
 * 1. Admin create page must have a MANUAL ADD fill-up form:
 *    Client Name, Contact No., Car Brand, Car Model, Car Year,
 *    Plate Number, Preferred Time and Date.
 * 2. (Contrast fix covered by tests/Unit/SectionNavContrastTest.php)
 * 3. Contact Number field must exist on the admin create page and be saved.
 */
class FixitAdjustmentCreateAppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    // ---------------------------------------------------------------
    // ITEM 1: Manual admin add fill-up form fields exist on the page
    // ---------------------------------------------------------------

    /** @test */
    public function create_page_renders_manual_add_client_name_field()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('name="client_name"', false);
    }

    /** @test */
    public function create_page_renders_manual_add_contact_number_field()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('name="contact_no"', false);
    }

    /** @test */
    public function create_page_renders_manual_add_vehicle_brand_field()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('name="vehicle_brand"', false);
    }

    /** @test */
    public function create_page_renders_manual_add_vehicle_model_field()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('name="vehicle_model"', false);
    }

    /** @test */
    public function create_page_renders_manual_add_vehicle_year_field()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('name="vehicle_year"', false);
    }

    /** @test */
    public function create_page_renders_manual_add_plate_number_field()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('name="plate_number"', false);
    }

    // ---------------------------------------------------------------
    // ITEM 1 + 3: Manual add creates appointment, customer, vehicle
    // ---------------------------------------------------------------

    /** @test */
    public function manual_add_creates_appointment_customer_and_vehicle()
    {
        $this->actingAs($this->admin)
            ->post('/appointments', [
                'client_name' => 'Juan Dela Cruz',
                'contact_no' => '09171234567',
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Vios',
                'vehicle_year' => 2021,
                'plate_number' => 'abc-1234',
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '14:00',
                'service_type' => ['preventive_maintenance'],
                'description' => 'PMS check',
            ])
            ->assertRedirect(route('appointments.index'));

        $customer = Customer::where('phone', '09171234567')->first();
        $this->assertNotNull($customer, 'customer was not created from manual add');
        $this->assertEquals('Juan', $customer->first_name);
        $this->assertEquals('Dela Cruz', $customer->last_name);

        $vehicle = Vehicle::where('customer_id', $customer->id)->first();
        $this->assertNotNull($vehicle, 'vehicle was not created from manual add');
        $this->assertEquals('Toyota', $vehicle->make);
        $this->assertEquals('Vios', $vehicle->model);
        $this->assertEquals(2021, $vehicle->year);
        $this->assertEquals('ABC-1234', $vehicle->license_plate, 'plate number must be saved (uppercased)');

        $appointment = Appointment::where('customer_id', $customer->id)->first();
        $this->assertNotNull($appointment, 'appointment was not created from manual add');
        $this->assertEquals('admin_panel', $appointment->booking_source);
        $this->assertStringContainsString('ABC-1234', $appointment->vehicle_description);
    }

    /** @test */
    public function manual_add_contact_number_is_optional()
    {
        $this->actingAs($this->admin)
            ->post('/appointments', [
                'client_name' => 'Juan Dela Cruz',
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Vios',
                'vehicle_year' => 2021,
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '14:00',
                'service_type' => ['preventive_maintenance'],
            ])
            ->assertSessionDoesntHaveErrors('contact_no')
            ->assertRedirect(route('appointments.index'));

        $customer = Customer::where('first_name', 'Juan')->where('last_name', 'Dela Cruz')->first();
        $this->assertNotNull($customer, 'customer must still be created without a contact number');
        $this->assertNull($customer->phone);
    }

    /** @test */
    public function manual_add_keeps_admin_priority_technician_and_notes()
    {
        $technician = User::factory()->create([
            'role' => 'technician',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->post('/appointments', [
                'client_name' => 'Maria Santos',
                'contact_no' => '09185551234',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'Civic',
                'vehicle_year' => 2019,
                'appointment_date' => now()->addDays(4)->toDateString(),
                'appointment_time' => '09:30',
                'service_type' => ['preventive_maintenance'],
                'priority' => 'high',
                'assigned_to' => $technician->id,
                'notes' => 'admin internal note',
            ])
            ->assertRedirect(route('appointments.index'));

        $appointment = Appointment::whereHas('customer', fn ($q) => $q->where('phone', '09185551234'))->first();
        $this->assertNotNull($appointment);
        $this->assertEquals('high', $appointment->priority, 'manual add must keep admin-selected priority');
        $this->assertEquals($technician->id, $appointment->assigned_technician_id, 'manual add must keep technician assignment');
        $this->assertEquals('admin internal note', $appointment->customer_notes, 'manual add must keep admin notes');
    }

    // ---------------------------------------------------------------
    // Regression: existing customer dropdown flow must keep working
    // ---------------------------------------------------------------

    /** @test */
    public function existing_customer_dropdown_flow_still_works()
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->admin)
            ->post('/appointments', [
                'customer_id' => $customer->id,
                'vehicle_make' => 'Mitsubishi',
                'vehicle_model' => 'Mirage',
                'vehicle_year' => 2022,
                'appointment_date' => now()->addDays(5)->toDateString(),
                'appointment_time' => '11:00',
                'service_type' => ['preventive_maintenance'],
            ])
            ->assertRedirect(route('appointments.index'));

        $this->assertNotNull(Appointment::where('customer_id', $customer->id)->first());
    }
}
