<?php

namespace Tests\Feature;

use App\Models\BlueprintService;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P6 requirement batch: service line-items on admin job orders.
 *
 *  - Add services from catalog (dropdown with default price)   [item 3]
 *  - Allow override of service price per line item             [item 4]
 *  - Set quantity per service                                  [item 5]
 *  - Can update: services, prices, technician, notes           [item 7]
 *  - Changes reflect on customer linked record                 [item 8]
 *
 * Written RED first (currently store/update persist no items).
 */
class AdminJobOrderServiceItemsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['email' => 'admin@fixit.test']);
    }

    private function makeCatalog(array $attr = []): BlueprintService
    {
        return BlueprintService::create(array_merge([
            'name'          => 'Oil Change',
            'description'   => 'Engine oil and filter change',
            'default_price' => 1500.00,
            'category'      => 'Maintenance',
            'is_active'     => true,
        ], $attr));
    }

    /** @test */
    public function store_persists_service_items_with_default_price_from_catalog()
    {
        $catalog  = $this->makeCatalog();
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);

        // item 3: add service from catalog, price defaults to catalog default_price
        $this->actingAs($this->admin)
            ->post('/admin/job-orders', [
                'customer_id'    => $customer->id,
                'vehicle_id'     => $vehicle->id,
                'job_order_date' => '2026-08-06',
                'job_order_status' => 'draft',
                'items' => [
                    ['catalog_id' => $catalog->id, 'quantity' => 1, 'unit_price' => null],
                ],
            ])
            ->assertRedirect(route('admin.job-orders.index'));

        $jobOrder = JobOrder::where('customer_id', $customer->id)->first();
        $this->assertNotNull($jobOrder);
        $this->assertSame(1, $jobOrder->items()->count());

        $item = $jobOrder->items()->first();
        $this->assertSame('Oil Change', $item->description);
        // default price applied from catalog
        $this->assertSame(1500.0, (float) $item->unit_cost);
        $this->assertSame(1500.0, (float) $item->total_cost);
    }

    /** @test */
    public function store_allows_price_override_per_line_item()
    {
        $catalog  = $this->makeCatalog(['default_price' => 1500.00]);
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);

        // item 4: override unit price (separate from catalog default)
        $this->actingAs($this->admin)
            ->post('/admin/job-orders', [
                'customer_id'    => $customer->id,
                'vehicle_id'     => $vehicle->id,
                'job_order_date' => '2026-08-06',
                'items' => [
                    ['catalog_id' => $catalog->id, 'quantity' => 1, 'unit_price' => 2000.00],
                ],
            ])
            ->assertRedirect(route('admin.job-orders.index'));

        $jobOrder = JobOrder::where('customer_id', $customer->id)->first();
        $item     = $jobOrder->items()->first();

        // override wins over catalog default
        $this->assertSame(2000.0, (float) $item->unit_cost);
        $this->assertSame(2000.0, (float) $item->total_cost);
    }

    /** @test */
    public function store_sets_quantity_per_service_and_totals()
    {
        $catalog  = $this->makeCatalog(['default_price' => 500.00]);
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);

        // item 5: quantity 3 => total = 3 * 500
        $this->actingAs($this->admin)
            ->post('/admin/job-orders', [
                'customer_id'    => $customer->id,
                'vehicle_id'     => $vehicle->id,
                'job_order_date' => '2026-08-06',
                'items' => [
                    ['catalog_id' => $catalog->id, 'quantity' => 3, 'unit_price' => null],
                ],
            ])
            ->assertRedirect(route('admin.job-orders.index'));

        $jobOrder = JobOrder::where('customer_id', $customer->id)->first();
        $item     = $jobOrder->items()->first();

        $this->assertSame(3.0, (float) $item->quantity);
        $this->assertSame(1500.0, (float) $item->total_cost);
    }

    /** @test */
    public function create_form_exposes_service_catalog_picker()
    {
        $this->makeCatalog(['name' => 'Oil Change', 'default_price' => 1500.00]);
        Customer::factory()->create();

        $this->actingAs($this->admin)
            ->get('/admin/job-orders/create')
            ->assertStatus(200)
            ->assertSee('Add Service')
            ->assertSee('Oil Change')
            ->assertSee('1500');
    }

    /** @test */
    public function edit_form_exposes_service_items_editor_and_notes()
    {
        $service = $this->makeCatalog(['name' => 'Brake Pads', 'default_price' => 4000.00]);
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $tech     = User::factory()->create();

        $this->actingAs($this->admin)->post('/admin/job-orders', [
            'customer_id'    => $customer->id,
            'vehicle_id'     => $vehicle->id,
            'job_order_date' => '2026-08-06',
            'job_order_status' => 'pending',
            'technician_id'  => $tech->id,
            'internal_notes' => 'Needs approval',
            'items' => [
                ['catalog_id' => $service->id, 'quantity' => 2, 'unit_price' => 4500.00],
            ],
        ]);

        $jobOrder = JobOrder::where('customer_id', $customer->id)->first();

        $this->actingAs($this->admin)
            ->get('/admin/job-orders/' . $jobOrder->id . '/edit')
            ->assertStatus(200)
            ->assertSee('Add Service')
            ->assertSee('Brake Pads')
            ->assertSee('Needs approval');
    }

    /** @test */
    public function create_form_exposes_searchable_customer_and_customer_filtered_vehicles()
    {
        $customer  = Customer::factory()->create(['first_name' => 'Wilma', 'last_name' => 'Bautista']);
        $otherCust = Customer::factory()->create(['first_name' => 'Fred', 'last_name' => 'Flintstone']);
        $v1 = Vehicle::factory()->create(['customer_id' => $customer->id,  'make' => 'Toyota', 'model' => 'Vios']);
        $v2 = Vehicle::factory()->create(['customer_id' => $otherCust->id, 'make' => 'Honda', 'model' => 'City']);

        $this->actingAs($this->admin)
            ->get('/admin/job-orders/create')
            ->assertStatus(200)
            ->assertSee('Wilma')
            ->assertSee("data-customer=\"{$customer->id}\"", false)
            ->assertSee('data-search', false)
            ->assertSee('filterCustomers', false);
    }

    /** @test */
    public function update_syncs_services_prices_and_keeps_technician_and_notes()
    {
        $catalog1 = $this->makeCatalog(['name' => 'Oil Change',    'default_price' => 1500.00]);
        $catalog2 = $this->makeCatalog(['name' => 'Brake Pads',    'default_price' => 4000.00]);
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $tech     = User::factory()->create();

        // create with one item
        $this->actingAs($this->admin)->post('/admin/job-orders', [
            'customer_id'    => $customer->id,
            'vehicle_id'     => $vehicle->id,
            'job_order_date' => '2026-08-06',
            'job_order_status' => 'pending',
            'internal_notes' => 'Initial note',
            'technician_id'  => $tech->id,
            'items' => [
                ['catalog_id' => $catalog1->id, 'quantity' => 1, 'unit_price' => null],
            ],
        ]);

        $jobOrder = JobOrder::where('customer_id', $customer->id)->first();

        // item 7: update replaces services (swap to Brake Pads), changes price, keeps tech+notes
        $this->actingAs($this->admin)
            ->put('/admin/job-orders/' . $jobOrder->id, [
                'customer_id'    => $customer->id,
                'vehicle_id'     => $vehicle->id,
                'job_order_date' => '2026-08-06',
                'job_order_status' => 'pending',
                'internal_notes' => 'Updated note',
                'technician_id'  => $tech->id,
                'items' => [
                    ['catalog_id' => $catalog2->id, 'quantity' => 2, 'unit_price' => 4500.00],
                ],
            ])
            ->assertRedirect(route('admin.job-orders.index'));

        $jobOrder->refresh();
        $this->assertSame('Updated note', $jobOrder->internal_notes);
        $this->assertSame($tech->id, $jobOrder->technician_id);

        // item 8: changes reflect on the customer-linked record
        $this->assertSame(1, $jobOrder->items()->count());
        $item = $jobOrder->items()->first();
        $this->assertSame('Brake Pads', $item->description);
        $this->assertSame(4500.0, (float) $item->unit_cost);   // override
        $this->assertSame(2.0, (float) $item->quantity);
        $this->assertSame(9000.0, (float) $item->total_cost);
        $this->assertSame($customer->id, $jobOrder->customer_id);
    }
}
