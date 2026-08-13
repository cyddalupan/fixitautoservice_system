<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\JobOrderItem;
use App\Models\JobOrderTask;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Estimate;
use App\Models\ServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JobOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JobOrder::with(['customer', 'vehicle', 'technician', 'serviceAdvisor', 'invoice'])
            ->latest();
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('job_order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vehicle', function($q) use ($search) {
                      $q->where('license_plate', 'like', "%{$search}%")
                        ->orWhere('make', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('job_order_status', $request->status);
        }
        
        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('job_order_date', $request->date);
        } elseif ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('job_order_date', [$dates[0], $dates[1]]);
            }
        }
        
        // Technician filter
        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }
        
        // Service advisor filter
        if ($request->filled('service_advisor_id')) {
            $query->where('service_advisor_id', $request->service_advisor_id);
        }
        
        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Warranty filter
        if ($request->filled('warranty')) {
            $query->where('is_warranty_work', $request->warranty == 'yes');
        }
        
        // Insurance filter
        if ($request->filled('insurance')) {
            $query->where('is_insurance_work', $request->insurance == 'yes');
        }
        
        // Overdue filter
        if ($request->filled('overdue')) {
            $query->where(function($q) {
                $q->where('payment_status', 'overdue')
                  ->orWhere(function($q2) {
                      $q2->where('payment_due_date', '<', Carbon::today())
                         ->where('balance_due', '>', 0);
                  });
            });
        }
        
        $jobOrders = $query->paginate(20);
        
        // Get technicians and advisors for filter dropdowns
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $advisors = User::where('role', 'service_advisor')->where('is_active', true)->get();
        
        // Get statistics
        $stats = [
            'total' => JobOrder::count(),
            'today' => JobOrder::today()->count(),
            'repairing' => JobOrder::repairing()->count(),
            'pending' => JobOrder::pending()->count(),
            'waiting_parts' => JobOrder::waitingParts()->count(),
            'completed' => JobOrder::completed()->count(),
            'released' => JobOrder::released()->count(),
            'overdue' => JobOrder::overdue()->count(),
            'warranty' => JobOrder::warranty()->count(),
            'insurance' => JobOrder::insurance()->count(),
        ];
        
        return view('job_orders.index', compact('jobOrders', 'technicians', 'advisors', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $vehicles = Vehicle::with('customer')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $advisors = User::where('role', 'service_advisor')->where('is_active', true)->get();
        $appointments = Appointment::where('appointment_status', 'scheduled')
            ->orWhere('appointment_status', 'confirmed')
            ->with(['customer', 'vehicle'])
            ->get();
        
        // Pre-select appointment if provided
        $selectedAppointment = $request->filled('appointment_id') 
            ? Appointment::with(['customer', 'vehicle'])->find($request->appointment_id)
            : null;
        
        // Pre-select vehicle if provided
        $selectedVehicle = $request->filled('vehicle_id') 
            ? Vehicle::with('customer')->find($request->vehicle_id)
            : null;
        
        // Pre-select customer if provided OR get from selected vehicle/appointment
        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);
        } elseif ($selectedVehicle && $selectedVehicle->customer) {
            // If vehicle is provided but customer isn't, get customer from vehicle
            $selectedCustomer = $selectedVehicle->customer;
        } elseif ($selectedAppointment && $selectedAppointment->customer) {
            // If appointment is provided but customer isn't, get customer from appointment
            $selectedCustomer = $selectedAppointment->customer;
        } else {
            $selectedCustomer = null;
        }
        
        // Get customer vehicles and history for summary card
        $customerVehicles = $selectedCustomer 
            ? $selectedCustomer->vehicles()->orderBy('created_at', 'desc')->get() 
            : collect();
        $customerHistory = $selectedCustomer
            ? Appointment::where('customer_id', $selectedCustomer->id)
                ->where('appointment_date', '>=', now()->subDays(90))
                ->orderBy('appointment_date', 'desc')
                ->get()
            : collect();
        
        // Common service templates
        $serviceTemplates = $this->getServiceTemplates();
        
        // Check for active transactions on the selected vehicle
        $activeTransaction = null;
        if ($selectedVehicle) {
            $activeTransaction = \App\Services\ActiveTransactionService::checkActiveTransaction($selectedVehicle->id);
        }
        
        // Service catalog items for line items
        $serviceItems = ServiceItem::where('is_active', true)->orderBy('name')->get();
        
        return view('job_orders.create', compact(
            'customers', 
            'vehicles', 
            'technicians', 
            'advisors', 
            'appointments',
            'selectedAppointment',
            'selectedCustomer',
            'selectedVehicle',
            'customerVehicles',
            'customerHistory',
            'serviceTemplates',
            'activeTransaction',
            'serviceItems'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_advisor_id' => 'nullable|exists:users,id',
            'technician_id' => 'nullable|exists:users,id',
            'job_order_date' => 'required|date',
            'job_order_type' => 'required|in:repair,maintenance,inspection,diagnostic,recall,other',
            'priority' => 'required|in:low,normal,high,emergency',
            'odometer_in' => 'nullable|integer|min:0',
            'fuel_level' => 'nullable|in:full,3/4,1/2,1/4,empty',
            'vehicle_condition' => 'nullable|string|max:1000',
            'customer_concerns' => 'required|string|max:2000',
            'customer_complaints' => 'nullable|string|max:2000',
            'initial_diagnosis' => 'nullable|string|max:2000',
            'recommended_services' => 'nullable|string|max:2000',
            'additional_notes' => 'nullable|string|max:1000',
            'estimated_labor_hours' => 'nullable|numeric|min:0',
            'estimated_labor_cost' => 'nullable|numeric|min:0',
            'estimated_parts_cost' => 'nullable|numeric|min:0',
            'estimated_tax' => 'nullable|numeric|min:0',
            'estimate_notes' => 'nullable|string|max:1000',
            'is_warranty_work' => 'boolean',
            'warranty_type' => 'nullable|required_if:is_warranty_work,true|string|max:100',
            'warranty_number' => 'nullable|string|max:100',
            'warranty_expiry' => 'nullable|date',
            'warranty_coverage' => 'nullable|numeric|min:0',
            'is_insurance_work' => 'boolean',
            'insurance_company' => 'nullable|required_if:is_insurance_work,true|string|max:100',
            'insurance_claim_number' => 'nullable|string|max:100',
            'insurance_adjuster' => 'nullable|string|max:100',
            'insurance_deductible' => 'nullable|numeric|min:0',
            'bay_number' => 'nullable|integer|min:1|max:20',
            'requires_customer_approval' => 'boolean',
            'requires_manager_approval' => 'boolean',
            'is_rush_order' => 'boolean',
            'is_complex_job' => 'boolean',
            'has_safety_concerns' => 'boolean',
            'service_template' => 'nullable|string',
            'service_type' => 'nullable|array',
            'service_type.*' => 'string|in:' . implode(',', \App\Models\ServiceType::keys()),
            'items' => 'nullable|array',
            'items.*.item_type' => 'required|in:labor,part,sublet,fee,tax,discount',
            'items.*.description' => 'required|string|max:500',
            'items.*.part_number' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.is_warranty' => 'boolean',
            'items.*.is_insurance' => 'boolean',
            'items.*.notes' => 'nullable|string|max:500',
        ]);
        
        // Check for duplicate vehicle in active transactions (skip if override_duplicate is set)
        if (!$request->filled('override_duplicate') || $request->override_duplicate !== '1') {
            if ($request->filled('vehicle_id')) {
                $vehicleId = $request->integer('vehicle_id');

                // Exclude appointments whose inspections have been archived
                $archivedInspectionIds = \App\Models\Archive::where('archivable_type', 'App\\Models\\VehicleInspection')
                    ->pluck('archivable_id')->toArray();
                $archivedAppointmentIds = [];
                if (!empty($archivedInspectionIds)) {
                    $archivedAppointmentIds = \App\Models\VehicleInspection::withTrashed()->whereIn('id', $archivedInspectionIds)
                        ->where('vehicle_id', $vehicleId)
                        ->whereNotNull('appointment_id')
                        ->pluck('appointment_id')->toArray();
                }

                $existingAppointment = Appointment::where('vehicle_id', $vehicleId)
                    ->whereIn('appointment_status', ['scheduled', 'checked_in', 'in_progress'])
                    ->whereNull('deleted_at')
                    ->where(function($q) use ($archivedAppointmentIds) {
                        if (!empty($archivedAppointmentIds)) {
                            $q->whereNotIn('id', $archivedAppointmentIds);
                        }
                    })
                    ->first();
                
                if ($existingAppointment) {
                    return back()->withErrors([
                        'vehicle_id' => 'This vehicle already has an active Appointment (' . $existingAppointment->appointment_number . ').'
                    ])->withInput();
                }
                
                // Exclude current work order if editing
                $existingJobOrder = JobOrder::where('vehicle_id', $vehicleId)
                    ->whereIn('job_order_status', ['pending', 'repairing', 'waiting_parts'])
                    ->whereNull('deleted_at');
                
                if ($existingJobOrder->exists()) {
                    return back()->withErrors([
                        'vehicle_id' => 'This vehicle already has an active Work Order (' . $existingJobOrder->first()->job_order_number . ').'
                    ])->withInput();
                }
                
                $existingEstimate = Estimate::where('vehicle_id', $vehicleId)
                    ->whereIn('status', ['draft', 'pending', 'sent'])
                    ->whereNull('deleted_at')
                    ->first();
                
                if ($existingEstimate) {
                    return back()->withErrors([
                        'vehicle_id' => 'This vehicle already has an active Estimate (' . ($existingEstimate->estimate_number ?? '#' . $existingEstimate->id) . ').'
                    ])->withInput();
                }
            }
        }
        
        // Generate work order number
        $validated['job_order_number'] = JobOrder::generateJobOrderNumber();
        
        // Set initial status
        $validated['job_order_status'] = 'pending';
        if ($validated['requires_customer_approval'] ?? false) {
            $validated['job_order_status'] = 'pending';
        }
        
        // Set check-in time
        $validated['check_in_time'] = now();
        
        // Calculate estimated total
        $validated['estimated_total'] = ($validated['estimated_labor_cost'] ?? 0) + 
                                       ($validated['estimated_parts_cost'] ?? 0) + 
                                       ($validated['estimated_tax'] ?? 0);
        
        // Set payment due date (30 days from today)
        $validated['payment_due_date'] = Carbon::today()->addDays(30);
        
        // Create work order
        $jobOrder = JobOrder::create($validated);
        
        // Create items if provided
        if (isset($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                JobOrderItem::create([
                    'job_order_id' => $jobOrder->id,
                    'item_type' => $itemData['item_type'],
                    'description' => $itemData['description'],
                    'part_number' => $itemData['part_number'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'] ?? 'each',
                    'unit_cost' => $itemData['unit_cost'],
                    'is_estimate' => true,
                    'is_warranty' => $itemData['is_warranty'] ?? false,
                    'is_insurance' => $itemData['is_insurance'] ?? false,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }
        }
        
        // Apply service template if selected
        if ($request->filled('service_template')) {
            $this->applyServiceTemplate($jobOrder, $request->service_template);
        }
        
        // Handle multi-select service types
        if ($request->filled('service_type')) {
            $serviceTypes = $request->service_type;
            $jobOrder->service_type = is_array($serviceTypes) ? json_encode($serviceTypes) : $serviceTypes;
            $jobOrder->save();
        }
        
        // Update appointment status if linked
        if ($jobOrder->appointment_id) {
            $appointment = Appointment::find($jobOrder->appointment_id);
            if ($appointment) {
                $appointment->update([
                    'appointment_status' => 'checked_in',
                    'checked_in_at' => now(),
                ]);
            }
        }
        
        return redirect()->route('job-orders.show', $jobOrder)
            ->with('success', 'Work order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(JobOrder $jobOrder)
    {
        // Mark as viewed if not yet viewed
        if ($jobOrder->viewed_at === null) {
            $jobOrder->update(['viewed_at' => now()]);
        }
        
        $jobOrder->load([
            'customer', 
            'vehicle', 
            'technician', 
            'serviceAdvisor', 
            'qualityChecker',
            'items',
            'tasks.assignedTechnician',
            'appointment',
            'serviceProgress',
            'vehicleInspection.inspectionFindings'
        ]);
        
        // Get similar work orders for this customer
        $customerJobOrders = JobOrder::where('customer_id', $jobOrder->customer_id)
            ->where('id', '!=', $jobOrder->id)
            ->orderBy('job_order_date', 'desc')
            ->limit(5)
            ->get();
        
        // Get timeline
        $timeline = $jobOrder->getTimeline();
        
        // Get technicians for task assignment
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        
        return view('job_orders.show', compact(
            'jobOrder', 
            'customerJobOrders', 
            'timeline',
            'technicians'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobOrder $jobOrder)
    {
        // Mark as viewed if not yet viewed
        if ($jobOrder->viewed_at === null) {
            $jobOrder->update(['viewed_at' => now()]);
        }
        
        $jobOrder->load(['customer', 'vehicle', 'items', 'tasks']);
        
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $vehicles = Vehicle::with('customer')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $advisors = User::where('role', 'service_advisor')->where('is_active', true)->get();
        $appointments = Appointment::where('appointment_status', 'scheduled')
            ->orWhere('appointment_status', 'confirmed')
            ->with(['customer', 'vehicle'])
            ->get();
        
        return view('job_orders.edit', compact(
            'jobOrder', 
            'customers', 
            'vehicles', 
            'technicians', 
            'advisors', 
            'appointments'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobOrder $jobOrder)
    {
        \Log::info('JobOrder update attempt', [
            'job_order_id' => $jobOrder->id,
            'request_data' => $request->all(),
            'current_status' => $jobOrder->job_order_status,
        ]);
        
        $validated = $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_advisor_id' => 'nullable|exists:users,id',
            'technician_id' => 'nullable|exists:users,id',
            'job_order_date' => 'required|date',
            'job_order_status' => 'required|in:pending,repairing,waiting_parts,completed,released,cancelled,draft',
            'priority' => 'required|in:low,normal,high,emergency',
            'job_order_type' => 'nullable|in:repair,maintenance,inspection,diagnostic,recall,other',
            'odometer_in' => 'nullable|integer|min:0',
            'fuel_level' => 'nullable|in:full,3/4,1/2,1/4,empty',
            'vehicle_condition' => 'nullable|string|max:1000',
            'customer_concerns' => 'nullable|string|max:2000',
            'customer_complaints' => 'nullable|string|max:2000',
            'initial_diagnosis' => 'nullable|string|max:2000',
            'recommended_services' => 'nullable|string|max:2000',
            'additional_notes' => 'nullable|string|max:1000',
            'estimated_labor_hours' => 'nullable|numeric|min:0',
            'estimated_labor_cost' => 'nullable|numeric|min:0',
            'estimated_parts_cost' => 'nullable|numeric|min:0',
            'estimated_tax' => 'nullable|numeric|min:0',
            'estimate_notes' => 'nullable|string|max:1000',
            'is_warranty_work' => 'boolean',
            'warranty_type' => 'nullable|string|max:100',
            'warranty_number' => 'nullable|string|max:100',
            'warranty_expiry' => 'nullable|date',
            'warranty_coverage' => 'nullable|numeric|min:0',
            'is_insurance_work' => 'boolean',
            'insurance_company' => 'nullable|string|max:100',
            'insurance_claim_number' => 'nullable|string|max:100',
            'insurance_adjuster' => 'nullable|string|max:100',
            'insurance_deductible' => 'nullable|numeric|min:0',
            'bay_number' => 'nullable|integer|min:1|max:20',
            'requires_customer_approval' => 'boolean',
            'technician_assignments' => 'nullable|json',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        \Log::info('JobOrder validation passed', [
            'job_order_id' => $jobOrder->id,
            'validated_data' => $validated,
        ]);
        
        // Update status timestamps
        if ($validated['job_order_status'] !== $jobOrder->job_order_status) {
            $statusField = null;
            switch ($validated['job_order_status']) {
                case 'repairing':
                    $statusField = 'work_start_time';
                    break;
                case 'completed':
                    $statusField = 'work_complete_time';
                    break;
                case 'released':
                    $statusField = 'invoice_sent_time';
                    break;
                case 'cancelled':
                    $statusField = 'cancelled_at';
                    break;
            }
            
            if ($statusField) {
                $validated[$statusField] = now();
            }
        }
        
        $jobOrder->update($validated);
        
        \Log::info('JobOrder update successful', [
            'job_order_id' => $jobOrder->id,
            'new_status' => $jobOrder->job_order_status,
            'new_technician_id' => $jobOrder->technician_id,
            'new_service_advisor_id' => $jobOrder->service_advisor_id,
        ]);
        
        return redirect()->route('job-orders.show', $jobOrder)
            ->with('success', 'Work order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobOrder $jobOrder)
    {
        $jobOrder->delete();
        
        return redirect()->route('job-orders.index')
            ->with('success', 'Work order deleted successfully.');
    }
    
    /**
     * Approve work order estimate.
     */
    public function approveEstimate(JobOrder $jobOrder)
    {
        if ($jobOrder->approveEstimate()) {
            return redirect()->back()->with('success', 'Work order estimate approved.');
        }
        
        return redirect()->back()->with('error', 'Unable to approve estimate.');
    }
    
    /**
     * Start work on work order.
     */
    public function startWork(JobOrder $jobOrder)
    {
        if ($jobOrder->startWork()) {
            return redirect()->back()->with('success', 'Work started on work order.');
        }
        
        return redirect()->back()->with('error', 'Unable to start work.');
    }
    
    /**
     * Complete work on work order.
     */
    public function completeWork(JobOrder $jobOrder)
    {
        if ($jobOrder->completeWork()) {
            return redirect()->back()->with('success', 'Work completed on work order.');
        }
        
        return redirect()->back()->with('error', 'Unable to complete work.');
    }
    
    /**
     * Mark work order as invoiced.
     */
    public function markAsReleased(JobOrder $jobOrder)
    {
        if ($jobOrder->markAsReleased()) {
            return redirect()->back()->with('success', 'Work order marked as released.');
        }
        
        return redirect()->back()->with('error', 'Unable to mark as released.');
    }
    
    /**
     * Add payment to work order.
     */
    public function addPayment(JobOrder $jobOrder, Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:100',
            'payment_notes' => 'nullable|string|max:500',
        ]);
        
        if ($jobOrder->addPayment($validated['amount'])) {
            // In production, you would create a Payment record here
            return redirect()->back()->with('success', 'Payment added successfully.');
        }
        
        return redirect()->back()->with('error', 'Unable to add payment.');
    }
    
    /**
     * Print work order.
     */
    public function print(JobOrder $jobOrder)
    {
        $jobOrder->load(['customer', 'vehicle', 'technician', 'serviceAdvisor', 'items']);
        
        // In production, you would generate a PDF here
        // For now, return a view that can be printed
        return view('job_orders.print', compact('jobOrder'));
    }
    
    /**
     * Get work order statistics.
     */
    public function statistics()
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        
        // Daily statistics
        $dailyStats = [
            'total' => JobOrder::whereDate('job_order_date', $today)->count(),
            'pending' => JobOrder::whereDate('job_order_date', $today)->where('job_order_status', 'pending')->count(),
            'repairing' => JobOrder::whereDate('job_order_date', $today)->where('job_order_status', 'repairing')->count(),
            'waiting_parts' => JobOrder::whereDate('job_order_date', $today)->where('job_order_status', 'waiting_parts')->count(),
            'completed' => JobOrder::whereDate('job_order_date', $today)->where('job_order_status', 'completed')->count(),
            'released' => JobOrder::whereDate('job_order_date', $today)->where('job_order_status', 'released')->count(),
            'revenue' => JobOrder::whereDate('job_order_date', $today)->where('job_order_status', 'completed')->sum('final_amount'),
        ];
        
        // Weekly statistics
        $weeklyStats = [
            'total' => JobOrder::whereBetween('job_order_date', [$weekStart, $weekEnd])->count(),
            'by_type' => JobOrder::whereBetween('job_order_date', [$weekStart, $weekEnd])
                ->groupBy('job_order_type')
                ->selectRaw('job_order_type, count(*) as count')
                ->pluck('count', 'job_order_type'),
            'by_status' => JobOrder::whereBetween('job_order_date', [$weekStart, $weekEnd])
                ->groupBy('job_order_status')
                ->selectRaw('job_order_status, count(*) as count')
                ->pluck('count', 'job_order_status'),
            'revenue' => JobOrder::whereBetween('job_order_date', [$weekStart, $weekEnd])
                ->where('job_order_status', 'completed')
                ->sum('final_amount'),
        ];
        
        // Monthly statistics
        $monthlyStats = [
            'total' => JobOrder::whereBetween('job_order_date', [$monthStart, $monthEnd])->count(),
            'revenue' => JobOrder::whereBetween('job_order_date', [$monthStart, $monthEnd])
                ->where('job_order_status', 'completed')
                ->sum('final_amount'),
            'avg_turnaround' => JobOrder::whereBetween('job_order_date', [$monthStart, $monthEnd])
                ->where('job_order_status', 'completed')
                ->whereNotNull('work_complete_time')
                ->whereNotNull('check_in_time')
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, check_in_time, work_complete_time)')),
            'profit_margin' => JobOrder::whereBetween('job_order_date', [$monthStart, $monthEnd])
                ->where('job_order_status', 'completed')
                ->avg('profit_margin'),
        ];
        
        // Technician performance
        $technicianStats = User::where('role', 'technician')
            ->where('is_active', true)
            ->withCount(['jobOrders as completed_job_orders' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('job_order_date', [$monthStart, $monthEnd])
                      ->where('job_order_status', 'completed');
            }])
            ->withSum(['jobOrders as total_revenue' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('job_order_date', [$monthStart, $monthEnd])
                      ->where('job_order_status', 'completed');
            }], 'final_amount')
            ->withAvg(['jobOrders as avg_turnaround' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('job_order_date', [$monthStart, $monthEnd])
                      ->where('job_order_status', 'completed')
                      ->whereNotNull('work_complete_time')
                      ->whereNotNull('check_in_time');
            }], DB::raw('TIMESTAMPDIFF(HOUR, check_in_time, work_complete_time)'))
            ->get();
        
        return view('job_orders.statistics', compact('dailyStats', 'weeklyStats', 'monthlyStats', 'technicianStats'));
    }
    
    /**
     * Get service templates.
     */
    private function getServiceTemplates(): array
    {
        // Use the centralized service types config
        $templates = [];
        $list = \App\Models\ServiceType::list();
        foreach ($list as $key => $info) {
            $name = is_array($info) ? ($info['name'] ?? ucfirst(str_replace('_', ' ', $key))) : $info;
            $templates[$key] = [
                'name' => $name,
                'description' => $name . ' - Standard service',
                'items' => [
                    [
                        'item_type' => 'labor',
                        'description' => $name . ' Labor',
                        'quantity' => 1.0,
                        'unit' => 'hours',
                        'unit_cost' => 85.00,
                    ],
                ],
            ];
        }
        return $templates;
    }
    
    /**
     * Apply service template to work order.
     */
    private function applyServiceTemplate(JobOrder $jobOrder, string $templateKey): void
    {
        $templates = $this->getServiceTemplates();
        
        if (!isset($templates[$templateKey])) {
            return;
        }
        
        $template = $templates[$templateKey];
        
        // Set service_type from template
        $jobOrder->service_type = json_encode([$templateKey]);
        $jobOrder->save();
        
        // Update work order description
        $jobOrder->update([
            'customer_concerns' => $template['description'],
            'job_order_type' => 'maintenance',
        ]);
        
        // Create template items
        foreach ($template['items'] as $itemData) {
            JobOrderItem::create([
                'job_order_id' => $jobOrder->id,
                'item_type' => $itemData['item_type'],
                'description' => $itemData['description'],
                'part_number' => $itemData['part_number'] ?? null,
                'quantity' => $itemData['quantity'],
                'unit' => $itemData['unit'],
                'unit_cost' => $itemData['unit_cost'],
                'is_estimate' => true,
            ]);
        }
        
        // Calculate estimated totals
        $jobOrder->calculateTotals();
    }
    
    /**
     * Update repair approval status via AJAX.
     */
    public function updateRepairApproval(Request $request, JobOrder $jobOrder)
    {
        // Validate request
        $request->validate([
            'repair_approval_status' => 'required|in:pending,go,no_go'
        ]);
        
        // Check permissions - only staff can update
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in.'
            ], 403);
        }
        
        $user = auth()->user();
        $allowedRoles = ['super_admin', 'admin', 'office_staff', 'technician', 'service_advisor', 'manager'];
        
        // Check if user has an allowed role
        if (!in_array($user->role, $allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only staff members can update repair approval status.'
            ], 403);
        }
        
        // Update the status
        $jobOrder->update([
            'repair_approval_status' => $request->repair_approval_status
        ]);
        
        // Log the action (commented out for now - activity log package might not be installed)
        // activity()
        //     ->causedBy(auth()->user())
        //     ->performedOn($jobOrder)
        //     ->withProperties([
        //         'old_status' => $jobOrder->getOriginal('repair_approval_status'),
        //         'new_status' => $request->repair_approval_status
        //     ])
        //     ->log('updated repair approval status');
        
        return response()->json([
            'success' => true,
            'message' => 'Repair approval status updated successfully.',
            'data' => [
                'id' => $jobOrder->id,
                'repair_approval_status' => $jobOrder->repair_approval_status,
                'status_text' => $jobOrder->repair_approval_status == 'go' ? 'Authorized (GO)' : 
                                ($jobOrder->repair_approval_status == 'no_go' ? 'Not Cleared (NO GO)' : 'Pending Review')
            ]
        ]);
    }
}