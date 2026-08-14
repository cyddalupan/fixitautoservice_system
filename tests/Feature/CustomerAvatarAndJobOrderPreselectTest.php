<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD regression tests for the 2026-08-14 fixes:
 *
 * Bug #1: /customers/{id} printed the ui-avatars URL as literal text inside
 *         the initials circle when the customer has no profile picture.
 *         Root cause: the @else branch rendered {{ $customer->avatar }}
 *         (a string) instead of the computed initials.
 *
 * Bug #2: /job-orders/create must pre-select the customer when the link came
 *         from a customer page (?customer_id=) and pre-select the vehicle
 *         when it came from a vehicle page (?vehicle_id=).
 */
class CustomerAvatarAndJobOrderPreselectTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    // ─── Bug #1: profile avatar block ───

    public function test_customer_show_page_renders_initials_not_avatar_url_text()
    {
        $customer = Customer::factory()->create([
            'first_name'      => 'Gerry',
            'last_name'       => 'Aliling',
            'profile_picture' => null,
        ]);

        $response = $this->actingAs($this->admin)->get("/customers/{$customer->id}");

        $response->assertOk();

        // The initials circle must contain the computed initials…
        $response->assertSee('GA', false);

        // …and must NOT contain the ui-avatars URL string.
        $this->assertStringNotContainsString(
            'ui-avatars.com/api/?name=',
            $response->getContent(),
            'The avatar URL must never be printed as text inside the initials circle.'
        );
    }

    public function test_customer_show_page_renders_image_when_profile_picture_exists()
    {
        $customer = Customer::factory()->create([
            'first_name'      => 'Gerry',
            'last_name'       => 'Aliling',
            'profile_picture' => 'profile-pictures/test.jpg',
        ]);

        $response = $this->actingAs($this->admin)->get("/customers/{$customer->id}");

        $response->assertOk();
        $response->assertSee('profile-pictures/test.jpg', false);
        $this->assertStringNotContainsString(
            'ui-avatars.com/api/?name=',
            $response->getContent()
        );
    }

    // ─── Bug #2: pre-selection on /job-orders/create ───

    public function test_job_order_create_preselects_customer_from_query_param()
    {
        $customer = Customer::factory()->create([
            'first_name' => 'Gerry',
            'last_name'  => 'Aliling',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/job-orders/create?customer_id={$customer->id}")
            ->assertOk();

        $html = $response->getContent();

        // Customer is locked in via hidden input + plaintext display.
        $this->assertStringContainsString('customer_selection_mode', $html);
        $this->assertStringContainsString(
            'name="customer_id" value="' . $customer->id . '"',
            $html
        );
        $this->assertStringContainsString('Gerry Aliling', $html);
    }

    public function test_job_order_create_preselects_vehicle_from_query_param()
    {
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($this->admin)
            ->get("/job-orders/create?vehicle_id={$vehicle->id}")
            ->assertOk();

        $html = $response->getContent();

        // Vehicle option must be marked selected.
        $this->assertMatchesRegularExpression(
            '/<option value="' . $vehicle->id . '"[^>]*selected/',
            $html
        );

        // Customer derived from the vehicle must be locked in too.
        $this->assertStringContainsString(
            'name="customer_id" value="' . $customer->id . '"',
            $html
        );
    }

    public function test_job_order_create_without_params_has_no_preselect()
    {
        $response = $this->actingAs($this->admin)
            ->get('/job-orders/create')
            ->assertOk();

        $html = $response->getContent();

        $this->assertStringNotContainsString('value="from_url"', $html);
        $this->assertStringContainsString('name="customer_id"', $html); // select, not hidden input
        $this->assertStringContainsString('name="customer_selection_mode" value="manual"', $html);
    }
}
