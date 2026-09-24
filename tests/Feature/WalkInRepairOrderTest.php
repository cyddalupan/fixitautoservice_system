<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Walk-in Repair Order intake (2026-09-24).
 *
 * The Repair Order create page now mirrors the Appointment "Update Information"
 * form: it can create the customer / vehicle inline, records the date received,
 * and tags the transaction as a walk-in when it did not come from a schedule.
 */
class WalkInRepairOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
        $this->admin->forceFill(['is_active' => true])->save();
    }

    /** @test */
    public function walk_in_intake_creates_customer_vehicle_and_tags_source()
    {
        $response = $this->actingAs($this->admin)->post('/repair-orders', [
            'inspection_type' => ['routine'],
            // No customer_id / vehicle_id -> brand new walk-in
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'phone' => '09171234567',
            'make' => 'Toyota',
            'model' => 'Vios',
            'year' => '2019',
            'license_plate' => 'ABC 1234',
            'date_received' => '2026-09-24',
        ]);

        $inspection = VehicleInspection::latest('id')->first();
        $this->assertNotNull($inspection, 'Repair Order was not created');
        $response->assertRedirect(route('inspections.show', $inspection));

        $this->assertSame('walk_in', $inspection->source);
        $this->assertTrue($inspection->is_walk_in);
        $this->assertSame('2026-09-24', $inspection->date_received->format('Y-m-d'));

        $customer = Customer::find($inspection->customer_id);
        $this->assertSame('Juan', $customer->first_name);
        $this->assertNull($customer->email, 'Walk-in customer without email should be allowed');

        $vehicle = Vehicle::find($inspection->vehicle_id);
        $this->assertSame('Toyota', $vehicle->make);
        $this->assertSame($customer->id, $vehicle->customer_id);
    }

    /** @test */
    public function create_page_renders_with_walk_in_intake_card()
    {
        $response = $this->actingAs($this->admin)->get('/repair-orders/create');

        $response->assertOk();
        // The create page mirrors the Appointment "Update Information" form 1:1.
        foreach (['Receipt', 'Customer', 'Vehicle', 'Services', 'Job Description', 'Parts / Supplies', 'Discount', 'Concern / Request'] as $section) {
            $response->assertSee($section, false);
        }
        $response->assertSee('name="first_name"', false);
        $response->assertSee('name="date_received"', false);
        $response->assertSee('name="service_types[]"', false);
        $response->assertSee('name="job_description_items[', false);
        $response->assertSee('name="parts_items[', false);
    }

    /** @test */
    public function walk_in_intake_persists_services_job_description_parts_and_discount()
    {
        $this->actingAs($this->admin)->post('/repair-orders', [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'phone' => '09170000000',
            'make' => 'Honda',
            'model' => 'City',
            'year' => '2021',
            'date_received' => '2026-09-24',
            'service_types' => ['preventive_maintenance', 'aircon_cleaning'],
            'job_description_items' => [
                ['description' => 'PMS', 'mh' => 2, 'unit_price' => 500, 'labor_cost' => 1000],
                ['description' => '', 'mh' => '', 'unit_price' => '', 'labor_cost' => ''], // empty row dropped
            ],
            'parts_items' => [
                ['description' => 'Oil Filter', 'qty' => 1, 'unit_price' => 450, 'cost' => 450],
            ],
            'discount' => 100,
            'customer_concerns' => 'Maingay ang aircon.',
        ])->assertRedirect();

        $inspection = VehicleInspection::latest('id')->first();
        $this->assertSame('walk_in', $inspection->source);
        $this->assertSame(['preventive_maintenance', 'aircon_cleaning'], $inspection->service_types);
        $this->assertCount(1, $inspection->job_description_items, 'Blank job-description rows should be dropped');
        $this->assertSame('PMS', $inspection->job_description_items[0]['description']);
        $this->assertSame(450.0, (float) $inspection->parts_items[0]['cost']);
        $this->assertSame('100.00', (string) $inspection->discount);
        $this->assertSame('Maingay ang aircon.', $inspection->customer_concerns);

        // Totals read the Repair Order's own items (no appointment needed).
        $this->assertSame(1000.0, $inspection->repair_labor_total);
        $this->assertSame(450.0, $inspection->repair_parts_total);
        $this->assertSame(1350.0, $inspection->repair_total);
    }

    /** @test */
    public function walk_in_intake_without_discount_defaults_to_zero_and_does_not_500()
    {
        // Regression: vehicle_inspections.discount is NOT NULL (default 0); a blank
        // field arrives as null and previously caused "Column 'discount' cannot be null".
        $this->actingAs($this->admin)->post('/repair-orders', [
            'first_name' => 'Pedro',
            'last_name' => 'Reyes',
            'phone' => '09180000000',
            'make' => 'Mitsubishi',
            'model' => 'Montero',
            'year' => '2020',
            'date_received' => '2026-09-24',
            'discount' => '',
            'service_types' => ['preventive_maintenance'],
            'job_description_items' => [['description' => 'PMS', 'mh' => 1, 'unit_price' => 0, 'labor_cost' => 500]],
            'parts_items' => [['description' => '', 'qty' => '', 'unit_price' => '', 'cost' => '']],
        ])->assertRedirect();

        $inspection = VehicleInspection::latest('id')->first();
        $this->assertNotNull($inspection);
        $this->assertSame('0.00', (string) $inspection->discount);
        $this->assertSame('walk_in', $inspection->source);
        $this->assertCount(0, $inspection->parts_items, 'Blank parts rows should be dropped');
    }

    /** @test */
    public function repair_order_linked_to_appointment_is_tagged_scheduled()
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $this->actingAs($this->admin)->post('/repair-orders', [
            'inspection_type' => ['routine'],
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'appointment_id' => $appointment->id,
            // The active appointment trips the duplicate-active-transaction guard
            // (pre-existing behaviour); the create page resolves it via this flag.
            'override_duplicate' => '1',
        ]);

        $inspection = VehicleInspection::latest('id')->first();
        $this->assertSame('scheduled', $inspection->source);
        $this->assertFalse($inspection->is_walk_in);
    }

    /** @test */
    public function walk_in_intake_without_any_name_is_rejected_with_a_friendly_error()
    {
        $response = $this->actingAs($this->admin)->post('/repair-orders', [
            'inspection_type' => ['routine'],
            'make' => 'Toyota',
            'model' => 'Vios',
        ]);

        $response->assertSessionHasErrors('customer_id');
        $this->assertNull(VehicleInspection::latest('id')->first());
    }

    /** @test */
    public function selecting_existing_customer_fills_details_without_creating_a_new_one()
    {
        $customer = Customer::factory()->create(['phone' => null]);
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $countBefore = Customer::count();

        $this->actingAs($this->admin)->post('/repair-orders', [
            'inspection_type' => ['routine'],
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'phone' => '09998887777', // fill in a missing detail
        ]);

        $this->assertSame($countBefore, Customer::count(), 'No new customer should be created');
        $this->assertSame('09998887777', $customer->fresh()->phone);
    }
}
