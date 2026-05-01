<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use App\Models\WorkOrder;
use Illuminate\Support\Collection;

class ServiceRecordService
{
    /**
     * Get all service workflows (centralized data structure)
     *
     * @param int|null $customerId Filter by customer
     * @param int|null $vehicleId Filter by vehicle
     * @param string|null $section Filter by section (appointments, inspections, estimates, work_orders, payments)
     * @return array
     */
    public function getWorkflows($customerId = null, $vehicleId = null, $section = null)
    {
        $workflows = [];

        // If filtering by customer
        if ($customerId) {
            $customer = Customer::findOrFail($customerId);
            $vehicles = Vehicle::where('customer_id', $customerId)
                ->with(['customer', 'appointments' => function($query) {
                    // Include all appointments including cancelled
                    $query->with(['vehicleInspection', 'estimate', 'workOrder']);
                    // REMOVED: ->orderBy('appointment_date', 'desc');
                    // Sorting will be done in buildVehicleWorkflow
                }])
                ->get();

            foreach ($vehicles as $vehicle) {
                $workflow = $this->buildVehicleWorkflow($vehicle);
                if (!empty($workflow['has_any_transaction'])) {
                    $workflows[] = $workflow;
                }
            }
        }
        // If filtering by vehicle
        elseif ($vehicleId) {
            $vehicle = Vehicle::with(['customer', 'appointments' => function($query) {
                // Include all appointments including cancelled
                $query->with(['vehicleInspection', 'estimate', 'workOrder.technician']);
                // REMOVED: ->orderBy('appointment_date', 'desc');
                // Sorting will be done in buildVehicleWorkflow
            }])->findOrFail($vehicleId);

            $workflow = $this->buildVehicleWorkflow($vehicle);
            if (!empty($workflow['has_any_transaction'])) {
                $workflows[] = $workflow;
            }
        }
        // Show ALL vehicles with recent appointments (last 30 days)
        else {
            // Show ALL vehicles with ANY appointments OR direct estimates (no date filter)
            // Get vehicles with appointments
            $vehicleIdsWithAppointments = Vehicle::whereHas('appointments')->pluck('id')->toArray();
            
            // Get vehicles with direct estimates (no appointments)
            $vehicleIdsWithEstimates = \App\Models\Estimate::whereNotNull('vehicle_id')
                ->whereNotIn('vehicle_id', $vehicleIdsWithAppointments)
                ->pluck('vehicle_id')
                ->unique()
                ->toArray();
            
            // Combine both
            $vehicleIds = array_merge($vehicleIdsWithAppointments, $vehicleIdsWithEstimates);
            
            if (empty($vehicleIds)) {
                return [];
            }
            
            $vehiclesWithAppointments = Vehicle::whereIn('id', $vehicleIds)
                ->with(['customer', 'appointments' => function($query) {
                    $query->with(['vehicleInspection', 'estimate', 'workOrder']);
                    // REMOVED: ->orderBy('appointment_date', 'desc');
                    // Sorting will be done in buildVehicleWorkflow
                }])
                ->orderBy('make')
                ->orderBy('model')
                ->get();

            foreach ($vehiclesWithAppointments as $vehicle) {
                // Use the proper buildVehicleWorkflow method which handles ALL appointments
                $workflow = $this->buildVehicleWorkflow($vehicle);

                // Add to workflows if it has any transaction (appointments count as transactions)
                if (!empty($workflow['has_any_transaction'])) {
                    $workflows[] = $workflow;
                }
            }
        }

        // Also fetch orphan appointments (vehicle_id IS NULL) — walk-ins or online bookings
        // that weren't linked to a vehicle record
        $orphanAppointments = \App\Models\Appointment::whereNull('vehicle_id')
            ->with(['vehicleInspection', 'estimate', 'workOrder', 'customer'])
            ->orderBy('appointment_date', 'desc')
            ->get();

        foreach ($orphanAppointments as $appointment) {
            // Create a synthetic workflow for this orphan appointment
            $syntheticWorkflow = [
                'vehicle' => null,
                'customer' => $appointment->customer,
                'appointments' => [],
                'inspections' => [],
                'estimates' => [],
                'work_orders' => [],
                'payments' => [],
                'has_any_transaction' => true
            ];

            $syntheticWorkflow['appointments'][] = [
                'id' => $appointment->id,
                'date' => $appointment->appointment_date,
                'service_type' => $appointment->appointment_type,
                'status' => $appointment->appointment_status,
                'notes' => $appointment->service_request,
                'vehicle_description' => $appointment->vehicle_description
            ];

            if ($appointment->vehicleInspection) {
                $syntheticWorkflow['inspections'][] = [
                    'id' => $appointment->vehicleInspection->id,
                    'date' => $appointment->vehicleInspection->created_at,
                    'inspection_type' => $appointment->vehicleInspection->inspection_type,
                    'inspection_types' => $appointment->vehicleInspection->inspection_types,
                    'status' => $appointment->vehicleInspection->inspection_type,
                    'technician' => $appointment->vehicleInspection->technician,
                    'customer_concerns' => $appointment->vehicleInspection->customer_concerns
                ];
            }

            if ($appointment->estimate) {
                $syntheticWorkflow['estimates'][] = [
                    'id' => $appointment->estimate->id,
                    'date' => $appointment->estimate->created_at,
                    'estimate_number' => $appointment->estimate->estimate_number,
                    'total_amount' => $appointment->estimate->total_amount,
                    'status' => $appointment->estimate->status,
                    'inspection_id' => $appointment->estimate->inspection_id
                ];
            }

            if ($appointment->workOrder) {
                $wo = $appointment->workOrder;
                $syntheticWorkflow['work_orders'][] = [
                    'id' => $wo->id,
                    'date' => $wo->created_at,
                    'status' => $wo->work_order_status ?? $wo->status,
                    'estimated_total' => $wo->estimated_total ?? 0,
                    'payment_status' => $wo->payment_status ?? 'pending',
                    'balance_due' => $wo->balance_due ?? ($wo->estimated_total ?? 0),
                    'technician_id' => $wo->technician_id,
                    'technician_name' => $wo->technician ? $wo->technician->name : null
                ];
            }

            $workflows[] = $syntheticWorkflow;
        }

        // Filter by section if specified
        if ($section) {
            $filteredWorkflows = [];
            foreach ($workflows as $workflow) {
                if (!empty($workflow[$section])) {
                    $filteredWorkflows[] = $workflow;
                }
            }
            return $filteredWorkflows;
        }

        return $workflows;
    }

