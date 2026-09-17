<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\JobOrder;

/**
 * Blueprint P6 — Job Order edit/update/destroy + status transitions.
 *
 * RED first: /admin/job-orders/{id}/edit, PUT and DELETE routes do not exist
 * yet on AdminJobOrderController (only index/create/store/show).
 *
 * Blueprint schema intent (services-only):
 *   - status transitions: pending -> in_progress -> completed
 *   - update notes, technician assignment, services
 *   - soft delete
 *   - form validation
 */
class AdminJobOrderEditUpdateTest extends TestCase
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

    private function makeJobOrder(string $status = 'pending'): JobOrder
    {
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);

        return JobOrder::factory()->create([
            'customer_id'      => $customer->id,
            'vehicle_id'       => $vehicle->id,
            'job_order_number' => 'JO-' . now()->format('Y') . '-0001',
            'job_order_status' => $status,
            'internal_notes'   => 'Initial notes',
        ]);
    }

    /** @test */
    public function admin_can_view_job_order_edit_form()
    {
        $jobOrder = $this->makeJobOrder();

        $this->actingAs($this->admin)
            ->get('/admin/job-orders/' . $jobOrder->id . '/edit')
            ->assertStatus(200)
            ->assertViewHas('jobOrder')
            ->assertSee('JO-' . now()->format('Y') . '-0001')
            ->assertSee('Edit');
    }

    /** @test */
    public function update_saves_notes_and_technician_and_redirects()
    {
        $jobOrder  = $this->makeJobOrder('pending');
        $technician = User::factory()->create();

        $this->actingAs($this->admin)
            ->put('/admin/job-orders/' . $jobOrder->id, [
                'customer_id'      => $jobOrder->customer_id,
                'vehicle_id'       => $jobOrder->vehicle_id,
                'job_order_status' => 'pending',
                'internal_notes'   => 'Updated notes after diagnosis',
                'technician_id'    => $technician->id,
            ])
            ->assertRedirect(route('admin.job-orders.index'));

        $jobOrder->refresh();
        $this->assertSame('Updated notes after diagnosis', $jobOrder->internal_notes);
        $this->assertSame($technician->id, $jobOrder->technician_id);
    }

    /** @test */
    public function status_transitions_pending_to_in_progress_to_completed()
    {
        $jobOrder = $this->makeJobOrder('pending');

        // pending -> in_progress
        $this->actingAs($this->admin)
            ->put('/admin/job-orders/' . $jobOrder->id, [
                'customer_id'      => $jobOrder->customer_id,
                'vehicle_id'       => $jobOrder->vehicle_id,
                'job_order_status' => 'in_progress',
            ])
            ->assertRedirect(route('admin.job-orders.index'));

        $this->assertSame('in_progress', $jobOrder->refresh()->job_order_status);

        // in_progress -> completed
        $this->actingAs($this->admin)
            ->put('/admin/job-orders/' . $jobOrder->id, [
                'customer_id'      => $jobOrder->customer_id,
                'vehicle_id'       => $jobOrder->vehicle_id,
                'job_order_status' => 'completed',
            ])
            ->assertRedirect(route('admin.job-orders.index'));

        $this->assertSame('completed', $jobOrder->refresh()->job_order_status);
    }

    /** @test */
    public function invalid_status_is_rejected_with_validation_error()
    {
        $jobOrder = $this->makeJobOrder('pending');

        $this->actingAs($this->admin)
            ->from('/admin/job-orders/' . $jobOrder->id . '/edit')
            ->put('/admin/job-orders/' . $jobOrder->id, [
                'customer_id'      => $jobOrder->customer_id,
                'vehicle_id'       => $jobOrder->vehicle_id,
                'job_order_status' => 'invalid_status_xyz',
            ])
            ->assertSessionHasErrors('job_order_status');

        $this->assertSame('pending', $jobOrder->fresh()->job_order_status);
    }

    /** @test */
    public function destroy_soft_deletes_job_order()
    {
        $jobOrder = $this->makeJobOrder('completed');

        $this->actingAs($this->admin)
            ->delete('/admin/job-orders/' . $jobOrder->id)
            ->assertRedirect(route('admin.job-orders.index'));

        $this->assertSoftDeleted('job_orders', ['id' => $jobOrder->id]);
    }

    /** @test */
    public function guests_cannot_access_edit_update_or_destroy()
    {
        $jobOrder = $this->makeJobOrder();

        // Guests are redirected to login for all three routes (auth-protected).
        $this->get('/admin/job-orders/' . $jobOrder->id . '/edit')->assertRedirect();
        $this->put('/admin/job-orders/' . $jobOrder->id, ['job_order_status' => 'pending'])->assertRedirect();
        $this->delete('/admin/job-orders/' . $jobOrder->id)->assertRedirect();
    }
}
