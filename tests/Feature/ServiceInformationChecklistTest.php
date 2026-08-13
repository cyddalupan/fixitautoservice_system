<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD (red -> green) for the "Service Information" checklist on the
 * "Fixit Adjustment - Create Appointment" card (2026-08-13):
 *
 * 1. Only these 8 services may be visible (anything else must be removed):
 *    PREVENTIVE MAINTENANCE, BASIC TUNE UP, EGR SERVICE, AIRCON CLEANING,
 *    AIRCON GENERAL CLEANING, AIRCON SERVICE, UNDERCHASSIS SERVICE,
 *    ENGINE SERVICE.
 * 2. Remove Estimated Cost and Quick Estimate from the create page.
 * 3. Service Description stays; the quick-suggestion options below it only
 *    appear AFTER the user selects a main service (e.g. Preventive
 *    Maintenance shows its branches only after it is picked).
 * 4. The booking form allows MULTIPLE services (like the appointment create
 *    page does).
 */
class ServiceInformationChecklistTest extends TestCase
{
    use RefreshDatabase;

    /** The exact 8 services Cyd listed (key => display name). */
    private const CHECKLIST = [
        'preventive_maintenance' => 'PREVENTIVE MAINTENANCE',
        'basic_tune_up' => 'BASIC TUNE UP',
        'egr_service' => 'EGR SERVICE',
        'aircon_cleaning' => 'AIRCON CLEANING',
        'aircon_general_cleaning' => 'AIRCON GENERAL CLEANING',
        'aircon_service' => 'AIRCON SERVICE',
        'underchassis_service' => 'UNDERCHASSIS SERVICE',
        'engine_service' => 'ENGINE SERVICE',
    ];

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    // ===================================================================
    // ITEM 1: Only the 8 checklist services exist (config + DB)
    // ===================================================================

    /** @test */
    public function config_service_types_list_is_exactly_the_eight_checklist_services()
    {
        $this->assertSame(
            self::CHECKLIST,
            config('service-types.list'),
            'config/service-types.php must contain EXACTLY the 8 checklist services (no diagnostic, auto parts sales, home service request, body repair and painting).'
        );
    }

    /** @test */
    public function service_types_table_contains_exactly_the_eight_checklist_services()
    {
        $rows = \DB::table('service_types')->orderBy('sort_order')->get();

        $this->assertCount(8, $rows, 'service_types table must contain exactly 8 rows');

        $keys = $rows->pluck('key')->all();
        $this->assertSame(array_keys(self::CHECKLIST), $keys);

        foreach ($rows as $row) {
            $this->assertTrue((bool) $row->is_active, "{$row->key} must be active");
        }
    }

    // ===================================================================
    // ITEM 2: Estimated Cost + Quick Estimate removed from create page
    // ===================================================================

    /** @test */
    public function create_page_has_no_estimated_cost_or_quick_estimate_fields()
    {
        $html = $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString(
            'estimated_cost',
            $html,
            'Estimated Cost input must be removed'
        );
        $this->assertStringNotContainsString(
            'Quick Estimate',
            $html,
            'Quick Estimate chips must be removed'
        );
        $this->assertStringNotContainsString(
            'quick-note-btn',
            $html,
            'no quick estimate / quick note buttons should remain on the page'
        );
    }

    // ===================================================================
    // ITEM 3: Description suggestions only appear after a main service
    // ===================================================================

    /** @test */
    public function create_page_description_suggestions_are_hidden_until_a_main_service_is_selected()
    {
        $html = $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->getContent();

        // The suggestions container must exist but start hidden.
        $this->assertMatchesRegularExpression(
            '/id="service-suggestions"[^>]*d-none/',
            $html,
            'service-suggestions container must exist and be hidden (d-none) by default'
        );

        // Each suggestion chip must be tagged with the main service it belongs to,
        // so the JS can reveal only the branches of the selected service.
        foreach (array_keys(self::CHECKLIST) as $key) {
            $this->assertStringContainsString(
                'data-service="' . $key . '"',
                $html,
                "suggestion chips must be tagged with data-service=\"{$key}\""
            );
        }
    }

    // ===================================================================
    // ITEM 4: Booking form allows MULTIPLE services
    // ===================================================================

    /** @test */
    public function booking_api_accepts_multiple_service_types_and_stores_them()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/api/booking/customer-booking', [
            'name' => 'Multi Service Guest',
            'email' => 'multi.service@example.com',
            'phone' => '09179876543',
            'vehicle_make' => 'Toyota',
            'vehicle_model' => 'Fortuner',
            'vehicle_year' => 2022,
            'vehicle_plate' => 'MULTI-001',
            'service_type' => ['preventive_maintenance', 'aircon_service'],
            'service_request' => 'PMS plus aircon cleaning',
            'appointment_date' => now()->addDays(6)->toDateString(),
            'appointment_time' => '09:00',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $appointment = \App\Models\Appointment::where('booking_source', 'website')->latest('id')->first();
        $this->assertNotNull($appointment);

        // Both selected services must be persisted (JSON array).
        $stored = is_array($appointment->service_types)
            ? $appointment->service_types
            : json_decode($appointment->service_types ?? '[]', true);
        $this->assertContains('preventive_maintenance', $stored);
        $this->assertContains('aircon_service', $stored);

        // appointment_type falls back to the first selected service.
        $this->assertSame('preventive_maintenance', $appointment->appointment_type);
    }

    /** @test */
    public function booking_api_still_accepts_a_single_service_type_string()
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/api/booking/customer-booking', [
            'name' => 'Single Service Guest',
            'email' => 'single.service@example.com',
            'phone' => '09171234599',
            'vehicle_make' => 'Honda',
            'vehicle_model' => 'Civic',
            'vehicle_year' => 2020,
            'vehicle_plate' => 'SINGLE-001',
            'service_type' => 'engine_service',
            'service_request' => 'Engine check',
            'appointment_date' => now()->addDays(7)->toDateString(),
            'appointment_time' => '11:30',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
