<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\JobOrder;

/**
 * Admin Job Order routes under /admin/job-orders* (blueprint P6 requirement).
 *
 * Red -> green TDD: the /admin/job-orders routes do not exist yet.
 */
class AdminJobOrderRoutesTest extends TestCase
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
    public function admin_can_view_paginated_job_orders_list()
    {
        JobOrder::factory()->create(['job_order_status' => 'in_progress']);
        JobOrder::factory()->create(['job_order_status' => 'completed']);

        $this->actingAs($this->admin)
            ->get('/admin/job-orders')
            ->assertStatus(200)
            ->assertViewHas('jobOrders', function ($jobOrders) {
                return $jobOrders->count() === 2;
            });
    }

    /** @test */
    public function list_shows_number_customer_vehicle_status_and_date()
    {
        $customer = Customer::factory()->create(['first_name' => 'Ana', 'last_name' => 'Cruz']);
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id, 'make' => 'Toyota', 'model' => 'Vios', 'year' => 2020]);
        $jobOrder = JobOrder::factory()->create(['customer_id' => $customer->id, 'vehicle_id' => $vehicle->id, 'job_order_number' => 'JO-2026-0001', 'job_order_status' => 'in_progress', 'job_order_date' => '2026-08-06']);

        $this->actingAs($this->admin)
            ->get('/admin/job-orders')
            ->assertStatus(200)
            ->assertSee('JO-2026-0001')
            ->assertSee('Ana Cruz')
            ->assertSee('Toyota')
            ->assertSee('in_progress')
            ->assertSee('2026-08-06');
    }

    /** @test */
    public function list_can_filter_by_status()
    {
        JobOrder::factory()->create(['job_order_status' => 'draft']);
        JobOrder::factory()->create(['job_order_status' => 'completed']);

        $this->actingAs($this->admin)
            ->get('/admin/job-orders?status=completed')
            ->assertStatus(200)
            ->assertViewHas('jobOrders', function ($jobOrders) {
                return $jobOrders->count() === 1
                    && $jobOrders->first()->job_order_status === 'completed';
            });
    }

    /** @test */
    public function list_can_filter_by_customer()
    {
        $customer = Customer::factory()->create();
        JobOrder::factory()->create(['customer_id' => $customer->id]);
        JobOrder::factory()->create();

        $this->actingAs($this->admin)
            ->get('/admin/job-orders?customer_id=' . $customer->id)
            ->assertStatus(200)
            ->assertViewHas('jobOrders', function ($jobOrders) use ($customer) {
                return $jobOrders->count() === 1
                    && $jobOrders->first()->customer_id === $customer->id;
            });
    }

    /** @test */
    public function list_can_filter_by_date_range()
    {
        JobOrder::factory()->create(['job_order_date' => '2026-08-01']);
        JobOrder::factory()->create(['job_order_date' => '2026-08-10']);

        $this->actingAs($this->admin)
            ->get('/admin/job-orders?date_from=2026-08-05&date_to=2026-08-15')
            ->assertStatus(200)
            ->assertViewHas('jobOrders', function ($jobOrders) {
                return $jobOrders->count() === 1
                    && substr((string) $jobOrders->first()->job_order_date, 0, 10) === '2026-08-10';
            });
    }

    /** @test */
    public function admin_can_view_job_order_detail()
    {
        $customer = Customer::factory()->create(['first_name' => 'Ana', 'last_name' => 'Cruz']);
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id, 'make' => 'Toyota', 'model' => 'Vios', 'year' => 2020]);
        $jobOrder = JobOrder::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id'  => $vehicle->id,
            'job_order_number' => 'JO-2026-0001',
            'job_order_status' => 'in_progress',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/job-orders/' . $jobOrder->id)
            ->assertStatus(200)
            ->assertViewHas('jobOrder')
            ->assertSee('JO-2026-0001')
            ->assertSee('Ana Cruz')
            ->assertSee('Toyota');
    }

    /** @test */
    public function admin_can_view_job_order_create_form()
    {
        $this->actingAs($this->admin)
            ->get('/admin/job-orders/create')
            ->assertStatus(200)
            ->assertSee('Customer')
            ->assertSee('Vehicle');
    }

    /** @test */
    public function store_creates_job_order_with_auto_number_and_status_draft()
    {
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($this->admin)
            ->post('/admin/job-orders', [
                'customer_id'      => $customer->id,
                'vehicle_id'       => $vehicle->id,
                'job_order_date'   => '2026-08-06',
                'job_order_status' => 'draft',
            ])
            ->assertRedirect(route('admin.job-orders.index'));

        $this->assertDatabaseHas('job_orders', [
            'customer_id'      => $customer->id,
            'vehicle_id'       => $vehicle->id,
            'job_order_status' => 'draft',
        ]);

        $created = JobOrder::where('customer_id', $customer->id)->first();
        $this->assertNotNull($created);
        $this->assertMatchesRegularExpression('/^JO-\d{4}-\d{4}$/', $created->job_order_number);
    }
}
