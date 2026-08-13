<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD (red -> green) for the UNCHECKED items of the "Fixit Adjustment -
 * Create Appointment" card -> "Appointment Details" checklist:
 *
 * 1. Remove the Priority features/field from the create form.
 * 2. Customer/Client: allow typing a NEW name, not just the dropdown.
 * 3. Car Brand: all brands listed, typeable, filtered as you type.
 * 4. Car Model: all models but only for the selected brand, typeable.
 * 5. Add Plate Number to the appointment details (vehicle section).
 */
class AppointmentDetailsUncheckedItemsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    // ===================================================================
    // ITEM 1: Remove Priority Features
    // ===================================================================

    /** @test */
    public function create_page_does_not_render_priority_field()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertDontSee('name="priority"', false);
    }

    /** @test */
    public function appointment_created_without_priority_defaults_to_normal()
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->admin)
            ->post('/appointments', [
                'customer_id' => $customer->id,
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Vios',
                'vehicle_year' => 2021,
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '10:00',
                'service_type' => ['preventive_maintenance'],
            ])
            ->assertRedirect(route('appointments.index'));

        $appointment = Appointment::where('customer_id', $customer->id)->first();
        $this->assertNotNull($appointment);
        $this->assertEquals('normal', $appointment->priority, 'appointment without priority must default to normal');
    }

    // ===================================================================
    // ITEM 2: Customer/Client typeable new name
    // ===================================================================

    /** @test */
    public function create_page_renders_typeable_customer_input_with_existing_customers()
    {
        $existing = Customer::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('customer_search', false) // typeable input
            ->assertSee('Juan Dela Cruz', false); // existing customer offered
    }

    /** @test */
    public function create_page_keeps_manual_add_client_name_field_for_new_customers()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('name="client_name"', false);
    }

    // ===================================================================
    // ITEM 3: Car Brand typeable + filtered list of all brands
    // ===================================================================

    /** @test */
    public function create_page_lists_all_active_brands_for_picking_and_typing()
    {
        VehicleBrand::create(['name' => 'Toyota', 'is_active' => true]);
        VehicleBrand::create(['name' => 'Honda', 'is_active' => true]);
        VehicleBrand::create(['name' => 'Isuzu', 'is_active' => false]); // inactive must NOT be listed

        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('Toyota', false)
            ->assertSee('Honda', false)
            ->assertDontSee('Isuzu', false);
    }

    /** @test */
    public function brand_input_remains_free_text_even_if_not_in_list()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('name="vehicle_brand"', false)
            ->assertSee('type="text"', false);
    }

    // ===================================================================
    // ITEM 4: Car Model dependent on selected brand
    // ===================================================================

    /** @test */
    public function create_page_renders_models_with_brand_association()
    {
        $toyota = VehicleBrand::create(['name' => 'Toyota', 'is_active' => true]);
        $honda = VehicleBrand::create(['name' => 'Honda', 'is_active' => true]);

        VehicleModel::create(['vehicle_brand_id' => $toyota->id, 'name' => 'Vios', 'is_active' => true]);
        VehicleModel::create(['vehicle_brand_id' => $toyota->id, 'name' => 'Fortuner', 'is_active' => true]);
        VehicleModel::create(['vehicle_brand_id' => $honda->id, 'name' => 'Civic', 'is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk();

        // The page must expose model data keyed by brand so the JS can filter.
        $response->assertSee('Vios', false)
            ->assertSee('Fortuner', false)
            ->assertSee('Civic', false)
            ->assertSee('modelsByBrand', false); // JS data hook
    }

    /** @test */
    public function model_input_remains_free_text()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('name="vehicle_model"', false);
    }

    // ===================================================================
    // ITEM 5: Plate number in appointment details (vehicle section)
    // ===================================================================

    /** @test */
    public function create_page_renders_plate_number_field_in_vehicle_details()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('name="plate_number"', false);
    }

    /** @test */
    public function admin_form_saves_plate_number_to_vehicle()
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->admin)
            ->post('/appointments', [
                'customer_id' => $customer->id,
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Vios',
                'vehicle_year' => 2021,
                'plate_number' => 'abc-1234',
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '10:00',
                'service_type' => ['preventive_maintenance'],
            ])
            ->assertRedirect(route('appointments.index'));

        $vehicle = Vehicle::where('customer_id', $customer->id)->first();
        $this->assertNotNull($vehicle);
        $this->assertEquals('ABC-1234', $vehicle->license_plate, 'admin form plate number must be saved (uppercased)');
    }
}
