<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD (red -> green) for Cyd's feedback on the create-appointment form
 * (2026-08-13):
 *
 * 1. There must be exactly ONE Plate Number input on the create page.
 *    (Previously the vehicle section AND the New Customer panel each had one.)
 * 2. Customer selection is SEARCH-FIRST: pick an existing customer from the
 *    typeable list; "Add New Customer" is an explicit option/toggle, not the
 *    default required flow.
 * 3. Brand/Model autocomplete data must be available via the booking API
 *    (used by fixitautoservices.com/booking/ static form).
 */
class AppointmentCreateFeedbackFixesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    // ===================================================================
    // ITEM 1: Single Plate Number input
    // ===================================================================

    /** @test */
    public function create_page_has_exactly_one_plate_number_input()
    {
        $html = $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->getContent();

        $this->assertEquals(
            1,
            substr_count($html, 'name="plate_number"'),
            'exactly one plate_number input must be rendered'
        );
        $this->assertStringNotContainsString(
            'plate_number_manual',
            $html,
            'manual-add panel must not render its own duplicate plate field'
        );
    }

    // ===================================================================
    // ITEM 2: Search-first customer + optional manual add
    // ===================================================================

    /** @test */
    public function create_page_renders_add_new_customer_toggle()
    {
        $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->assertSee('Add New Customer', false);
    }

    /** @test */
    public function manual_add_panel_is_hidden_by_default()
    {
        $html = $this->actingAs($this->admin)
            ->get('/appointments/create')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            'manual-add-panel',
            $html,
            'manual add panel must still exist in the DOM'
        );
        $this->assertMatchesRegularExpression(
            '/manual-add-panel[^>]*d-none/',
            $html,
            'manual add panel must be hidden (d-none) by default — search-first UX'
        );
    }

    /** @test */
    public function existing_customer_selection_does_not_require_manual_add_fields()
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->admin)
            ->post('/appointments', [
                'customer_id' => $customer->id,
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Vios',
                'vehicle_year' => 2021,
                'appointment_date' => now()->addDays(3)->toDateString(),
                'appointment_time' => '10:00',
                'service_type' => ['preventive_maintenance'],
            ])
            ->assertRedirect(route('appointments.index'));

        $this->assertDatabaseHas('appointments', ['customer_id' => $customer->id]);
    }

    // ===================================================================
    // ITEM 3: Booking API exposes brand/model autocomplete data
    // (consumed by the static booking form on fixitautoservices.com/booking/)
    // ===================================================================

    /** @test */
    public function booking_api_returns_active_vehicle_brands()
    {
        VehicleBrand::create(['name' => 'Toyota', 'is_active' => true]);
        VehicleBrand::create(['name' => 'Honda', 'is_active' => true]);
        VehicleBrand::create(['name' => 'Isuzu', 'is_active' => false]); // inactive excluded

        $response = $this->getJson('/api/booking/vehicle-brands')
            ->assertOk()
            ->assertJsonMissing(['Isuzu']);

        $this->assertEqualsCanonicalizing(
            ['Toyota', 'Honda'],
            $response->json(),
            'active brands must be returned (order-independent)'
        );
    }

    /** @test */
    public function booking_api_returns_models_filtered_by_brand()
    {
        $toyota = VehicleBrand::create(['name' => 'Toyota', 'is_active' => true]);
        $honda = VehicleBrand::create(['name' => 'Honda', 'is_active' => true]);

        VehicleModel::create(['vehicle_brand_id' => $toyota->id, 'name' => 'Vios', 'is_active' => true]);
        VehicleModel::create(['vehicle_brand_id' => $toyota->id, 'name' => 'Fortuner', 'is_active' => true]);
        VehicleModel::create(['vehicle_brand_id' => $honda->id, 'name' => 'Civic', 'is_active' => true]);

        $response = $this->getJson('/api/booking/vehicle-models?brand=Toyota')
            ->assertOk()
            ->assertJsonMissing(['Civic']);

        $this->assertEqualsCanonicalizing(
            ['Vios', 'Fortuner'],
            $response->json(),
            'models must be filtered by brand (order-independent)'
        );
    }
}
