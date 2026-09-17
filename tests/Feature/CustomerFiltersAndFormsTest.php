<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerFiltersAndFormsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
    }

    // ─── 1. Filters on /customers (JS sends filters as JSON string) ───

    public function test_api_search_applies_is_active_filter_from_json_string()
    {
        $active = Customer::factory()->create(['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'is_active' => true]);
        $inactive = Customer::factory()->create(['first_name' => 'Maria', 'last_name' => 'Santos', 'is_active' => false]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/customers/search?filters=' . urlencode(json_encode(['is_active' => true])));

        $response->assertOk();
        $ids = collect($response->json('customers'))->pluck('id')->all();
        $this->assertContains($active->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_api_search_applies_brand_filter_from_json_string()
    {
        $toyota = Customer::factory()->create(['first_name' => 'Toy', 'last_name' => 'Owner']);
        Vehicle::factory()->create(['customer_id' => $toyota->id, 'make' => 'Toyota']);
        $honda = Customer::factory()->create(['first_name' => 'Hon', 'last_name' => 'Owner']);
        Vehicle::factory()->create(['customer_id' => $honda->id, 'make' => 'Honda']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/customers/search?filters=' . urlencode(json_encode(['brand' => 'Toyota'])));

        $response->assertOk();
        $ids = collect($response->json('customers'))->pluck('id')->all();
        $this->assertContains($toyota->id, $ids);
        $this->assertNotContains($honda->id, $ids);
    }

    public function test_api_search_applies_location_filter_from_json_string()
    {
        $qc = Customer::factory()->create(['first_name' => 'Qc', 'last_name' => 'Resident', 'city' => 'Quezon City']);
        $makati = Customer::factory()->create(['first_name' => 'Mk', 'last_name' => 'Resident', 'city' => 'Makati']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/customers/search?filters=' . urlencode(json_encode(['location' => 'Quezon'])));

        $response->assertOk();
        $ids = collect($response->json('customers'))->pluck('id')->all();
        $this->assertContains($qc->id, $ids);
        $this->assertNotContains($makati->id, $ids);
    }

    // ─── 2. Stray "&gt;" after inputs on /customers/create ───

    public function test_create_page_has_no_stray_gt_after_error_directives()
    {
        $source = file_get_contents(resource_path('views/customers/create.blade.php'));
        $this->assertStringNotContainsString('@enderror>', $source, 'Stray ">" after @enderror renders &gt; after inputs');
        $this->assertStringNotContainsString('@endif>', $source);
    }

    // ─── 3. Create vs edit forms must contain the same needed info ───

    public function test_create_page_contains_all_core_fields_that_edit_page_has()
    {
        $customer = Customer::factory()->create();

        $createHtml = $this->actingAs($this->admin)->get(route('customers.create'))->getContent();
        $editHtml = $this->actingAs($this->admin)->get(route('customers.edit', $customer))->getContent();

        preg_match_all('/name="([^"]+)"/', $createHtml, $createMatches);
        preg_match_all('/name="([^"]+)"/', $editHtml, $editMatches);
        $createNames = array_unique($createMatches[1]);
        $editNames = array_unique($editMatches[1]);

        // Portal-only fields and form-technical fields are not needed on create
        $portalOnly = ['cropped_image', 'remove_photo', 'portalToggleActive', '_token', '_method'];

        foreach ($editNames as $name) {
            if (in_array($name, $portalOnly, true) || str_starts_with($name, 'newPortal') || str_starts_with($name, 'portal')) {
                continue;
            }
            $this->assertContains($name, $createNames, "Create form is missing field: {$name}");
        }
    }

    public function test_store_accepts_first_and_last_name_fields()
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'phone' => '09171234567',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'phone' => '09171234567']);
    }

    public function test_store_keeps_full_name_backward_compat()
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'full_name' => 'Maria Santos',
            'phone' => '09179876543',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['first_name' => 'Maria', 'last_name' => 'Santos']);
    }

    public function test_store_saves_additional_profile_fields()
    {
        $response = $this->actingAs($this->admin)->post(route('customers.store'), [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'phone' => '09171234567',
            'email' => 'juan@example.com',
            'address' => '123 Main St',
            'city' => 'Quezon City',
            'state' => 'NCR',
            'zip_code' => '1100',
            'customer_type' => 'individual',
            'segment' => 'premium',
            'is_active' => '1',
            'preferred_contact_method' => 'sms',
            'notes' => 'VIP customer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'first_name' => 'Juan',
            'city' => 'Quezon City',
            'state' => 'NCR',
            'zip_code' => '1100',
            'customer_type' => 'individual',
            'segment' => 'premium',
            'is_active' => 1,
            'preferred_contact' => 'sms',
            'notes' => 'VIP customer',
        ]);
    }

    public function test_update_accepts_first_and_last_name_fields()
    {
        $customer = Customer::factory()->create(['first_name' => 'Old', 'last_name' => 'Name']);

        $response = $this->actingAs($this->admin)->put(route('customers.update', $customer), [
            'first_name' => 'New',
            'last_name' => 'Name',
            'phone' => '09171234567',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'first_name' => 'New', 'last_name' => 'Name']);
    }

    public function test_update_keeps_full_name_backward_compat()
    {
        $customer = Customer::factory()->create(['first_name' => 'Old', 'last_name' => 'Name']);

        $response = $this->actingAs($this->admin)->put(route('customers.update', $customer), [
            'full_name' => 'Renamed Person',
            'phone' => '09171234567',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'first_name' => 'Renamed', 'last_name' => 'Person']);
    }

    public function test_update_saves_additional_profile_fields()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('customers.update', $customer), [
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'phone' => '09171234567',
            'city' => 'Quezon City',
            'segment' => 'vip',
            'preferred_contact_method' => 'email',
            'notes' => 'Updated notes',
            'is_active' => '0',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'city' => 'Quezon City',
            'segment' => 'vip',
            'preferred_contact' => 'email',
            'notes' => 'Updated notes',
            'is_active' => 0,
        ]);
    }
}
