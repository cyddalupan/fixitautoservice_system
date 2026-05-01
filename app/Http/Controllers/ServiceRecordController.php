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
        
        // ── Apply filters ──
        if ($search) {
            $workflows = array_filter($workflows, function($wf) use ($search) {
                $q = strtolower($search);
                $customer = $wf['customer'] ?? null;
                $vehicle = $wf['vehicle'] ?? null;
                $match = false;
                if ($customer) {
                    $match = $match || str_contains(strtolower($customer->first_name ?? ''), $q);
                    $match = $match || str_contains(strtolower($customer->last_name ?? ''), $q);
                    $match = $match || str_contains(strtolower($customer->phone ?? ''), $q);
                }
                if ($vehicle) {
                    $match = $match || str_contains(strtolower($vehicle->license_plate ?? ''), $q);
                    $match = $match || str_contains(strtolower($vehicle->vin ?? ''), $q);
                    $match = $match || str_contains(strtolower($vehicle->make ?? ''), $q);
                    $match = $match || str_contains(strtolower($vehicle->model ?? ''), $q);
                }
                foreach ($wf['work_orders'] as $wo) {
                    $invoice = $wo['invoice_number'] ?? '';
                    $match = $match || str_contains(strtolower($invoice), $q);
                }
                return $match;
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
