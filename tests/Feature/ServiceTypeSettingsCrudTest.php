<?php

namespace Tests\Feature;

use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD (red -> green) for the Service Types CRUD inside the Settings page.
 *
 * Cyd's request (2026-08-13):
 *   "gawan na natin ng crud ito para mabilis mag adjust pwedeng nasa
 *    settings page nalang" — the 8 services list must be editable from
 *    the Settings page so the shop can adjust it quickly.
 *
 * The CRUD edits the `service_types` table, which must be the single
 * source of truth for the booking form, the admin create-page selector
 * and API validation (config/service-types.php stays as fallback only).
 */
class ServiceTypeSettingsCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    private function postForm(string $uri, array $data)
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        return $this->actingAs($this->admin)->post($uri, $data);
    }

    private function putForm(string $uri, array $data)
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        return $this->actingAs($this->admin)->put($uri, $data);
    }

    // ===================================================================
    // INDEX / LISTING
    // ===================================================================

    /** @test */
    public function settings_page_links_to_the_service_types_crud()
    {
        $html = $this->actingAs($this->admin)
            ->get('/settings')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('settings.service-types.index'),
            $html,
            'Settings page must link to the Service Types CRUD'
        );
    }

    /** @test */
    public function admin_can_view_the_service_types_index_page()
    {
        $html = $this->actingAs($this->admin)
            ->get('/settings/service-types')
            ->assertOk()
            ->getContent();

        foreach (['PREVENTIVE MAINTENANCE', 'BASIC TUNE UP', 'EGR SERVICE', 'AIRCON CLEANING'] as $name) {
            $this->assertStringContainsString($name, $html, "index must list {$name}");
        }
    }

    // ===================================================================
    // CREATE
    // ===================================================================

    /** @test */
    public function admin_can_create_a_service_type()
    {
        $this->postForm('/settings/service-types', [
            'key' => 'paint_protection',
            'name' => 'PAINT PROTECTION',
            'icon' => '🎨',
            'sort_order' => 9,
            'is_active' => 1,
        ])->assertRedirect(route('settings.service-types.index'));

        $this->assertDatabaseHas('service_types', [
            'key' => 'paint_protection',
            'name' => 'PAINT PROTECTION',
            'icon' => '🎨',
            'is_active' => 1,
        ]);
    }

    /** @test */
    public function create_requires_key_and_name()
    {
        $this->postForm('/settings/service-types', [])
            ->assertSessionHasErrors(['key', 'name']);
    }

    /** @test */
    public function create_rejects_duplicate_key()
    {
        $this->postForm('/settings/service-types', [
            'key' => 'preventive_maintenance',
            'name' => 'DUPLICATE',
        ])->assertSessionHasErrors('key');
    }

    /** @test */
    public function new_service_type_appears_in_the_booking_selector_and_validation()
    {
        $this->postForm('/settings/service-types', [
            'key' => 'paint_protection',
            'name' => 'PAINT PROTECTION',
            'icon' => '🎨',
            'sort_order' => 9,
            'is_active' => 1,
        ])->assertRedirect(route('settings.service-types.index'));

        // The shared selector partial must render the new service.
        $html = view('partials.service-type-selector', [
            'name' => 'service_type',
            'multiple' => false,
        ])->render();
        $this->assertStringContainsString('PAINT PROTECTION', $html);

        // API validation must accept the new key.
        $this->assertContains('paint_protection', ServiceType::keys());
    }

    // ===================================================================
    // UPDATE
    // ===================================================================

    /** @test */
    public function admin_can_update_a_service_type()
    {
        $type = ServiceType::where('key', 'egr_service')->firstOrFail();

        $this->putForm("/settings/service-types/{$type->id}", [
            'key' => 'egr_service',
            'name' => 'EGR SERVICE (UPDATED)',
            'icon' => '🔄',
            'sort_order' => 3,
            'is_active' => 1,
        ])->assertRedirect(route('settings.service-types.index'));

        $this->assertDatabaseHas('service_types', [
            'id' => $type->id,
            'name' => 'EGR SERVICE (UPDATED)',
        ]);
    }

    // ===================================================================
    // TOGGLE ACTIVE
    // ===================================================================

    /** @test */
    public function admin_can_toggle_a_service_type_active_status()
    {
        $type = ServiceType::where('key', 'underchassis_service')->firstOrFail();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs($this->admin)
            ->post("/settings/service-types/{$type->id}/toggle")
            ->assertRedirect(route('settings.service-types.index'));

        $this->assertDatabaseHas('service_types', ['id' => $type->id, 'is_active' => 0]);

        // Inactive services must not appear in the active booking list.
        $this->assertNotContains('underchassis_service', ServiceType::activeKeys());
    }

    // ===================================================================
    // DELETE
    // ===================================================================

    /** @test */
    public function admin_can_delete_a_service_type()
    {
        $type = ServiceType::where('key', 'engine_service')->firstOrFail();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs($this->admin)
            ->delete("/settings/service-types/{$type->id}")
            ->assertRedirect(route('settings.service-types.index'));

        $this->assertDatabaseMissing('service_types', ['id' => $type->id]);
        $this->assertNotContains('engine_service', ServiceType::keys());
    }

    // ===================================================================
    // DB IS THE SOURCE OF TRUTH (config is only a fallback)
    // ===================================================================

    /** @test */
    public function service_type_helpers_read_from_db_with_config_fallback()
    {
        // Seeded DB rows drive the list.
        $this->assertSame(
            'EGR SERVICE',
            ServiceType::list()['egr_service']
        );

        $this->assertSame('🔄', ServiceType::icons()['egr_service']);
    }
}
