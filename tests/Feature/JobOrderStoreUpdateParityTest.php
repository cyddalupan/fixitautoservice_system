<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\JobOrder;

/**
 * Bug #2c + #3: Job orders CRUD must fully work.
 *
 * - store() requires service_advisor_id but no service_advisor users exist in
 *   the DB, and the create form's advisor dropdown is empty -> validation
 *   fails silently -> "after saving nothing happens".
 * - update() only validates a tiny subset of fields, so editing wipes
 *   warranty/insurance/estimates/concerns data.
 * - create and edit forms must expose the same field set for job orders.
 *
 * RED first: store() without service_advisor_id fails validation (redirect
 * back with errors, no JobOrder created); update() drops most fields.
 */
class JobOrderStoreUpdateParityTest extends TestCase
{
    private function authUser(): User
    {
        return User::factory()->create();
    }

    private function makeCustomerVehicle(): array
    {
        $customer = Customer::factory()->create();
        $vehicle  = Vehicle::factory()->create(['customer_id' => $customer->id]);
        return [$customer, $vehicle];
    }

    private function validPayload(Customer $customer, Vehicle $vehicle): array
    {
        return [
            'customer_id'      => $customer->id,
            'vehicle_id'       => $vehicle->id,
            'job_order_date'   => now()->toDateString(),
            'job_order_type'   => 'repair',
            'priority'         => 'normal',
            'customer_concerns'=> 'Engine noise when accelerating',
            'odometer_in'      => 45230,
            'fuel_level'       => '3/4',
            'vehicle_condition'=> 'good',
        ];
    }

    /** @test */
    public function store_creates_job_order_without_service_advisor_and_redirects()
    {
        $user = $this->authUser();
        [$customer, $vehicle] = $this->makeCustomerVehicle();

        $this->actingAs($user)
            ->post('/job-orders', $this->validPayload($customer, $vehicle))
            ->assertRedirect();

        $jobOrder = JobOrder::first();
        $this->assertNotNull($jobOrder, 'JobOrder must be created without a service_advisor_id');
        $this->assertSame('pending', $jobOrder->job_order_status);
        $this->assertSame('Engine noise when accelerating', $jobOrder->customer_concerns);
    }

    /** @test */
    public function store_saves_warranty_and_insurance_fields()
    {
        $user = $this->authUser();
        [$customer, $vehicle] = $this->makeCustomerVehicle();

        $payload = array_merge($this->validPayload($customer, $vehicle), [
            'is_warranty_work'    => '1',
            'warranty_type'       => 'extended',
            'warranty_number'     => 'W-001234',
            'warranty_expiry'     => now()->addYear()->toDateString(),
            'warranty_coverage'   => 150000,
            'is_insurance_work'   => '1',
            'insurance_company'   => 'AXA Philippines',
            'insurance_claim_number' => 'CLM-9988',
            'insurance_adjuster'  => 'Juan Dela Cruz',
            'insurance_deductible'=> 5000,
            'bay_number'          => 3,
            'requires_customer_approval' => '1',
        ]);

        $this->actingAs($user)
            ->post('/job-orders', $payload)
            ->assertRedirect();

        $jobOrder = JobOrder::first();
        $this->assertSame(1, (int) $jobOrder->is_warranty_work);
        $this->assertSame('extended', $jobOrder->warranty_type);
        $this->assertSame('W-001234', $jobOrder->warranty_number);
        $this->assertSame(1, (int) $jobOrder->is_insurance_work);
        $this->assertSame('AXA Philippines', $jobOrder->insurance_company);
        $this->assertSame('CLM-9988', $jobOrder->insurance_claim_number);
        $this->assertSame(3, (int) $jobOrder->bay_number);
    }

    /** @test */
    public function store_succeeds_when_estimate_and_insurance_fields_are_empty_strings()
    {
        $user = $this->authUser();
        [$customer, $vehicle] = $this->makeCustomerVehicle();

        // The real create form submits empty strings for the optional
        // estimate/warranty/insurance fields. ConvertEmptyStringsToNull turns
        // them into null, and the DB columns are NOT NULL DEFAULT 0 -> the
        // insert used to 500. This is the live "after saving nothing happens"
        // failure for customers who skip the optional sections.
        $payload = array_merge($this->validPayload($customer, $vehicle), [
            'service_advisor_id'     => '',
            'technician_id'          => '',
            'odometer_in'            => '',
            'fuel_level'             => '',
            'vehicle_condition'      => '',
            'customer_complaints'    => '',
            'initial_diagnosis'      => '',
            'recommended_services'   => '',
            'additional_notes'       => '',
            'estimated_labor_hours'  => '',
            'estimated_labor_cost'   => '',
            'estimated_parts_cost'   => '',
            'estimated_tax'          => '',
            'estimate_notes'         => '',
            'is_warranty_work'       => '0',
            'warranty_type'          => '',
            'warranty_number'        => '',
            'warranty_expiry'        => '',
            'warranty_coverage'      => '',
            'is_insurance_work'      => '0',
            'insurance_company'      => '',
            'insurance_claim_number' => '',
            'insurance_adjuster'     => '',
            'insurance_deductible'   => '',
            'bay_number'             => '',
            'requires_customer_approval' => '0',
            'requires_manager_approval'  => '0',
            'is_rush_order'          => '0',
            'is_complex_job'         => '0',
            'has_safety_concerns'    => '0',
        ]);

        $this->actingAs($user)
            ->post('/job-orders', $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $jobOrder = JobOrder::first();
        $this->assertNotNull($jobOrder, 'JobOrder must be created even when optional numeric fields are empty');
        $this->assertSame('0.00', (string) $jobOrder->estimated_labor_hours);
        $this->assertSame('0.00', (string) $jobOrder->estimated_labor_cost);
        $this->assertSame('0.00', (string) $jobOrder->estimated_parts_cost);
        $this->assertSame('0.00', (string) $jobOrder->estimated_tax);
        $this->assertSame('0.00', (string) $jobOrder->insurance_deductible);
    }

    /** @test */
    public function update_saves_full_field_set_and_redirects()
    {
        $user = $this->authUser();
        [$customer, $vehicle] = $this->makeCustomerVehicle();
        $jobOrder = JobOrder::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id'  => $vehicle->id,
        ]);

