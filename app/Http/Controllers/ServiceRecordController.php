<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use App\Models\WorkOrder;
use App\Services\ServiceRecordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceRecordController extends Controller
{
    protected $serviceRecordService;
    
    public function __construct(ServiceRecordService $serviceRecordService)
    {
        $this->serviceRecordService = $serviceRecordService;
    }
    
    public function index(Request $request)
    {
        $customerId = $request->input('customer_id');
        $vehicleId = $request->input('vehicle_id');
        $search = $request->input('search');
        $stage = $request->input('stage');
        $technicianId = $request->input('technician_id');
        $dateRange = $request->input('date_range');
        
        // Get workflows from centralized service
        $workflows = $this->serviceRecordService->getWorkflows($customerId, $vehicleId);
        
        // ── Apply filters (enhanced multi-keyword search) ──
        if ($search) {
            $keywords = preg_split('/\s+/', trim($search));
            $keywords = array_filter($keywords, fn($k) => strlen($k) > 0);
            $keywords = array_map('strtolower', $keywords);

            $workflows = array_filter($workflows, function($wf) use ($keywords) {
                $customer = $wf['customer'] ?? null;
                $vehicle = $wf['vehicle'] ?? null;

                // Build a single searchable text block for this workflow
                $haystackParts = [];

                // Customer fields
                if ($customer) {
                    $haystackParts[] = $customer->first_name ?? '';
                    $haystackParts[] = $customer->last_name ?? '';
                    $haystackParts[] = $customer->phone ?? '';
                }

                // Vehicle fields
                if ($vehicle) {
                    $haystackParts[] = $vehicle->license_plate ?? '';
                    $haystackParts[] = $vehicle->vin ?? '';
                    $haystackParts[] = $vehicle->make ?? '';
                    $haystackParts[] = $vehicle->model ?? '';
                }

                // Appointments — service_type, status, appointment_number
                foreach ($wf['appointments'] ?? [] as $a) {
                    $haystackParts[] = $a['service_type'] ?? '';
                    $haystackParts[] = $a['status'] ?? '';
                    $haystackParts[] = $a['appointment_number'] ?? '';
                    $haystackParts[] = $a['transaction_id'] ?? '';
                    $haystackParts[] = $a['notes'] ?? '';
                }

                // Estimates — estimate_number, status
                foreach ($wf['estimates'] ?? [] as $e) {
                    $haystackParts[] = $e['estimate_number'] ?? '';
                    $haystackParts[] = $e['status'] ?? '';
                }

                // Work orders — status, invoice_number
                foreach ($wf['work_orders'] ?? [] as $wo) {
                    $haystackParts[] = $wo['status'] ?? '';
                    $haystackParts[] = $wo['invoice_number'] ?? '';
                }

                $haystack = strtolower(implode(' ', $haystackParts));

                // All keywords must match (AND logic)
                foreach ($keywords as $kw) {
                    if (!str_contains($haystack, $kw)) {
                        return false;
                    }
                }
                return true;
            });
        }

        if ($technicianId) {
            $workflows = array_filter($workflows, function($wf) use ($technicianId) {
                foreach ($wf['work_orders'] as $wo) {
                    if (($wo['technician_id'] ?? 0) == $technicianId) return true;
                }
                return false;
            });
        }

        if ($dateRange) {
            $workflows = array_filter($workflows, function($wf) use ($dateRange) {
                $days = match($dateRange) { 'today'=>0, 'week'=>7, 'month'=>30, 'quarter'=>90, default=>null };
                if ($days === null) return true;
                $cutoff = now()->subDays($days);
                foreach ($wf['work_orders'] as $wo) {
                    if (isset($wo['date']) && $wo['date'] >= $cutoff) return true;
                }
                foreach ($wf['appointments'] as $a) {
                    if (isset($a['date']) && $a['date'] >= $cutoff) return true;
                }
                return false;
            });
        }

        // Stage filter (post-processing)
        $stageSteps = ['booked','checked_in','inspection','estimate','approved','in_progress','ready','paid','completed'];
        if ($stage === 'archived') {
            $workflows = array_filter($workflows, function($wf) {
                foreach ($wf['inspections'] ?? [] as $insp) {
                    if (!empty($insp['is_archived'])) return true;
                }
                return false;
            });
        } elseif ($stage) {
            $workflows = array_filter($workflows, function($wf) use ($stage, $stageSteps) {
                $currentStage = $this->determineStage($wf);
                return $currentStage === $stage;
            });
        }

        $workflows = array_values($workflows); // re-index
        
        // Get summary counts from service
        $summaryCounts = $this->serviceRecordService->getSummaryCounts();
        $scheduledCount = $summaryCounts['scheduledCount'];
        $repairOrderCount = $summaryCounts['repairOrderCount'];
        $estimateCount = $summaryCounts['estimateCount'];
        $jobOrderCount = $summaryCounts['jobOrderCount'];
        
        $customers = Customer::orderBy('last_name')->get();
        $vehicles = Vehicle::orderBy('make')->get();
        $technicians = User::whereHas('workOrders')->orWhere('role', 'technician')->orderBy('name')->get();
        $technicians = User::where(function($q) { $q->whereHas('workOrders')->orWhere('role', 'technician'); })->orderBy('name')->get();

        // Compute total for sidebar
        $serviceRecordsTotal = $scheduledCount + $repairOrderCount + $estimateCount + $jobOrderCount;
        \Illuminate\Support\Facades\View::share('serviceRecordsTotal', $serviceRecordsTotal);

        // Empty collections for compatibility
        $appointments = collect([]);
        $inspections = collect([]);
        $estimates = collect([]);
        $workOrders = collect([]);
        $payments = collect([]);
        
        return view('service_records.index', compact(
            'workflows', 'customers', 'vehicles', 'customerId', 'vehicleId',
            'scheduledCount', 'repairOrderCount', 'estimateCount', 'jobOrderCount',
            'appointments', 'inspections', 'estimates', 'workOrders', 'payments',
            'technicians'
        ));
    }

    /**
     * Determine the current workflow stage
     */
    private function determineStage(array $wf): string
    {
        $hasWO = count($wf['work_orders']) > 0;
        $hasEst = count($wf['estimates']) > 0;
        $hasInsp = count($wf['inspections']) > 0;
        $hasAppts = count($wf['appointments']) > 0;

        if ($hasWO) {
            $s = $wf['work_orders'][0]['status'] ?? '';
            return match($s) {
                'pending' => 'checked_in',
                'in_progress' => 'in_progress',
                'completed' => 'completed',
                'cancelled' => 'cancelled',
                'waiting_parts' => 'waiting_parts',
                default => 'checked_in'
            };
        }
        if ($hasEst) {
            return ($wf['estimates'][0]['status'] ?? '') === 'approved' ? 'approved' : 'estimate';
        }
        if ($hasInsp) return 'inspection';
        if ($hasAppts) return ($wf['appointments'][0]['status'] ?? '') === 'checked_in' ? 'checked_in' : 'booked';
        return 'booked';
    }
}
