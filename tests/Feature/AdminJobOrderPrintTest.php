<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\JobOrderItem;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P7 — Job Order Printing (Two Versions), blueprint-aligned.
 *
 * Routes (blueprint):
 *   GET /admin/job-orders/{id}/print      -> Version A: full JO with pricing
 *   GET /admin/job-orders/{id}/print/tech -> Version B: tech only, NO pricing/personal info
 *
 * RED -> GREEN (red first: print routes 404 / views missing).
 */
class AdminJobOrderPrintTest extends TestCase
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
        $customer = Customer::factory()->create([
            'first_name' => 'Dante',
            'last_name'  => 'Reyes',
            'email'      => 'dante@example.com',
            'phone'      => '09171234567',
            'address'    => '123 A. Bonifacio Ave, QC',
        ]);
        $vehicle = Vehicle::factory()->create([
            'customer_id'  => $customer->id,
            'make'         => 'Toyota',
            'model'        => 'Hilux',
            'year'         => 2020,
            'license_plate'=> 'ABC-1234',
        ]);
        $tech = User::factory()->create(['name' => 'Engr. Marco Tech', 'email' => 'tech@fixit.test']);

        $jobOrder = JobOrder::create([
            'customer_id'      => $customer->id,
            'vehicle_id'       => $vehicle->id,
            'technician_id'    => $tech->id,
            'job_order_date'   => '2026-08-06',
            'job_order_status' => 'pending',
            'job_order_number' => 'JO-2026-0001',
            'service_advisor_id' => $this->admin->id,
            'final_amount'     => 3200.00,
        ]);

        JobOrderItem::create([
            'job_order_id' => $jobOrder->id,
            'item_type'    => 'labor',
            'description'  => 'Oil Change',
            'quantity'     => 1,
            'unit_cost'    => 1200.00,
            'total_cost'   => 1200.00,
            'final_amount' => 1200.00,
        ]);
        JobOrderItem::create([
            'job_order_id' => $jobOrder->id,
            'item_type'    => 'part',
            'description'  => 'Oil Filter',
            'quantity'     => 2,
            'unit_cost'    => 1000.00,
            'total_cost'   => 2000.00,
            'final_amount' => 2000.00,
        ]);

        return $jobOrder->fresh();
    }

    public function test_admin_job_order_print_versionA_route_returns_pdf(): void
    {
        $jobOrder = $this->makeJobOrder();

        $response = $this->actingAs($this->admin)
            ->get("/admin/job-orders/{$jobOrder->id}/print");

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $this->binaryContent($response));
    }

    public function test_versionA_contains_customer_vehicle_services_pricing_and_total(): void
    {
        $jobOrder = $this->makeJobOrder();

        $response = $this->actingAs($this->admin)
            ->get("/admin/job-orders/{$jobOrder->id}/print");

        $response->assertOk();
        $pdfText = $this->binaryContent($response);
        // PDF body is compressed/flate; decode to confirm rendered text.
        $decoded = $this->decodePdfText($pdfText);

        // Version A must show pricing + customer + vehicle.
        $this->assertStringContainsString('Dante', $decoded);
        $this->assertStringContainsString('Toyota', $decoded);
        $this->assertStringContainsString('Oil Change', $decoded);
        $this->assertStringContainsString('Oil Filter', $decoded);
        $this->assertStringContainsString('JO-2026-0001', $decoded);
        $this->assertStringContainsString('3,200.00', $decoded);
    }

    public function test_print_tech_route_returns_pdf(): void
    {
        $jobOrder = $this->makeJobOrder();

        $response = $this->actingAs($this->admin)
            ->get("/admin/job-orders/{$jobOrder->id}/print/tech");

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $this->binaryContent($response));
    }

    public function test_tech_version_shows_tech_and_services_but_hides_pricing_and_personal_info(): void
    {
        $jobOrder = $this->makeJobOrder();

        $response = $this->actingAs($this->admin)
            ->get("/admin/job-orders/{$jobOrder->id}/print/tech");

        $response->assertOk();
        $decoded = $this->decodePdfText($this->binaryContent($response));

        // Must SHOW tech name + vehicle + services.
        $this->assertStringContainsString('Marco Tech', $decoded);
        $this->assertStringContainsString('Toyota', $decoded);
        $this->assertStringContainsString('Oil Change', $decoded);
        $this->assertStringContainsString('Oil Filter', $decoded);

        // Must HIDE pricing + customer personal info.
        $this->assertStringNotContainsString('3,200.00', $decoded);
        $this->assertStringNotContainsString('Dante', $decoded);
        $this->assertStringNotContainsString('dante@example.com', $decoded);
        $this->assertStringNotContainsString('09171234567', $decoded);
    }

    private function binaryContent(\Illuminate\Testing\TestResponse $response): string
    {
        return $response->getContent();
    }

    private function decodePdfText(string $pdf): string
    {
        // Try to extract readable text via `pdftotext` if available, else fall back to raw.
        $tmp = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tmp, $pdf);
        $out = '';
        @exec('pdftotext ' . escapeshellarg($tmp) . ' - 2>/dev/null', $lines, $code);
        if ($code === 0) {
            $out = implode("\n", $lines);
        }
        @unlink($tmp);
        if ($out === '') {
            // Fallback: strip binary, keep ASCII printable chars.
            $out = preg_replace('/[^\x20-\x7E]/', '', $pdf);
        }
        return $out;
    }
}
