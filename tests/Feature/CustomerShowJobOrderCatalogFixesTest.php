<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\JobOrder;
use App\Models\JobOrderItem;
use App\Models\ServiceItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD fixes for:
 *  1. Customer show page 500 ("Call to a member function count() on string" —
 *     the customers.notes COLUMN shadows the notes() RELATION).
 *  2. Job-orders CRUD not saving (create form missing required fields:
 *     job_order_date, job_order_type, priority) + service catalog data should
 *     appear in job-orders.
 *  3. Catalog (/service-items) CRUD works + discoverable navigation link.
 */
class CustomerShowJobOrderCatalogFixesTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    // ─── 1. Customer show page 500 ───

    public function test_customer_show_page_renders_when_notes_column_has_value()
    {
        $customer = Customer::factory()->create(['notes' => 'nothing special']);

        $response = $this->actingAs($this->admin)->get("/customers/{$customer->id}");

        $response->assertOk();
        $response->assertSee('nothing special');
    }

    public function test_customer_show_page_displays_customer_notes_relation_records()
    {
        $customer = Customer::factory()->create();
        CustomerNote::create([
            'customer_id' => $customer->id,
            'note_type'   => 'general',
            'content'     => 'Prefers phone calls in the afternoon',
            'user_id'     => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/customers/{$customer->id}");

        $response->assertOk();
        $response->assertSee('Prefers phone calls in the afternoon');
    }

    public function test_customer_notes_route_renders()
    {
        $customer = Customer::factory()->create();
        CustomerNote::create([
            'customer_id' => $customer->id,
            'note_type'   => 'general',
            'content'     => 'Follow up on quote',
            'user_id'     => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/customers/{$customer->id}/notes");

        $response->assertOk();
    }

    // ─── 2. Job-orders CRUD ───

    public function test_job_order_create_page_contains_required_fields()
    {
        $customer = Customer::factory()->create();
        Vehicle::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($this->admin)->get('/job-orders/create');

        $response->assertOk();
        $response->assertSee('name="job_order_date"', false);
        $response->assertSee('name="job_order_type"', false);
        $response->assertSee('name="priority"', false);
    }

    public function test_job_order_create_page_lists_service_catalog_items()
    {
        $customer = Customer::factory()->create();
        Vehicle::factory()->create(['customer_id' => $customer->id]);
        ServiceItem::create([
            'name'           => 'Full Engine Tune-Up',
            'retail_price'   => 2500.00,
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/job-orders/create');

        $response->assertOk();
        $response->assertSee('Full Engine Tune-Up');
    }

    public function test_job_order_can_be_stored_with_service_catalog_items()
    {
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $advisor  = User::factory()->create(['role' => 'service_advisor']);

        $response = $this->actingAs($this->admin)->post('/job-orders', [
            'customer_id'        => $customer->id,
            'vehicle_id'         => $vehicle->id,
            'service_advisor_id' => $advisor->id,
            'job_order_date'     => now()->toDateString(),
            'job_order_type'     => 'repair',
            'priority'           => 'normal',
            'customer_concerns'  => 'Engine noise when accelerating',
            'items'              => [
                [
                    'item_type'   => 'labor',
                    'description' => 'Full Engine Tune-Up',
                    'quantity'    => 1,
                    'unit_cost'   => 2500.00,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('job_orders', [
            'customer_id'       => $customer->id,
            'vehicle_id'        => $vehicle->id,
            'job_order_type'    => 'repair',
            'priority'          => 'normal',
            'job_order_status'  => 'pending',
        ]);
        $this->assertDatabaseHas('job_order_items', [
            'description' => 'Full Engine Tune-Up',
            'unit_cost'   => 2500.00,
        ]);
    }

    public function test_job_order_edit_page_has_valid_job_order_type_options()
    {
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $advisor  = User::factory()->create(['role' => 'service_advisor']);
        $jobOrder = JobOrder::factory()->create([
            'customer_id'        => $customer->id,
            'vehicle_id'         => $vehicle->id,
            'service_advisor_id' => $advisor->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/job-orders/{$jobOrder->id}/edit");

        $response->assertOk();
        $response->assertSee('value="repair"', false);
        $response->assertSee('value="maintenance"', false);
        $response->assertDontSee('value="standard"', false);
    }

    // ─── 3. Service catalog CRUD + navigation ───

    public function test_service_catalog_index_renders()
    {
        ServiceItem::create(['name' => 'Oil Change', 'retail_price' => 500.00, 'is_active' => true]);

        $response = $this->actingAs($this->admin)->get('/service-items');

        $response->assertOk();
        $response->assertSee('Oil Change');
    }

    public function test_service_catalog_can_create_update_and_delete()
    {
        // Create
        $response = $this->actingAs($this->admin)->post('/service-items', [
            'name'         => 'Brake Pad Replacement',
            'retail_price' => 1800.00,
            'is_active'    => 1,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('service_items', ['name' => 'Brake Pad Replacement']);

        $item = ServiceItem::where('name', 'Brake Pad Replacement')->first();

        // Update
        $response = $this->actingAs($this->admin)->put("/service-items/{$item->id}", [
            'name'         => 'Brake Pad Replacement (Front)',
            'retail_price' => 1900.00,
            'is_active'    => 1,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('service_items', ['name' => 'Brake Pad Replacement (Front)']);

        // Delete (soft delete — ServiceItem uses SoftDeletes)
        $response = $this->actingAs($this->admin)->delete("/service-items/{$item->id}");
        $response->assertSessionHasNoErrors();
        $this->assertSoftDeleted('service_items', ['name' => 'Brake Pad Replacement (Front)']);
    }

    public function test_sidebar_has_discoverable_service_catalog_link()
    {
        $response = $this->actingAs($this->admin)->get('/customers');

        $response->assertOk();
        $response->assertSee('Service Catalog');
        $response->assertSee('/service-items', false);
    }
}
