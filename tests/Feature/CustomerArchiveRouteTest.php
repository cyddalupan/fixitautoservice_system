<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Archive;

/**
 * Bug #1: POST /customers/{id}/archive returns 404 — the route is missing.
 *
 * The customers index view's delete button posts to /customers/{id}/archive
 * (CustomerController::archive exists but no route registers it).
 *
 * RED first: route does not exist -> POST returns 404.
 */
class CustomerArchiveRouteTest extends TestCase
{
    /** @test */
    public function archive_route_exists_and_archives_customer()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $customer = Customer::factory()->create();

        $this->actingAs($admin)
            ->post('/customers/' . $customer->id . '/archive')
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('success');

        // Customer is hard-deleted (no SoftDeletes on Customer) but archived
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);

        // Archive record created
        $this->assertDatabaseHas('archives', [
            'archivable_id' => $customer->id,
            'archivable_type' => 'App\Models\Customer',
            'source_module' => 'customer',
        ]);
    }

    /** @test */
    public function non_admin_cannot_archive_a_customer()
    {
        $staff = User::factory()->create(['role' => 'office_staff']);
        $customer = Customer::factory()->create();

        $this->actingAs($staff)
            ->post('/customers/' . $customer->id . '/archive')
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    /** @test */
    public function archive_route_is_registered_in_web_routes()
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('customers.archive'),
            'Route customers.archive must be registered'
        );
    }
}
