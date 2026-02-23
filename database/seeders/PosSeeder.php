<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxRate;
use App\Models\Discount;
use App\Models\PaymentMethod;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\User;

class PosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create tax rates
        $taxRates = [
            [
                'name' => 'Standard VAT',
                'code' => 'VAT12',
                'rate' => 12.00,
                'is_active' => true,
            ],
            [
                'name' => 'Zero Rate',
                'code' => 'ZERO',
                'rate' => 0.00,
                'is_active' => true,
            ],
            [
                'name' => 'Exempt',
                'code' => 'EXEMPT',
                'rate' => 0.00,
                'is_active' => true,
            ],
        ];

        foreach ($taxRates as $taxRate) {
            TaxRate::create($taxRate);
        }

        // Create discounts
        $discounts = [
            [
                'name' => 'Loyalty Discount',
                'code' => 'LOYALTY10',
                'type' => 'percentage',
                'value' => 10.00,
                'is_active' => true,
            ],
            [
                'name' => 'Senior Citizen Discount',
                'code' => 'SENIOR20',
                'type' => 'percentage',
                'value' => 20.00,
                'is_active' => true,
            ],
            [
                'name' => 'First Time Customer',
                'code' => 'FIRST15',
                'type' => 'percentage',
                'value' => 15.00,
                'is_active' => true,
            ],
            [
                'name' => 'Cash Payment Discount',
                'code' => null,
                'type' => 'fixed',
                'value' => 500.00,
                'is_active' => true,
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::create($discount);
        }

        // Create payment methods
        $paymentMethods = [
            [
                'name' => 'Cash',
                'code' => 'CASH',
                'type' => 'cash',
                'is_active' => true,
            ],
            [
                'name' => 'Credit Card',
                'code' => 'CC',
                'type' => 'card',
                'is_active' => true,
            ],
            [
                'name' => 'Debit Card',
                'code' => 'DC',
                'type' => 'card',
                'is_active' => true,
            ],
            [
                'name' => 'Bank Transfer',
                'code' => 'BANK',
                'type' => 'bank',
                'is_active' => true,
            ],
            [
                'name' => 'GCash',
                'code' => 'GCASH',
                'type' => 'digital',
                'is_active' => true,
            ],
            [
                'name' => 'Check',
                'code' => 'CHECK',
                'type' => 'check',
                'is_active' => true,
            ],
        ];

        foreach ($paymentMethods as $paymentMethod) {
            PaymentMethod::create($paymentMethod);
        }

        // Get sample data
        $customers = Customer::take(5)->get();
        $vehicles = Vehicle::take(5)->get();
        $workOrders = WorkOrder::take(5)->get();
        $users = User::take(3)->get();

        if ($customers->isEmpty() || $users->isEmpty()) {
            $this->command->info('No customers or users found. Skipping invoice creation.');
            return;
        }

        // Create sample invoices
        $invoiceTypes = ['service', 'parts', 'combined', 'estimate'];
        $statuses = ['draft', 'sent', 'paid', 'overdue'];
        $paymentStatuses = ['pending', 'partial', 'paid'];

        for ($i = 1; $i <= 20; $i++) {
            $customer = $customers->random();
            $vehicle = $vehicles->random();
            $workOrder = $workOrders->random();
            $user = $users->random();
            
            $invoiceDate = now()->subDays(rand(1, 60));
            $dueDate = $invoiceDate->copy()->addDays(rand(7, 30));
            
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'invoice_type' => $invoiceTypes[array_rand($invoiceTypes)],
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id ?? null,
                'work_order_id' => $workOrder->id ?? null,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'subtotal' => 0, // Will be calculated
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0, // Will be calculated
                'amount_paid' => 0,
                'balance_due' => 0, // Will be calculated
                'status' => $statuses[array_rand($statuses)],
                'payment_status' => $paymentStatuses[array_rand($paymentStatuses)],
                'notes' => 'Sample invoice for testing purposes.',
                'created_by' => $user->id,
            ]);

            // Create invoice items
            $itemTypes = ['service', 'parts', 'labor', 'fee'];
            $itemNames = [
                'service' => ['Oil Change', 'Brake Service', 'Tire Rotation', 'Engine Tune-up'],
                'parts' => ['Oil Filter', 'Brake Pads', 'Spark Plugs', 'Air Filter'],
                'labor' => ['Mechanic Labor', 'Diagnostic Fee', 'Installation'],
                'fee' => ['Shop Supplies', 'Environmental Fee', 'Disposal Fee'],
            ];

            $subtotal = 0;
            $numItems = rand(1, 5);

            for ($j = 1; $j <= $numItems; $j++) {
                $itemType = $itemTypes[array_rand($itemTypes)];
                $itemName = $itemNames[$itemType][array_rand($itemNames[$itemType])];
                $quantity = rand(1, 4);
                $unitPrice = rand(500, 5000);
                $total = $quantity * $unitPrice;
                $subtotal += $total;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => $itemType,
                    'item_name' => $itemName,
                    'description' => "Sample {$itemType} item",
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_amount' => $total,
                ]);
            }

            // Calculate tax and discount
            $taxRate = TaxRate::where('code', 'VAT12')->first();
            $taxAmount = ($subtotal * $taxRate->rate) / 100;
            
            $discount = Discount::where('code', 'LOYALTY10')->first();
            $discountAmount = ($subtotal * $discount->value) / 100;
            
            $totalAmount = $subtotal + $taxAmount - $discountAmount;
            
            // Determine payment amounts based on status
            if ($invoice->payment_status === 'paid') {
                $amountPaid = $totalAmount;
                $balanceDue = 0;
            } elseif ($invoice->payment_status === 'partial') {
                $amountPaid = $totalAmount * 0.5;
                $balanceDue = $totalAmount - $amountPaid;
            } else {
                $amountPaid = 0;
                $balanceDue = $totalAmount;
            }

            // Update invoice with calculated amounts
            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'balance_due' => $balanceDue,
            ]);

            // Create payments if invoice has payments
            if ($invoice->payment_status === 'paid' || $invoice->payment_status === 'partial') {
                $paymentMethod = PaymentMethod::where('code', 'CASH')->first();
                
                Payment::create([
                    'payment_number' => 'PAY-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'invoice_id' => $invoice->id,
                    'customer_id' => $customer->id,
                    'work_order_id' => $workOrder->id ?? null,
                    'payment_date' => $invoiceDate->copy()->addDays(rand(1, 7)),
                    'amount' => $amountPaid,
                    'payment_method_id' => $paymentMethod->id,
                    'payment_method_name' => $paymentMethod->name,
                    'status' => 'completed',
                    'notes' => 'Sample payment for testing.',
                    'received_by' => $user->id,
                ]);
            }
        }

        $this->command->info('POS sample data created successfully!');
        $this->command->info('- Tax Rates: ' . TaxRate::count());
        $this->command->info('- Discounts: ' . Discount::count());
        $this->command->info('- Payment Methods: ' . PaymentMethod::count());
        $this->command->info('- Invoices: ' . Invoice::count());
        $this->command->info('- Invoice Items: ' . InvoiceItem::count());
        $this->command->info('- Payments: ' . Payment::count());
    }
}