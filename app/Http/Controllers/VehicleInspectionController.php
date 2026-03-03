<?php

namespace App\Http\Controllers;

use App\Models\VehicleInspection;
use App\Models\InspectionItem;
use App\Models\InspectionCategory;
use App\Models\InspectionTemplate;
use App\Models\WorkOrder;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VehicleInspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VehicleInspection::with(['customer', 'vehicle', 'technician', 'workOrder'])
            ->latest();
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('inspection_name', 'like', "%{$search}%")
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
            $query->where('inspection_status', $request->status);
        }
        
        // Type filter
        if ($request->filled('type')) {
            $query->where('inspection_type', $request->type);
        }
        
        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('created_at', [$dates[0], $dates[1]]);
            }
        }
        
        // Technician filter
        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }
        
        // Safety concerns filter
        if ($request->filled('safety')) {
            $query->where('has_safety_concerns', $request->safety == 'yes');
        }
        
        // Urgent issues filter
        if ($request->filled('urgent')) {
            $query->where('has_urgent_issues', $request->urgent == 'yes');
        }
        
        // Customer approval filter
        if ($request->filled('customer_approved')) {
            $query->where('customer_approved', $request->customer_approved == 'yes');
        }
        
        // Work order filter
        if ($request->filled('work_order_id')) {
            $query->where('work_order_id', $request->work_order_id);
        }
        
        $inspections = $query->paginate(20);
        
        // Get technicians for filter dropdown
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        
        // Get statistics
        $stats = [
            'total' => VehicleInspection::count(),
            'today' => VehicleInspection::whereDate('created_at', Carbon::today())->count(),
            'in_progress' => VehicleInspection::inProgress()->count(),
            'completed' => VehicleInspection::completed()->count(),
            'approved' => VehicleInspection::approved()->count(),
            'with_safety' => VehicleInspection::withSafetyConcerns()->count(),
            'with_urgent' => VehicleInspection::withUrgentIssues()->count(),
            'customer_approved' => VehicleInspection::customerApproved()->count(),
        ];
        
        return view('inspections.index', compact('inspections', 'technicians', 'stats'));
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
        $workOrders = WorkOrder::whereIn('work_order_status', ['draft', 'pending_approval', 'approved'])
            ->with(['customer', 'vehicle'])
            ->get();
        $appointments = Appointment::whereIn('appointment_status', ['scheduled', 'confirmed', 'checked_in'])
            ->with(['customer', 'vehicle'])
            ->get();
        $templates = InspectionTemplate::active()->get();
        
        // Pre-select work order if provided
        $selectedWorkOrder = $request->filled('work_order_id') 
            ? WorkOrder::with(['customer', 'vehicle'])->find($request->work_order_id)
            : null;
        
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
        
        return view('inspections.create', compact(
            'customers', 
            'vehicles', 
            'technicians', 
            'advisors', 
            'workOrders',
            'appointments',
            'templates',
            'selectedWorkOrder',
            'selectedAppointment',
            'selectedCustomer',
            'selectedVehicle'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_order_id' => 'nullable|exists:work_orders,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'technician_id' => 'nullable|exists:users,id',
            'service_advisor_id' => 'nullable|exists:users,id',
            'inspection_type' => 'required|in:pre_service,post_service,safety,comprehensive,custom',
            'inspection_name' => 'nullable|string|max:255',
            'inspection_notes' => 'nullable|string|max:2000',
            'customer_concerns' => 'nullable|string|max:2000',
            'requires_customer_approval' => 'boolean',
            'template_id' => 'nullable|exists:inspection_templates,id',
        ]);
        
        // Set default inspection name if not provided
        if (empty($validated['inspection_name'])) {
            $typeLabel = match($validated['inspection_type']) {
                'pre_service' => 'Pre-Service Inspection',
                'post_service' => 'Post-Service Inspection',
                'safety' => 'Safety Inspection',
                'comprehensive' => 'Comprehensive Inspection',
                'custom' => 'Custom Inspection',
                default => 'Inspection',
            };
            $validated['inspection_name'] = $typeLabel . ' - ' . Carbon::today()->format('M d, Y');
        }
        
        // Set created by
        $validated['created_by'] = auth()->id();
        
        // Create inspection
        $inspection = VehicleInspection::create($validated);
        
        // Apply template if selected
        if ($request->filled('template_id')) {
            $template = InspectionTemplate::find($request->template_id);
            if ($template) {
                $template->applyToInspection($inspection);
            }
        } else {
            // Create default items
            $inspection->createDefaultItems();
        }
        
        return redirect()->route('inspections.show', $inspection)
            ->with('success', 'Vehicle inspection created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleInspection $inspection)
    {
        $inspection->load([
            'customer', 
            'vehicle', 
            'technician', 
            'serviceAdvisor', 
            'workOrder',
            'appointment',
            'items.category',
            'createdBy',
            'approvedBy'
        ]);
        
        // Group items by category
        $itemsByCategory = $inspection->items->groupBy('category.category_name');
        
        // Get statistics
        $itemStats = [
            'total' => $inspection->total_items_checked,
            'passed' => $inspection->items_passed,
            'failed' => $inspection->items_failed,
            'attention_needed' => $inspection->items_attention_needed,
            'not_applicable' => $inspection->items_not_applicable,
            'pass_rate' => $inspection->pass_rate,
            'score' => $inspection->inspection_score,
        ];
        
        // Get technicians for assignment
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        
        // Get similar inspections for this vehicle
        $vehicleInspections = VehicleInspection::where('vehicle_id', $inspection->vehicle_id)
            ->where('id', '!=', $inspection->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('inspections.show', compact(
            'inspection', 
            'itemsByCategory', 
            'itemStats',
            'technicians',
            'vehicleInspections'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleInspection $inspection)
    {
        $inspection->load(['customer', 'vehicle', 'items']);
        
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $vehicles = Vehicle::with('customer')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $advisors = User::where('role', 'service_advisor')->where('is_active', true)->get();
        $workOrders = WorkOrder::whereIn('work_order_status', ['draft', 'pending_approval', 'approved'])
            ->with(['customer', 'vehicle'])
            ->get();
        $appointments = Appointment::whereIn('appointment_status', ['scheduled', 'confirmed', 'checked_in'])
            ->with(['customer', 'vehicle'])
            ->get();
        
        return view('inspections.edit', compact(
            'inspection', 
            'customers', 
            'vehicles', 
            'technicians', 
            'advisors', 
            'workOrders', 
            'appointments'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleInspection $inspection)
    {
        // For AJAX requests (partial updates), use simpler validation
        if ($request->ajax() || $request->wantsJson()) {
            $validated = $request->validate([
                'technician_notes' => 'nullable|string|max:2000',
                'customer_concerns' => 'nullable|string|max:2000',
                'vehicle_mileage' => 'nullable|integer|min:0',
                'inspection_type' => 'nullable|in:pre_purchase,routine_maintenance,safety,comprehensive,diagnostic,emissions,custom',
            ]);
            
            // Update only the provided fields
            $inspection->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Inspection updated successfully.',
                'data' => $validated
            ]);
        }
        
        // Full form submission (non-AJAX)
        $validated = $request->validate([
            'inspection_type' => 'required|in:pre_purchase,routine_maintenance,safety,comprehensive,diagnostic,emissions,custom',
            'inspection_status' => 'required|in:draft,in_progress,completed,approved,rejected,cancelled',
            'inspection_name' => 'required|string|max:255',
            'inspection_notes' => 'nullable|string|max:2000',
            'technician_notes' => 'nullable|string|max:2000',
            'customer_concerns' => 'nullable|string|max:2000',
            'recommended_services' => 'nullable|string|max:2000',
            'additional_notes' => 'nullable|string|max:1000',
            'safety_notes' => 'nullable|string|max:2000',
            'urgent_issues_notes' => 'nullable|string|max:2000',
            'requires_customer_approval' => 'boolean',
            'customer_approved' => 'boolean',
            'customer_approval_method' => 'nullable|in:digital_signature,email,sms,in_person',
            'customer_approval_notes' => 'nullable|string|max:500',
            'has_upsell_opportunities' => 'boolean',
            'upsell_notes' => 'nullable|string|max:2000',
            'estimated_upsell_value' => 'nullable|numeric|min:0',
            'actual_upsell_value' => 'nullable|numeric|min:0',
            'vehicle_mileage' => 'nullable|integer|min:0',
        ]);
        
        // Update status timestamps
        if ($validated['inspection_status'] !== $inspection->inspection_status) {
            $statusField = null;
            switch ($validated['inspection_status']) {
                case 'in_progress':
                    $statusField = 'inspection_started_at';
                    break;
                case 'completed':
                    $statusField = 'inspection_completed_at';
                    break;
                case 'approved':
                    $statusField = 'report_generated_at';
                    break;
            }
            
            if ($statusField) {
                $validated[$statusField] = now();
            }
        }
        
        // Update updated by
        $validated['updated_by'] = auth()->id();
        
        $inspection->update($validated);
        
        return redirect()->route('inspections.show', $inspection)
            ->with('success', 'Vehicle inspection updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleInspection $inspection)
    {
        $inspection->delete();
        
        return redirect()->route('inspections.index')
            ->with('success', 'Vehicle inspection deleted successfully.');
    }
    
    /**
     * Start inspection.
     */
    public function startInspection(VehicleInspection $inspection)
    {
        if ($inspection->startInspection()) {
            return redirect()->back()->with('success', 'Inspection started.');
        }
        
        return redirect()->back()->with('error', 'Unable to start inspection.');
    }
    
    /**
     * Complete inspection.
     */
    public function completeInspection(VehicleInspection $inspection)
    {
        // Log the attempt
        \Log::info('Complete inspection attempt for inspection ID: ' . $inspection->id . ' by user ID: ' . auth()->id());

        if ($inspection->completeInspection()) {
            \Log::info('Successfully completed inspection ID: ' . $inspection->id . '. New status: ' . $inspection->inspection_status);
            return redirect()->back()->with('success', 'Inspection completed.');
        }
        
        \Log::error('Failed to complete inspection ID: ' . $inspection->id);
        return redirect()->back()->with('error', 'Unable to complete inspection.');
    }
    
    /**
     * Undo completion of inspection.
     */
    public function undoCompleteInspection(VehicleInspection $inspection)
    {
        // Log the attempt
        \Log::info('Undo completion attempt for inspection ID: ' . $inspection->id . ' by user ID: ' . auth()->id());

        if ($inspection->undoCompleteInspection()) {
            \Log::info('Successfully undone completion for inspection ID: ' . $inspection->id . '. New status: ' . $inspection->inspection_status);
            return redirect()->back()->with('success', 'Inspection marked as incomplete. The technician has been notified.');
        }
        
        \Log::error('Failed to undo completion for inspection ID: ' . $inspection->id);
        return redirect()->back()->with('error', 'Unable to undo inspection completion.');
    }
    
    /**
     * Approve inspection.
     */
    public function approveInspection(VehicleInspection $inspection)
    {
        if ($inspection->approveInspection(auth()->user())) {
            return redirect()->back()->with('success', 'Inspection approved.');
        }
        
        return redirect()->back()->with('error', 'Unable to approve inspection.');
    }
    
    /**
     * Request customer approval.
     */
    public function requestCustomerApproval(VehicleInspection $inspection, Request $request)
    {
        $validated = $request->validate([
            'method' => 'required|in:digital_signature,email,sms,in_person',
        ]);
        
        if ($inspection->requestCustomerApproval($validated['method'])) {
            return redirect()->back()->with('success', 'Customer approval requested.');
        }
        
        return redirect()->back()->with('error', 'Unable to request customer approval.');
    }
    
    /**
     * Approve by customer.
     */
    public function approveByCustomer(VehicleInspection $inspection, Request $request)
    {
        $validated = $request->validate([
            'method' => 'required|in:digital_signature,email,sms,in_person',
            'notes' => 'nullable|string|max:500',
        ]);
        
        if ($inspection->approveByCustomer($validated['method'], $validated['notes'] ?? null)) {
            return redirect()->back()->with('success', 'Inspection approved by customer.');
        }
        
        return redirect()->back()->with('error', 'Unable to approve by customer.');
    }
    
    /**
     * Generate inspection report.
     */
    public function generateReport(VehicleInspection $inspection)
    {
        $report = $inspection->generateReport();
        
        // In production, you would generate a PDF here
        // For now, return a view that can be printed
        return view('inspections.report', [
            'inspection' => $inspection,
            'report' => $report,
        ]);
    }
    
    /**
     * Get inspection statistics.
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
            'total' => VehicleInspection::whereDate('created_at', $today)->count(),
            'by_type' => VehicleInspection::whereDate('created_at', $today)
                ->groupBy('inspection_type')
                ->selectRaw('inspection_type, count(*) as count')
                ->pluck('count', 'inspection_type'),
            'by_status' => VehicleInspection::whereDate('created_at', $today)
                ->groupBy('inspection_status')
                ->selectRaw('inspection_status, count(*) as count')
                ->pluck('count', 'inspection_status'),
            'avg_score' => VehicleInspection::whereDate('created_at', $today)
                ->whereNotNull('inspection_score')
                ->avg('inspection_score'),
        ];
        
        // Weekly statistics
        $weeklyStats = [
            'total' => VehicleInspection::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            'by_type' => VehicleInspection::whereBetween('created_at', [$weekStart, $weekEnd])
                ->groupBy('inspection_type')
                ->selectRaw('inspection_type, count(*) as count')
                ->pluck('count', 'inspection_type'),
            'by_status' => VehicleInspection::whereBetween('created_at', [$weekStart, $weekEnd])
                ->groupBy('inspection_status')
                ->selectRaw('inspection_status, count(*) as count')
                ->pluck('count', 'inspection_status'),
            'avg_score' => VehicleInspection::whereBetween('created_at', [$weekStart, $weekEnd])
                ->whereNotNull('inspection_score')
                ->avg('inspection_score'),
            'safety_concerns' => VehicleInspection::whereBetween('created_at', [$weekStart, $weekEnd])
                ->where('has_safety_concerns', true)
                ->count(),
            'urgent_issues' => VehicleInspection::whereBetween('created_at', [$weekStart, $weekEnd])
                ->where('has_urgent_issues', true)
                ->count(),
            'customer_approved' => VehicleInspection::whereBetween('created_at', [$weekStart, $weekEnd])
                ->where('customer_approved', true)
                ->count(),
        ];
        
        // Monthly statistics
        $monthlyStats = [
            'total' => VehicleInspection::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            'avg_score' => VehicleInspection::whereBetween('created_at', [$monthStart, $monthEnd])
                ->whereNotNull('inspection_score')
                ->avg('inspection_score'),
            'avg_duration' => VehicleInspection::whereBetween('created_at', [$monthStart, $monthEnd])
                ->whereNotNull('inspection_started_at')
                ->whereNotNull('inspection_completed_at')
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, inspection_started_at, inspection_completed_at)')),
            'total_upsell' => VehicleInspection::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('has_upsell_opportunities', true)
                ->sum('estimated_upsell_value'),
            'actual_upsell' => VehicleInspection::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('has_upsell_opportunities', true)
                ->sum('actual_upsell_value'),
        ];
        
        // Technician performance
        $technicianStats = User::where('role', 'technician')
            ->where('is_active', true)
            ->withCount(['vehicleInspections as completed_inspections' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('created_at', [$monthStart, $monthEnd])
                      ->where('inspection_status', 'completed');
            }])
            ->withAvg(['vehicleInspections as avg_score' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('created_at', [$monthStart, $monthEnd])
                      ->where('inspection_status', 'completed')
                      ->whereNotNull('inspection_score');
            }], 'inspection_score')
            ->withAvg(['vehicleInspections as avg_duration' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('created_at', [$monthStart, $monthEnd])
                      ->where('inspection_status', 'completed')
                      ->whereNotNull('inspection_started_at')
                      ->whereNotNull('inspection_completed_at');
            }], DB::raw('TIMESTAMPDIFF(MINUTE, inspection_started_at, inspection_completed_at)'))
            ->get();
        
        // Category performance
        $categoryStats = InspectionCategory::withCount(['items as total_items'])
            ->withCount(['items as passed_items' => function($query) use ($monthStart, $monthEnd) {
                $query->whereHas('inspection', function($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('created_at', [$monthStart, $monthEnd]);
                })->where('item_status', 'passed');
            }])
            ->withCount(['items as failed_items' => function($query) use ($monthStart, $monthEnd) {
                $query->whereHas('inspection', function($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('created_at', [$monthStart, $monthEnd]);
                })->where('item_status', 'failed');
            }])
            ->get()
            ->map(function($category) {
                $total = $category->total_items;
                $passed = $category->passed_items;
                $failed = $category->failed_items;
                
                return [
                    'category_name' => $category->category_name,
                    'total_items' => $total,
                    'passed_items' => $passed,
                    'failed_items' => $failed,
                    'pass_rate' => $total > 0 ? ($passed / $total) * 100 : 0,
                    'fail_rate' => $total > 0 ? ($failed / $total) * 100 : 0,
                ];
            });
        
        return view('inspections.statistics', compact(
            'dailyStats', 
            'weeklyStats', 
            'monthlyStats', 
            'technicianStats',
            'categoryStats'
        ));
    }
    
    /**
     * Manage inspection items.
     */
    public function manageItems(VehicleInspection $inspection)
    {
        $inspection->load(['items.category']);
        
        $categories = InspectionCategory::active()->ordered()->get();
        
        return view('inspections.manage-items', compact('inspection', 'categories'));
    }
    
    /**
     * Update inspection item.
     */
    public function updateItem(Request $request, VehicleInspection $inspection, InspectionItem $item)
    {
        $validated = $request->validate([
            'item_status' => 'required|in:pending,passed,failed,attention_needed,not_applicable',
            'measured_value' => 'nullable|numeric',
            'technician_notes' => 'nullable|string|max:1000',
            'recommendation' => 'nullable|string|max:500',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_time_hours' => 'nullable|numeric|min:0',
            'priority' => 'nullable|in:low,medium,high,critical',
            'requires_attention' => 'boolean',
            'is_safety_issue' => 'boolean',
            'is_urgent_issue' => 'boolean',
            'is_critical_issue' => 'boolean',
        ]);
        
        $item->update($validated);
        
        // Update inspection counts
        $inspection->updateItemCounts();
        
        return redirect()->back()->with('success', 'Inspection item updated.');
    }
    
    /**
     * Upload photo for inspection.
     */
    public function uploadPhoto(Request $request, VehicleInspection $inspection)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'description' => 'nullable|string|max:255',
        ]);
        
        try {
            // Store the photo
            $path = $request->file('photo')->store('inspections/photos', 'public');
            
            // Add photo to inspection
            $inspection->addPhoto($path, $request->description);
            
            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully',
                'photo' => [
                    'path' => $path,
                    'path_url' => asset('storage/' . $path),
                    'description' => $request->description,
                    'uploaded_at' => now()->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload photo: ' . $e->getMessage()
            ], 500);
        }
    }
}