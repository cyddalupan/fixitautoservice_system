<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\VehicleInspection;
use App\Models\Estimate;
use App\Models\WorkOrder;

class ActiveTransactionService
{
    /**
     * Check if a vehicle already has an active transaction.
     * Returns array with stage info or null if none found.
     *
     * Priority order: Work Order > Estimate > Inspection > Appointment
     *
     * @param int $vehicleId
     * @return array|null
     */
    public static function checkActiveTransaction($vehicleId)
    {
        // Check Work Orders first (highest priority)
        $workOrder = WorkOrder::where('vehicle_id', $vehicleId)
            ->whereNotIn('work_order_status', ['completed', 'cancelled', 'archived'])
            ->whereNull('deleted_at')
            ->latest()
            ->first();
        if ($workOrder) {
            return [
                'has_active' => true,
                'stage' => 'Job Order',
                'stage_key' => 'work_order',
                'reference_number' => $workOrder->work_order_number ?? 'WO-' . str_pad($workOrder->id, 5, '0', STR_PAD_LEFT),
                'record_id' => $workOrder->id,
                'status' => $workOrder->work_order_status,
                'created_at' => $workOrder->created_at,
                'route' => route('work-orders.show', $workOrder->id) ?? '/work-orders/' . $workOrder->id,
                'customer' => optional($workOrder->customer)->name,
                'customer_id' => optional($workOrder->customer)->id,
            ];
        }

        // Check Estimates
        $estimate = Estimate::where('vehicle_id', $vehicleId)
            ->whereNotIn('status', ['completed', 'cancelled', 'archived'])
            ->whereNull('deleted_at')
            ->latest()
            ->first();
        if ($estimate) {
            return [
                'has_active' => true,
                'stage' => 'Estimate',
                'stage_key' => 'estimate',
                'reference_number' => $estimate->estimate_number ?? 'EST-' . str_pad($estimate->id, 5, '0', STR_PAD_LEFT),
                'record_id' => $estimate->id,
                'status' => $estimate->status,
                'created_at' => $estimate->created_at,
                'route' => route('estimates.show', $estimate->id) ?? '/estimates/' . $estimate->id,
                'customer' => optional($estimate->customer)->name,
                'customer_id' => optional($estimate->customer)->id,
            ];
        }

        // Check Inspections (Repair Orders)
        $inspection = VehicleInspection::where('vehicle_id', $vehicleId)
            ->whereNotIn('inspection_status', ['completed', 'cancelled', 'archived', 'released'])
            ->whereNull('deleted_at')
            ->latest()
            ->first();
        if ($inspection) {
            return [
                'has_active' => true,
                'stage' => 'Repair Order',
                'stage_key' => 'inspection',
                'reference_number' => 'INS-' . str_pad($inspection->id, 5, '0', STR_PAD_LEFT),
                'record_id' => $inspection->id,
                'status' => $inspection->inspection_type ?? 'pending',
                'created_at' => $inspection->created_at,
                'route' => route('inspections.show', $inspection->id) ?? '/inspections/' . $inspection->id,
                'customer' => optional($inspection->customer)->name,
                'customer_id' => optional($inspection->customer)->id,
            ];
        }

        // Check Appointments
        $appointment = Appointment::where('vehicle_id', $vehicleId)
            ->whereNotIn('appointment_status', ['completed', 'cancelled', 'archived'])
            ->whereNull('deleted_at')
            ->latest()
            ->first();
        if ($appointment) {
            return [
                'has_active' => true,
                'stage' => 'Appointment',
                'stage_key' => 'appointment',
                'reference_number' => $appointment->appointment_number ?? 'APT-' . str_pad($appointment->id, 5, '0', STR_PAD_LEFT),
                'record_id' => $appointment->id,
                'status' => $appointment->appointment_status,
                'created_at' => $appointment->created_at,
                'route' => route('appointments.show', $appointment->id) ?? '/appointments/' . $appointment->id,
                'customer' => optional($appointment->customer)->name,
                'customer_id' => optional($appointment->customer)->id,
            ];
        }

        return null; // No active transaction
    }

    /**
     * Get the vehicle_id from various request params.
     *
     * @param \Illuminate\Http\Request $request
     * @return int|null
     */
    public static function resolveVehicleId($request)
    {
        if ($request->filled('vehicle_id')) {
            return $request->vehicle_id;
        }
        if ($request->filled('vehicle')) {
            return $request->vehicle;
        }
        return null;
    }
}
