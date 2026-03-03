<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Generate service IDs for appointments that don't have them
        $appointments = DB::table('appointments')
            ->whereNull('service_id')
            ->get();
        
        foreach ($appointments as $appointment) {
            $serviceId = 'SVC-' . date('Ymd', strtotime($appointment->created_at)) . '-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT);
            DB::table('appointments')
                ->where('id', $appointment->id)
                ->update(['service_id' => $serviceId]);
        }

        // Propagate service IDs to other tables
        // Update estimates
        DB::table('estimates')
            ->join('appointments', 'estimates.appointment_id', '=', 'appointments.id')
            ->whereNull('estimates.service_id')
            ->update(['estimates.service_id' => DB::raw('appointments.service_id')]);

        // Update work_orders
        DB::table('work_orders')
            ->join('appointments', 'work_orders.appointment_id', '=', 'appointments.id')
            ->whereNull('work_orders.service_id')
            ->update(['work_orders.service_id' => DB::raw('appointments.service_id')]);

        // Update invoices
        DB::table('invoices')
            ->join('appointments', 'invoices.appointment_id', '=', 'appointments.id')
            ->whereNull('invoices.service_id')
            ->update(['invoices.service_id' => DB::raw('appointments.service_id')]);

        // Update payments through invoices
        DB::table('payments')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->whereNull('payments.service_id')
            ->update(['payments.service_id' => DB::raw('invoices.service_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed safely
    }
};