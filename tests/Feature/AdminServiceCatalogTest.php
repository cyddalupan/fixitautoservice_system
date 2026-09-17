<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BlueprintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Blueprint source of truth: /admin/services CRUD against the SIMPLE
 * blueprint `services` table (name, description, default_price decimal,
 * category, is_active). No brand/model break-out.
 */
class AdminServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_service_list(): void
    {
        BlueprintService::factory()->create(['name' => 'Oil Change', 'category' => 'Maintenance']);

        $this->actingAs($this->admin())
            ->get('/admin/services')
            ->assertOk()
            ->assertSee('Oil Change')
            ->assertSee('Maintenance');
    }

    public function test_admin_can_create_service_with_blueprint_fields(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/services', [
                'name' => 'Brake Pad Replacement',
                'description' => 'Replace front brake pads',
                'default_price' => '2500.00',
                'category' => 'Brakes',
                'is_active' => 1,
            ])
            ->assertRedirect('/admin/services');

        $this->assertDatabaseHas('services', [
            'name' => 'Brake Pad Replacement',
            'description' => 'Replace front brake pads',
            'default_price' => 2500.00,
            'category' => 'Brakes',
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_update_service(): void
    {
        $service = BlueprintService::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->admin())
            ->put("/admin/services/{$service->id}", [
                'name' => 'New Name',
                'description' => 'Updated desc',
                'default_price' => '3500.50',
                'category' => 'Engine',
                'is_active' => 1,
            ])
            ->assertRedirect('/admin/services');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'New Name',
            'default_price' => 3500.50,
        ]);
    }

    public function test_admin_can_delete_service(): void
    {
        $service = BlueprintService::factory()->create();

        $this->actingAs($this->admin())
            ->delete("/admin/services/{$service->id}")
            ->assertRedirect('/admin/services');

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_admin_can_toggle_service_active(): void
    {
        $service = BlueprintService::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
            ->patch("/admin/services/{$service->id}/toggle")
            ->assertRedirect('/admin/services');

        $this->assertDatabaseHas('services', ['id' => $service->id, 'is_active' => 0]);

        // Toggle back on
        $this->actingAs($this->admin())
            ->patch("/admin/services/{$service->id}/toggle")
            ->assertRedirect('/admin/services');

        $this->assertDatabaseHas('services', ['id' => $service->id, 'is_active' => 1]);
    }
}
