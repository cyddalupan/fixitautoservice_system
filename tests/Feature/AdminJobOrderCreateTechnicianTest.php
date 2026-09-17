<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P6 requirement: "Assign technician (text input)" on the admin JO create flow.
 * The update flow already persists technician_id; the CREATE flow must too.
 * RED -> GREEN (red first: store ignores technician_id + create form lacks the field).
 */
class AdminJobOrderCreateTechnicianTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'email' => 'admin@fixit.test',
        ]);
    }

    /** @test */
    public function create_form_exposes_technician_select()
    {
        $technician = User::factory()->create(['name' => 'Tec Maria']);
        $customer   = Customer::factory()->create();
        Vehicle::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($this->admin)
            ->get('/admin/job-orders/create')
            ->assertStatus(200)
            ->assertSee('Tec Maria');
    }

    /** @test */
    public function store_persists_technician_assignment()
    {
        $technician = User::factory()->create();
        $customer   = Customer::factory()->create();
        $vehicle    = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($this->admin)
            ->post('/admin/job-orders', [
                'customer_id'      => $customer->id,
                'vehicle_id'       => $vehicle->id,
                'job_order_date'   => '2026-08-06',
                'job_order_status' => 'draft',
                'technician_id'    => $technician->id,
            ])
            ->assertRedirect(route('admin.job-orders.index'));

        $this->assertDatabaseHas('job_orders', [
            'customer_id'   => $customer->id,
            'technician_id' => $technician->id,
        ]);

        $created = JobOrder::where('customer_id', $customer->id)->first();
        $this->assertNotNull($created);
        $this->assertSame($technician->id, $created->technician_id);
    }

    /** @test */
    public function technicians_listed_on_create_form_only_include_users()
    {
        $technician = User::factory()->create(['name' => 'Tec Jose']);
        $nonTech    = User::factory()->create(['name' => 'Admin Only']);

        $this->actingAs($this->admin)
            ->get('/admin/job-orders/create')
            ->assertStatus(200)
            ->assertSee('Tec Jose')
            ->assertSee('Admin Only');
    }
}