    /**
     * Build workflow for a single vehicle (legacy compatibility)
     */
    private function buildVehicleWorkflow(Vehicle $vehicle)
    {
        $workflow = [
            'vehicle' => $vehicle,
            'customer' => $vehicle->customer,
            'appointments' => [],
            'inspections' => [],
            'estimates' => [],
            'work_orders' => [],
            'payments' => [],
            'has_any_transaction' => false
        ];

        // Force reload appointments with a fresh query
        // This bypasses any eager-loading issues
        $appointments = \App\Models\Appointment::where('vehicle_id', $vehicle->id)
            ->with(['vehicleInspection', 'estimate', 'workOrder'])
            ->orderBy('appointment_date', 'desc')
            ->get();

        foreach ($appointments as $appointment) {
            $workflow['appointments'][] = [
                'id' => $appointment->id,
                'date' => $appointment->appointment_date,
                'service_type' => $appointment->appointment_type,
                'status' => $appointment->appointment_status,
                'notes' => $appointment->service_request,
                'transaction_id' => 'APT-' . str_pad($appointment->id, 5, '0', STR_PAD_LEFT)
            ];

            // ANY appointment counts as a transaction!
            $workflow['has_any_transaction'] = true;

            if ($appointment->vehicleInspection) {
                $workflow['inspections'][] = [
                    'id' => $appointment->vehicleInspection->id,
                    'date' => $appointment->vehicleInspection->created_at,
                    'inspection_type' => $appointment->vehicleInspection->inspection_type,
                    'inspection_types' => $appointment->vehicleInspection->inspection_types,
                    'status' => $appointment->vehicleInspection->inspection_type,
                    'technician' => $appointment->vehicleInspection->technician,
                    'customer_concerns' => $appointment->vehicleInspection->customer_concerns,
                    'transaction_id' => 'INS-' . str_pad($appointment->vehicleInspection->id, 5, '0', STR_PAD_LEFT)
                ];
                $workflow['has_any_transaction'] = true;
            }

            if ($appointment->estimate) {
                $workflow['estimates'][] = [
                    'id' => $appointment->estimate->id,
                    'date' => $appointment->estimate->created_at,
                    'estimate_number' => $appointment->estimate->estimate_number,
                    'total_amount' => $appointment->estimate->total_amount,
                    'status' => $appointment->estimate->status,
                    'inspection_id' => $appointment->estimate->inspection_id
                ];
                $workflow['has_any_transaction'] = true;
            }

            if ($appointment->workOrder) {
                $wo = $appointment->workOrder;
                $workflow['work_orders'][] = [
                    'id' => $wo->id,
                    'date' => $wo->created_at,
                    'status' => $wo->work_order_status ?? $wo->status,
                    'estimated_total' => $wo->estimated_total ?? 0,
                    'payment_status' => $wo->payment_status ?? 'pending',
                    'balance_due' => $wo->balance_due ?? ($wo->estimated_total ?? 0),
                    'technician_id' => $wo->technician_id,
                    'technician_name' => $wo->technician ? $wo->technician->name : null
                ];
                $workflow['has_any_transaction'] = true;
            }
        }

        // SPECIAL CASE: Check for estimates directly linked to vehicle (not through appointment)
        $directEstimates = \App\Models\Estimate::where('vehicle_id', $vehicle->id)->get();
        foreach ($directEstimates as $directEstimate) {
            // Check if this estimate is already in the workflow (linked to an appointment)
            $alreadyAdded = false;
            foreach ($workflow['estimates'] as $existingEstimate) {
                if ($existingEstimate['id'] == $directEstimate->id) {
                    $alreadyAdded = true;
                    break;
                }
            }
            
            if (!$alreadyAdded) {
                $workflow['estimates'][] = [
                    'id' => $directEstimate->id,
                    'date' => $directEstimate->created_at,
                    'estimate_number' => $directEstimate->estimate_number,
                    'total_amount' => $directEstimate->total_amount,
                    'status' => $directEstimate->status,
                    'inspection_id' => $directEstimate->inspection_id
                ];
                $workflow['has_any_transaction'] = true;
            }
        }

        // ── Load archived inspections for this vehicle ──
        $archivedInspections = \App\Models\Archive::where('source_module', 'inspection')
            ->whereRaw('JSON_EXTRACT(original_data, "$.vehicle_id") = ?', [$vehicle->id])
            ->orderBy('archived_at', 'desc')
            ->get();

        foreach ($archivedInspections as $archive) {
            $origData = $archive->original_data;
            $workflow['inspections'][] = [
                'id' => $archive->archivable_id,
                'date' => $archive->archived_at,
                'inspection_type' => $origData['inspection_type'] ?? '—',
                'inspection_types' => $origData['inspection_types'] ?? null,
                'status' => 'archived',
                'technician' => null,
                'customer_concerns' => $origData['customer_concerns'] ?? '—',
                'is_archived' => true,
                'archive_id' => $archive->id,
                'transaction_id' => 'INS-' . str_pad($origData['id'] ?? $archive->archivable_id, 5, '0', STR_PAD_LEFT),
            ];
            // Archived inspections count as transactions too
            $workflow['has_any_transaction'] = true;
        }

        return $workflow;
    }

