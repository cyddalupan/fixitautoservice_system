<?php
require '/var/www/fixit-system/vendor/autoload.php';
$app = require '/var/www/fixit-system/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
$recent = now()->subDays(90);

echo "=== COUNTER LOGIC ANALYSIS ===\n\n";

// =========================================================
// CORRECT COUNTER DEFINITIONS per MarX
// =========================================================
echo "CORRECT COUNTER DEFINITIONS:\n";
echo "A. Appointments = scheduled-only appointments NOT progressed (no linked WO/Est)\n";
echo "B. Repair Orders = work orders that are NOT linked to a scheduled appointment\n";  
echo "C. Estimates = all estimates\n";
echo "D. Job Orders = work orders that represent job orders specifically\n";
echo "Note: 'Job Order' currently = same model as Work Order. They may be the same thing conceptually.\n\n";

// A. Appointments: ONLY scheduled/confirmed, not yet progressed
$appts = App\Models\Appointment::where('appointment_date', '>=', $recent)
    ->whereIn('appointment_status', ['scheduled', 'confirmed'])
    ->get();

$scheduledOnly = 0;
echo "SCHEDULED/CONFIRMED APPOINTMENTS:\n";
foreach ($appts as $a) {
    $hasWO = WorkOrder::where('appointment_id', $a->id)->exists();
    $hasEst = Estimate::where('appointment_id', $a->id)->exists();
    $progressed = $hasWO || $hasEst;
    if (!$progressed) {
        echo "  [{$a->id}] {$a->appointment_number} status={$a->appointment_status} = COUNTED\n";
        $scheduledOnly++;
    } else {
        echo "  [{$a->id}] {$a->appointment_number} status={$a->appointment_status} = SKIP (already progressed)\n";
    }
}
echo "A. Appointments total: $scheduledOnly\n\n";

// B. Repair Orders: work orders NOT linked to a scheduled appointment
$allWOs = WorkOrder::where('created_at', '>=', $recent)->get();
$repairOrders = 0;
echo "REPAIR ORDERS:\n";
foreach ($allWOs as $wo) {
    if (!$wo->appointment_id) {
        echo "  [{$wo->id}] {$wo->work_order_number} - standalone (no appointment) = COUNTED\n";
        $repairOrders++;
    } else {
        $linkedAppt = App\Models\Appointment::find($wo->appointment_id);
        if ($linkedAppt && in_array($linkedAppt->appointment_status, ['scheduled','confirmed'])) {
            echo "  [{$wo->id}] {$wo->work_order_number} - linked to scheduled appt [{$linkedAppt->id}] = SKIP (part of appointment)\n";
        } else {
            $status = $linkedAppt ? $linkedAppt->appointment_status : 'deleted';
            echo "  [{$wo->id}] {$wo->work_order_number} - linked to non-scheduled appt [{$wo->appointment_id}] status={$status} = COUNTED\n";
            $repairOrders++;
        }
    }
}
echo "B. Repair orders total: $repairOrders\n\n";

// C. Estimates
$estCount = Estimate::where('created_at', '>=', $recent)->count();
echo "C. Estimates total: $estCount\n\n";

// D. Job Orders = Work Orders that are in 'job' categories
// In this system, 'Job Order' IS 'Work Order' - same table, same model
// MarX counts 5 job orders separately from 1 repair order
// So total work orders = repair orders + job orders  
$woTotal = count($allWOs);
$jobOrders = $woTotal - $repairOrders;
echo "D. Job orders (WO total - repair orders): $jobOrders\n\n";

$grandTotal = $scheduledOnly + $repairOrders + $estCount + $jobOrders;
echo "=== GRAND TOTAL: $scheduledOnly + $repairOrders + $estCount + $jobOrders = $grandTotal ===\n";
echo "MarX expected: 3 + 1 + 6 + 5 = 15\n";
EOF
php /var/www/fixit-system/counting_test.php
