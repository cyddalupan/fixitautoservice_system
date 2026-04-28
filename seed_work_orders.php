<?php
/**
 * Seed 2 sample work orders for testing the upgraded UI
 * Run: php seed_work_orders.php
 */

// Bootstrap Laravel with minimal setup - use HTTP kernel
chdir('/var/www/fixit-system');
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

// Handle the kernel manually to avoid URL generator issues
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WorkOrder;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use Carbon\Carbon;

echo "=== Seed Work Orders ===\n\n";

// 1. Customer: Mark Angeles (id=4) - Vehicle 4 (Toyota Camry 2023)
$customer1 = Customer::find(4);
$vehicle1 = Vehicle::find(4);
if (!$customer1 || !$vehicle1) {
    echo "ERROR: Customer 4 or Vehicle 4 not found\n";
    exit(1);
}

// 2. Customer: John Smith (id=1) - Vehicle 1 (Honda Accord 2020)  
$customer2 = Customer::find(1);
$vehicle2 = Vehicle::find(1);
if (!$customer2 || !$vehicle2) {
    echo "ERROR: Customer 1 or Vehicle 1 not found\n";
    exit(1);
}

$advisor = User::where('role', 'office_staff')->first();
$technician = User::where('role', 'technician')->first();

if (!$advisor || !$technician) {
    echo "ERROR: Advisor or technician not found\n";
    exit(1);
}

echo "Using Advisor: {$advisor->name} (id:{$advisor->id})\n";
echo "Using Technician: {$technician->name} (id:{$technician->id})\n\n";

// WO #1 - PMS for Mark Angeles
$wo1 = WorkOrder::create([
    'customer_id' => $customer1->id,
    'vehicle_id' => $vehicle1->id,
    'service_advisor_id' => $advisor->id,
    'technician_id' => $technician->id,
    'work_order_number' => 'WO-' . date('Ymd') . '-001',
    'work_order_date' => Carbon::today()->toDateString(),
    'work_order_status' => 'repairing',
    'work_order_type' => 'maintenance',
    'priority' => 'normal',
    'odometer_in' => 45123,
    'fuel_level' => '3/4',
    'customer_concerns' => "Change oil and oil filter\nTop up all fluids\nInspect brakes and tires\nCheck engine light occasionally flickers",
    'customer_complaints' => "Check engine light flickers when idling after long drives",
    'initial_diagnosis' => 'Preliminary scan shows P0420 code (Catalyst System Efficiency Below Threshold). Will perform full diagnostic.',
    'recommended_services' => 'Oil change service, brake inspection, diagnostic scan for check engine light',
    'additional_notes' => 'Customer mentioned upcoming long trip - recommend thorough inspection',
    'estimated_labor_hours' => 3.5,
    'estimated_labor_cost' => 3500.00,
    'estimated_parts_cost' => 6500.00,
    'estimated_tax' => 1000.00,
    'estimated_total' => 11000.00,
    'estimate_notes' => 'Includes synthetic oil change, oil filter, cabin filter, brake inspection',
    'has_safety_concerns' => true,
    'bay_number' => 3,
    'technician_assignments' => json_encode([
        ['technician_id' => $technician->id, 'technician_name' => $technician->name, 'role' => 'Lead Technician'],
    ]),
    'requires_customer_approval' => true,
]);

echo "✅ Created WO #1: {$wo1->work_order_number} - {$customer1->name} ({$wo1->work_order_status})\n";

// WO #2 - Repair for John Smith
$wo1_ref = WorkOrder::where('customer_id', $customer1->id)->latest()->first();
$wo2 = WorkOrder::create([
    'customer_id' => $customer2->id,
    'vehicle_id' => $vehicle2->id,
    'service_advisor_id' => $advisor->id,
    'technician_id' => $technician->id,
    'work_order_number' => 'WO-' . date('Ymd') . '-002',
    'work_order_date' => Carbon::today()->toDateString(),
    'work_order_status' => 'completed',
    'work_order_type' => 'repair',
    'priority' => 'high',
    'odometer_in' => 72350,
    'fuel_level' => '1/2',
    'vehicle_condition' => 'Minor scratch on rear bumper (pre-existing). Tires at 50% tread depth.',
    'customer_concerns' => "Vibrations when braking at highway speeds\nSqueaking noise from front left wheel when turning\nA/C not cooling effectively",
    'initial_diagnosis' => 'Front brake rotors warped, left front wheel bearing worn, A/C refrigerant low',
    'recommended_services' => 'Replace front brake rotors and pads, replace left front wheel bearing, A/C recharge and leak test',
    'additional_notes' => 'Customer is a valued repeat client - offer loyalty discount',
    'estimated_labor_hours' => 5.0,
    'estimated_labor_cost' => 6000.00,
    'estimated_parts_cost' => 12500.00,
    'estimated_tax' => 1850.00,
    'estimated_total' => 20350.00,
    'actual_labor_hours' => 4.5,
    'actual_labor_cost' => 5400.00,
    'actual_parts_cost' => 11800.00,
    'actual_tax' => 1720.00,
    'actual_total' => 18920.00,
    'discount_amount' => 1000.00,
    'final_amount' => 17920.00,
    'payment_status' => 'paid',
    'amount_paid' => 17920.00,
    'balance_due' => 0.00,
    'work_performed' => "1. Replaced front brake rotors and pads (OEM spec)\n2. Replaced left front wheel bearing assembly\n3. A/C recharge with dye - no leaks detected\n4. Test drove - vibrations eliminated, no noise, A/C cold at 7°C",
    'technician_notes' => 'Left front bearing was difficult to remove due to corrosion. Applied anti-seize on new bearing.',
    'quality_check_passed' => true,
    'quality_check_by' => 2, // Shop Manager
    'quality_check_at' => Carbon::now()->subHours(2),
    'is_warranty_work' => false,
    'is_insurance_work' => false,
    'customer_notified' => true,
    'customer_notified_time' => Carbon::now()->subHour(),
    'customer_pickup_time' => Carbon::now(),
    'check_in_time' => Carbon::now()->subHours(8),
    'work_start_time' => Carbon::now()->subHours(6),
    'work_complete_time' => Carbon::now()->subHours(3),
    'bay_number' => 1,
    'technician_assignments' => json_encode([
        ['technician_id' => $technician->id, 'technician_name' => $technician->name, 'role' => 'Lead Technician'],
    ]),
]);

echo "✅ Created WO #2: {$wo2->work_order_number} - {$customer2->name} ({$wo2->work_order_status})\n";
echo "\nDone! 2 work orders created successfully.\n";