        $payload = array_merge($this->validPayload($customer, $vehicle), [
            'job_order_status'    => 'pending',
            'is_warranty_work'    => '1',
            'warranty_type'       => 'manufacturer',
            'warranty_number'     => 'W-MFG-77',
            'warranty_expiry'     => now()->addMonths(6)->toDateString(),
            'warranty_coverage'   => 80000,
            'is_insurance_work'   => '1',
            'insurance_company'   => 'Philam Life',
            'insurance_claim_number' => 'CLM-5566',
            'insurance_adjuster'  => 'Maria Santos',
            'insurance_deductible'=> 3000,
            'bay_number'          => 2,
            'customer_concerns'   => 'Updated: grinding noise when turning',
            'initial_diagnosis'   => 'Suspected wheel bearing wear',
            'estimated_labor_hours' => '2.5',
            'estimated_labor_cost'  => '2500',
            'estimated_parts_cost'  => '4500',
            'estimated_tax'         => '700',
            'additional_notes'      => 'Customer approved estimate via phone',
        ]);

        $this->actingAs($user)
            ->put('/job-orders/' . $jobOrder->id, $payload)
            ->assertRedirect();

        $jobOrder->refresh();
        $this->assertSame(1, (int) $jobOrder->is_warranty_work);
        $this->assertSame('W-MFG-77', $jobOrder->warranty_number);
        $this->assertSame(1, (int) $jobOrder->is_insurance_work);
        $this->assertSame('Philam Life', $jobOrder->insurance_company);
        $this->assertSame(2, (int) $jobOrder->bay_number);
        $this->assertSame('Updated: grinding noise when turning', $jobOrder->customer_concerns);
        $this->assertSame('Suspected wheel bearing wear', $jobOrder->initial_diagnosis);
        $this->assertSame('2.50', (string) $jobOrder->estimated_labor_hours);
        $this->assertSame('Customer approved estimate via phone', $jobOrder->additional_notes);
    }

    /** @test */
    public function create_and_edit_forms_expose_the_same_field_names()
    {
        $user = $this->authUser();
        [$customer, $vehicle] = $this->makeCustomerVehicle();
        $jobOrder = JobOrder::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id'  => $vehicle->id,
        ]);

        $createHtml = $this->actingAs($user)->get('/job-orders/create')->getContent();
        $editHtml   = $this->actingAs($user)->get('/job-orders/' . $jobOrder->id . '/edit')->getContent();

        $createFields = $this->extractFieldNames($createHtml);
        $editFields   = $this->extractFieldNames($editHtml);

        // Core job-order fields that must exist on BOTH forms.
        $shared = [
            'customer_id', 'vehicle_id', 'job_order_date', 'job_order_type',
            'priority', 'service_advisor_id', 'technician_id', 'odometer_in',
            'fuel_level', 'vehicle_condition', 'customer_concerns',
            'customer_complaints', 'initial_diagnosis', 'recommended_services',
            'additional_notes', 'estimated_labor_hours', 'estimated_labor_cost',
            'estimated_parts_cost', 'estimated_tax', 'estimate_notes',
            'is_warranty_work', 'warranty_type', 'warranty_number',
            'warranty_expiry', 'warranty_coverage', 'is_insurance_work',
            'insurance_company', 'insurance_claim_number', 'insurance_adjuster',
            'insurance_deductible', 'bay_number', 'requires_customer_approval',
        ];

        foreach ($shared as $field) {
            $this->assertContains($field, $createFields, "Create form missing field: {$field}");
            $this->assertContains($field, $editFields, "Edit form missing field: {$field}");
        }
    }

    private function extractFieldNames(string $html): array
    {
        preg_match_all('/name="([^"]+)"/', $html, $m);
        return array_values(array_unique($m[1]));
    }
}
