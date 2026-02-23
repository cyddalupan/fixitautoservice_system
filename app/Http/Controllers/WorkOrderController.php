<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\WorkOrderTask;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkOrder::with(['customer', 'vehicle', 'technician', 'serviceAdvisor'])
            ->latest();
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('work_order_number', 'like', "%{$search}%")
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
            $query->where('work_order_status', $request->status);
        }
        
        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('work_order_date', $request->date);
        } elseif ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('work_order_date', [$dates[0], $dates[1]]);
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
        
        $workOrders = $query->paginate(20);
        
        // Get technicians and advisors for filter dropdowns
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $advisors = User::where('role', 'service_advisor')->where('is_active', true)->get();
        
        // Get statistics
        $stats = [
            'total' => WorkOrder::count(),
            'today' => WorkOrder::today()->count(),
            'in_progress' => WorkOrder::inProgress()->count(),
            'pending_approval' => WorkOrder::where('work_order_status', 'pending_approval')->count(),
            'completed' => WorkOrder::completed()->count(),
            'overdue' => WorkOrder::overdue()->count(),
            'warranty' => WorkOrder::warranty()->count(),
            'insurance' => WorkOrder::insurance()->count(),
        ];
        
        return view('work_orders.index', compact('workOrders', 'technicians', 'advisors', 'stats'));
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
        
        // Pre-select customer if provided
        $selectedCustomer = $request->filled('customer_id') 
            ? Customer::find($request->customer_id)
            : null;
        
        // Pre-select vehicle if provided
        $selectedVehicle = $request->filled('vehicle_id') 
            ? Vehicle::with('customer')->find($request->vehicle_id)
            : null;
        
        // Common service templates
        $serviceTemplates = $this->getServiceTemplates();
        
        return view('work_orders.create', compact(
            'customers', 
            'vehicles', 
            'technicians', 
            'advisors', 
            'appointments',
            'selectedAppointment',
            'selectedCustomer',
            'selectedVehicle',
            'serviceTemplates'
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
            'service_advisor_id' => 'required|exists:users,id',
            'technician_id' => 'nullable|exists:users,id',
            'work_order_date' => 'required|date',
            'work_order_type' => 'required|in:repair,maintenance,inspection,diagnostic,recall,other',
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
        
        // Generate work order number
        $validated['work_order_number'] = WorkOrder::generateWorkOrderNumber();
        
        // Set initial status
        $validated['work_order_status'] = 'draft';
        if ($validated['requires_customer_approval'] ?? false) {
            $validated['work_order_status'] = 'pending_approval';
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
        $workOrder = WorkOrder::create($validated);
        
        // Create items if provided
        if (isset($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                WorkOrderItem::create([
                    'work_order_id' => $workOrder->id,
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
            $this->applyServiceTemplate($workOrder, $request->service_template);
        }
        
        // Update appointment status if linked
        if ($workOrder->appointment_id) {
            $appointment = Appointment::find($workOrder->appointment_id);
            if ($appointment) {
                $appointment->update([
                    'appointment_status' => 'checked_in',
                    'checked_in_at' => now(),
                ]);
            }
        }
        
        return redirect()->route('work-orders.show', $workOrder)
            ->with('success', 'Work order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkOrder $workOrder)
    {
        $workOrder->load([
            'customer', 
            'vehicle', 
            'technician', 
            'serviceAdvisor', 
            'qualityChecker',
            'items',
            'tasks.assignedTechnician',
            'appointment'
        ]);
        
        // Get similar work orders for this customer
        $customerWorkOrders = WorkOrder::where('customer_id', $workOrder->customer_id)
            ->where('id', '!=', $workOrder->id)
            ->orderBy('work_order_date', 'desc')
            ->limit(5)
            ->get();
        
        // Get timeline
        $timeline = $workOrder->getTimeline();
        
        // Get technicians for task assignment
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        
        return view('work_orders.show', compact(
            'workOrder', 
            'customerWorkOrders', 
            'timeline',
            'technicians'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkOrder $workOrder)
    {
        $workOrder->load(['customer', 'vehicle', 'items', 'tasks']);
        
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $vehicles = Vehicle::with('customer')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $advisors = User::where('role', 'service_advisor')->where('is_active', true)->get();
        $appointments = Appointment::where('appointment_status', 'scheduled')
            ->orWhere('appointment_status', 'confirmed')
            ->with(['customer', 'vehicle'])
            ->get();
        
        return view('work_orders.edit', compact(
            'workOrder', 
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
    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'work_order_date' => 'required|date',
            'work_order_type' => 'required|in:repair,maintenance,inspection,diagnostic,recall,other',
            'work_order_status' => 'required|in:draft,pending_approval,approved,in_progress,on_hold,completed,cancelled,invoiced',
            'priority' => 'required|in:low,normal,high,emergency',
            'odometer_in' => 'nullable|integer|min:0',
            'odometer_out' => 'nullable|integer|min:0|gte:odometer_in',
            'fuel_level' => 'nullable|in:full,3/4,1/2,1/4,empty',
            'vehicle_condition' => 'nullable|string|max:1000',
            'customer_concerns' => 'required|string|max:2000',
            'customer_complaints' => 'nullable|string|max:2000',
            'initial_diagnosis' => 'nullable|string|max:2000',
            'technician_diagnosis' => 'nullable|string|max:2000',
            'recommended_services' => 'nullable|string|max:2000',
            'additional_notes' => 'nullable|string|max:1000',
            'estimated_labor_hours' => 'nullable|numeric|min:0',
            'estimated_labor_cost' => 'nullable|numeric|min:0',
            'estimated_parts_cost' => 'nullable|numeric|min:0',
            'estimated_tax' => 'nullable|numeric|min:0',
            'estimate_approved' => 'boolean',
            'estimate_notes' => 'nullable|string|max:1000',
            'actual_labor_hours' => 'nullable|numeric|min:0',
            'actual_labor_cost' => 'nullable|numeric|min:0',
            'actual_parts_cost' => 'nullable|numeric|min:0',
            'actual_tax' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid,overdue,written_off',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_due_date' => 'nullable|date',
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
            'bay_status' => 'nullable|in:assigned,occupied,available,maintenance',
            'parts_ordered' => 'boolean',
            'quality_check_passed' => 'boolean',
            'customer_notified' => 'boolean',
            'notification_method' => 'nullable|in:sms,email,phone,in_person',
            'work_performed' => 'nullable|string|max:5000',
            'technician_notes' => 'nullable|string|max:2000',
            'service_advisor_notes' => 'nullable|string|max:2000',
            'customer_feedback' => 'nullable|string|max:2000',
            'customer_rating' => 'nullable|integer|min:1|max:5',
            'requires_customer_approval' => 'boolean',
            'customer_approval_received' => 'boolean',
            'requires_manager_approval' => 'boolean',
            'manager_approval_received' => 'boolean',
            'is_rush_order' => 'boolean',
            'is_complex_job' => 'boolean',
            'has_safety_concerns' => 'boolean',
            'internal_notes' => 'nullable|string|max:2000',
        ]);
        
        // Update status timestamps
        if ($validated['work_order_status'] !== $workOrder->work_order_status) {
            $statusField = null;
            switch ($validated['work_order_status']) {
                case 'approved':
                    $statusField = 'estimate_approved_at';
                    break;
                case 'in_progress':
                    $statusField = 'work_start_time';
                    break;
                case 'completed':
                    $statusField = 'work_complete_time';
                    break;
                case 'cancelled':
                    $statusField = 'cancelled_at';
                    break;
                case 'invoiced':
                    $statusField = 'invoice_sent_time';
                    break;
            }
            
            if ($statusField) {
                $validated[$statusField] = now();
            }
        }
        
        // Calculate totals
        $validated['estimated_total'] = ($validated['estimated_labor_cost'] ?? 0) + 
                                       ($validated['estimated_parts_cost'] ?? 0) + 
                                       ($validated['estimated_tax'] ?? 0);
        
        $validated['actual_total'] = ($validated['actual_labor_cost'] ?? 0) + 
                                    ($validated['actual_parts_cost'] ?? 0) + 
                                    ($validated['actual_tax'] ?? 0);
        
        $validated['final_amount'] = $validated['actual_total'] - ($validated['discount_amount'] ?? 0);
        $validated['balance_due'] = $validated['final_amount'] - ($validated['amount_paid'] ?? 0);
        
        $workOrder->update($validated);
        
        return redirect()->route('work-orders.show', $workOrder)
            ->with('success', 'Work order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();
        
        return redirect()->route('work-orders.index')
            ->with('success', 'Work order deleted successfully.');
    }
    
    /**
     * Approve work order estimate.
     */
    public function approveEstimate(WorkOrder $workOrder)
    {
        if ($workOrder->approveEstimate()) {
            return redirect()->back()->with('success', 'Work order estimate approved.');
        }
        
        return redirect()->back()->with('error', 'Unable to approve estimate.');
    }
    
    /**
     * Start work on work order.
     */
    public function startWork(WorkOrder $workOrder)
    {
        if ($workOrder->startWork()) {
            return redirect()->back()->with('success', 'Work started on work order.');
        }
        
        return redirect()->back()->with('error', 'Unable to start work.');
    }
    
    /**
     * Complete work on work order.
     */
    public function completeWork(WorkOrder $workOrder)
    {
        if ($workOrder->completeWork()) {
            return redirect()->back()->with('success', 'Work completed on work order.');
        }
        
        return redirect()->back()->with('error', 'Unable to complete work.');
    }
    
    /**
     * Mark work order as invoiced.
     */
    public function markAsInvoiced(WorkOrder $workOrder)
    {
        if ($workOrder->markAsInvoiced()) {
            return redirect()->back()->with('success', 'Work order marked as invoiced.');
        }
        
        return redirect()->back()->with('error', 'Unable to mark as invoiced.');
    }
    
    /**
     * Add payment to work order.
     */
    public function addPayment(WorkOrder $workOrder, Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:100',
            'payment_notes' => 'nullable|string|max:500',
        ]);
        
        if ($workOrder->addPayment($validated['amount'])) {
            // In production, you would create a Payment record here
            return redirect()->back()->with('success', 'Payment added successfully.');
        }
        
        return redirect()->back()->with('error', 'Unable to add payment.');
    }
    
    /**
     * Print work order.
     */
    public function print(WorkOrder $workOrder)
    {
        $workOrder->load(['customer', 'vehicle', 'technician', 'serviceAdvisor', 'items']);
        
        // In production, you would generate a PDF here
        // For now, return a view that can be printed
        return view('work_orders.print', compact('workOrder'));
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
            'total' => WorkOrder::whereDate('work_order_date', $today)->count(),
            'draft' => WorkOrder::whereDate('work_order_date', $today)->where('work_order_status', 'draft')->count(),
            'pending_approval' => WorkOrder::whereDate('work_order_date', $today)->where('work_order_status', 'pending_approval')->count(),
            'in_progress' => WorkOrder::whereDate('work_order_date', $today)->where('work_order_status', 'in_progress')->count(),
            'completed' => WorkOrder::whereDate('work_order_date', $today)->where('work_order_status', 'completed')->count(),
            'revenue' => WorkOrder::whereDate('work_order_date', $today)->where('work_order_status', 'completed')->sum('final_amount'),
        ];
        
        // Weekly statistics
        $weeklyStats = [
            'total' => WorkOrder::whereBetween('work_order_date', [$weekStart, $weekEnd])->count(),
            'by_type' => WorkOrder::whereBetween('work_order_date', [$weekStart, $weekEnd])
                ->groupBy('work_order_type')
                ->selectRaw('work_order_type, count(*) as count')
                ->pluck('count', 'work_order_type'),
            'by_status' => WorkOrder::whereBetween('work_order_date', [$weekStart, $weekEnd])
                ->groupBy('work_order_status')
                ->selectRaw('work_order_status, count(*) as count')
                ->pluck('count', 'work_order_status'),
            'revenue' => WorkOrder::whereBetween('work_order_date', [$weekStart, $weekEnd])
                ->where('work_order_status', 'completed')
                ->sum('final_amount'),
        ];
        
        // Monthly statistics
        $monthlyStats = [
            'total' => WorkOrder::whereBetween('work_order_date', [$monthStart, $monthEnd])->count(),
            'revenue' => WorkOrder::whereBetween('work_order_date', [$monthStart, $monthEnd])
                ->where('work_order_status', 'completed')
                ->sum('final_amount'),
            'avg_turnaround' => WorkOrder::whereBetween('work_order_date', [$monthStart, $monthEnd])
                ->where('work_order_status', 'completed')
                ->whereNotNull('work_complete_time')
                ->whereNotNull('check_in_time')
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, check_in_time, work_complete_time)')),
            'profit_margin' => WorkOrder::whereBetween('work_order_date', [$monthStart, $monthEnd])
                ->where('work_order_status', 'completed')
                ->avg('profit_margin'),
        ];
        
        // Technician performance
        $technicianStats = User::where('role', 'technician')
            ->where('is_active', true)
            ->withCount(['workOrders as completed_work_orders' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('work_order_date', [$monthStart, $monthEnd])
                      ->where('work_order_status', 'completed');
            }])
            ->withSum(['workOrders as total_revenue' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('work_order_date', [$monthStart, $monthEnd])
                      ->where('work_order_status', 'completed');
            }], 'final_amount')
            ->withAvg(['workOrders as avg_turnaround' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('work_order_date', [$monthStart, $monthEnd])
                      ->where('work_order_status', 'completed')
                      ->whereNotNull('work_complete_time')
                      ->whereNotNull('check_in_time');
            }], DB::raw('TIMESTAMPDIFF(HOUR, check_in_time, work_complete_time)'))
            ->get();
        
        return view('work_orders.statistics', compact('dailyStats', 'weeklyStats', 'monthlyStats', 'technicianStats'));
    }
    
    /**
     * Get service templates.
     */
    private function getServiceTemplates(): array
    {
        return [
            'oil_change' => [
                'name' => 'Oil Change Service',
                'description' => 'Standard oil change with filter replacement',
                'items' => [
                    [
                        'item_type' => 'labor',
                        'description' => 'Oil Change Labor',
                        'quantity' => 0.5,
                        'unit' => 'hours',
                        'unit_cost' => 85.00,
                    ],
                    [
                        'item_type' => 'part',
                        'description' => 'Synthetic Oil 5W-30',
                        'part_number' => 'OIL-5W30-SYN',
                        'quantity' => 5,
                        'unit' => 'quarts',
                        'unit_cost' => 8.50,
                    ],
                    [
                        'item_type' => 'part',
                        'description' => 'Oil Filter',
                        'part_number' => 'OF-1234',
                        'quantity' => 1,
                        'unit' => 'each',
                        'unit_cost' => 12.99,
                    ],
                ],
            ],
            'brake_service' => [
                'name' => 'Brake Service',
                'description' => 'Front brake pad and rotor replacement',
                'items' => [
                    [
                        'item_type' => 'labor',
                        'description' => 'Brake Service Labor',
                        'quantity' => 2.0,
                        'unit' => 'hours',
                        'unit_cost' => 85.00,
                    ],
                    [
                        'item_type' => 'part',
                        'description' => 'Brake Pads (Front)',
                        'part_number' => 'BP-F-5678',
                        'quantity' => 1,
                        'unit' => 'set',
                        'unit_cost' => 89.99,
                    ],
                    [
                        'item_type' => 'part',
                        'description' => 'Brake Rotors (Front)',
                        'part_number' => 'BR-F-9012',
                        'quantity' => 2,
                        'unit' => 'each',
                        'unit_cost' => 65.00,
                    ],
                ],
            ],
            'tire_rotation' => [
                'name' => 'Tire Rotation & Balance',
                'description' => 'Four-tire rotation and balance',
                'items' => [
                    [
                        'item_type' => 'labor',
                        'description' => 'Tire Rotation Labor',
                        'quantity' => 0.75,
                        'unit' => 'hours',
                        'unit_cost' => 85.00,
                    ],
                    [
                        'item_type' => 'part',
                        'description' => 'Wheel Weights',
                        'part_number' => 'WW-001',
                        'quantity' => 8,
                        'unit' => 'each',
                        'unit_cost' => 0.50,
                    ],
                ],
            ],
            'battery_replacement' => [
                'name' => 'Battery Replacement',
                'description' => 'Battery replacement and testing',
                'items' => [
                    [
                        'item_type' => 'labor',
                        'description' => 'Battery Replacement Labor',
                        'quantity' => 0.5,
                        'unit' => 'hours',
                        'unit_cost' => 85.00,
                    ],
                    [
                        'item_type' => 'part',
                        'description' => 'Car Battery',
                        'part_number' => 'BAT-750CCA',
                        'quantity' => 1,
                        'unit' => 'each',
                        'unit_cost' => 149.99,
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Apply service template to work order.
     */
    private function applyServiceTemplate(WorkOrder $workOrder, string $templateKey): void
    {
        $templates = $this->getServiceTemplates();
        
        if (!isset($templates[$templateKey])) {
            return;
        }
        
        $template = $templates[$templateKey];
        
        // Update work order description
        $workOrder->update([
            'customer_concerns' => $template['description'],
            'work_order_type' => 'maintenance',
        ]);
        
        // Create template items
        foreach ($template['items'] as $itemData) {
            WorkOrderItem::create([
                'work_order_id' => $workOrder->id,
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
        $workOrder->calculateTotals();
    }
}