    /**
     * Get summary counts for dashboard
     */
    public function getSummaryCounts()
    {
        $recentDate = now()->subDays(90);

        // Get all recent appointments
        $allAppointments = Appointment::with(['customer', 'vehicle'])
            ->where('appointment_date', '>=', $recentDate)
            // Include all appointments including cancelled
            ->get();

        // Count scheduled appointments (excluding those with repair orders)
        $scheduledCount = 0;
        foreach ($allAppointments as $appointment) {
            $hasRepairOrder = WorkOrder::where('appointment_id', $appointment->id)->exists();
            if (!$hasRepairOrder) {
                $scheduledCount++;
            }
        }

        // Count repair orders (work orders)
        $repairOrderCount = WorkOrder::where('created_at', '>=', $recentDate)->count();

        // Count estimates
        $estimateCount = Estimate::where('created_at', '>=', $recentDate)->count();

        // Count job orders (same as repair orders for now)
        $jobOrderCount = $repairOrderCount;

        return [
            'scheduledCount' => $scheduledCount,
            'repairOrderCount' => $repairOrderCount,
            'estimateCount' => $estimateCount,
            'jobOrderCount' => $jobOrderCount
        ];
    }

    /**
     * Extract specific section data from workflows
     */
    public function extractSectionData($workflows, $section)
    {
        $sectionData = [];

        foreach ($workflows as $workflow) {
            if (!empty($workflow[$section])) {
                foreach ($workflow[$section] as $item) {
                    $item['vehicle'] = $workflow['vehicle'];
                    $item['customer'] = $workflow['customer'];
                    $sectionData[] = $item;
                }
            }
        }

        return $sectionData;
    }
}