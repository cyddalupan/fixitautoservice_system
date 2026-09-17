<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P6 requirement: "Action buttons: Edit, Delete" on the JO detail view.
 * Routes exist; the show view must surface Edit + Delete actions.
 * RED -> GREEN (red first: show view has no action buttons).
 */
class AdminJobOrderShowActionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['email' => 'admin@fixit.test']);
    }

    private function makeJobOrder(): JobOrder
    {
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);

        return JobOrder::create([
            'customer_id'      => $customer->id,
            'vehicle_id'       => $vehicle->id,
            'job_order_date'   => '2026-08-06',
            'job_order_status' => 'pending',
            'job_order_number' => 'JO-2026-0001',
            'service_advisor_id' => $this->admin->id,
        ]);
    }

    /** @test */
    public function show_view_displays_edit_action_button()
    {
        $jo = $this->makeJobOrder();

        $this->actingAs($this->admin)
            ->get("/admin/job-orders/{$jo->id}")
            ->assertStatus(200)
            ->assertSee(route('admin.job-orders.edit', $jo))
            ->assertSee('Edit');
    }

    /** @test */
    public function show_view_displays_delete_action_button()
    {
        $jo = $this->makeJobOrder();

        $this->actingAs($this->admin)
            ->get("/admin/job-orders/{$jo->id}")
            ->assertStatus(200)
            ->assertSee(route('admin.job-orders.destroy', $jo))
            ->assertSee('Delete');
    }
}
