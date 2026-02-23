<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\TaxRate;
use App\Models\Discount;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Create test data
        $this->customer = Customer::factory()->create();
        $this->vehicle = Vehicle::factory()->create(['customer_id' => $this->customer->id]);
        $this->workOrder = WorkOrder::factory()->create([
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => 'completed'
        ]);
        
        $this->taxRate = TaxRate::create([
            'name' => 'Test VAT',
            'code' => 'TESTVAT',
            'rate' => 12.00,
            'is_active' => true,
        ]);
        
        $this->discount = Discount::create([
            'name' => 'Test Discount',
            'code' => 'TEST10',
            'type' => 'percentage',
            'value' => 10.00,
            'is_active' => true,
        ]);
        
        $this->paymentMethod = PaymentMethod::create([
            'name' => 'Test Cash',
            'code' => 'TESTCASH',
            'type' => 'cash',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_create_an_invoice()
    {
        $response = $this->post('/invoices', [
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'work_order_id' => $this->workOrder->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Test invoice notes',
            'items' => [
                [
                    'item_type' => 'service',
                    'item_name' => 'Oil Change',
                    'description' => 'Full synthetic oil change',
                    'quantity' => 1,
                    'unit_price' => 2500.00,
                ],
                [
                    'item_type' => 'parts',
                    'item_name' => 'Oil Filter',
                    'description' => 'Premium oil filter',
                    'quantity' => 1,
                    'unit_price' => 500.00,
                ],
            ],
            'tax_rate_id' => $this->taxRate->id,
            'discount_id' => $this->discount->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('invoices', [
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'work_order_id' => $this->workOrder->id,
            'status' => 'draft',
            'payment_status' => 'pending',
        ]);
        
        $invoice = Invoice::latest()->first();
        $this->assertEquals(3000.00, $invoice->subtotal);
        $this->assertEquals(360.00, $invoice->tax_amount); // 12% of 3000
        $this->assertEquals(300.00, $invoice->discount_amount); // 10% of 3000
        $this->assertEquals(3060.00, $invoice->total_amount); // 3000 + 360 - 300
        
        $this->assertCount(2, $invoice->items);
    }

    /** @test */
    public function it_can_update_an_invoice()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);
        
        $response = $this->put("/invoices/{$invoice->id}", [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(15)->format('Y-m-d'),
            'notes' => 'Updated invoice notes',
            'items' => [
                [
                    'item_type' => 'service',
                    'item_name' => 'Brake Service',
                    'description' => 'Complete brake service',
                    'quantity' => 1,
                    'unit_price' => 4500.00,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $invoice->refresh();
        $this->assertEquals(4500.00, $invoice->subtotal);
        $this->assertEquals('Updated invoice notes', $invoice->notes);
        $this->assertCount(1, $invoice->items);
    }

    /** @test */
    public function it_can_send_an_invoice()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);

        $response = $this->post("/invoices/{$invoice->id}/send", [
            'delivery_method' => 'email',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $invoice->refresh();
        $this->assertEquals('sent', $invoice->status);
        $this->assertEquals('email', $invoice->delivery_method);
        $this->assertNotNull($invoice->sent_at);
    }

    /** @test */
    public function it_can_mark_an_invoice_as_paid()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'sent',
            'payment_status' => 'pending',
            'total_amount' => 1000.00,
        ]);

        $response = $this->post("/invoices/{$invoice->id}/mark-as-paid");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals('paid', $invoice->payment_status);
        $this->assertEquals(1000.00, $invoice->amount_paid);
        $this->assertEquals(0, $invoice->balance_due);
        $this->assertNotNull($invoice->paid_date);
    }

    /** @test */
    public function it_can_cancel_an_invoice()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'sent',
        ]);

        $response = $this->post("/invoices/{$invoice->id}/cancel", [
            'reason' => 'Customer requested cancellation',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->status);
    }

    /** @test */
    public function it_can_delete_a_draft_invoice()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'draft',
        ]);

        $response = $this->delete("/invoices/{$invoice->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    /** @test */
    public function it_cannot_delete_non_draft_invoice()
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'sent',
        ]);

        $response = $this->delete("/invoices/{$invoice->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    /** @test */
    public function it_can_list_invoices()
    {
        Invoice::factory()->count(5)->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->get('/invoices');

        $response->assertStatus(200);
        $response->assertSee('Invoices');
        $response->assertViewHas('invoices');
    }

    /** @test */
    public function it_can_show_invoice_statistics()
    {
        Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'paid',
            'payment_status' => 'paid',
            'total_amount' => 1000.00,
        ]);
        
        Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'sent',
            'payment_status' => 'pending',
            'total_amount' => 2000.00,
            'balance_due' => 2000.00,
        ]);

        $response = $this->get('/invoices/statistics');

        $response->assertStatus(200);
        $response->assertSee('Invoice Statistics');
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post('/invoices', []);

        $response->assertSessionHasErrors([
            'customer_id',
            'invoice_date',
            'items',
        ]);
    }

    /** @test */
    public function it_validates_invoice_items()
    {
        $response = $this->post('/invoices', [
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'item_type' => 'invalid_type',
                    'item_name' => '',
                    'quantity' => 0,
                    'unit_price' => -100,
                ],
            ],
        ]);

        $response->assertSessionHasErrors([
            'items.0.item_type',
            'items.0.item_name',
            'items.0.quantity',
            'items.0.unit_price',
        ]);
    }
}