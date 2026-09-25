<?php

namespace App\Http\Controllers;

use App\Models\VehicleInspection;
use App\Models\InspectionItem;
use App\Models\InspectionCategory;
use App\Models\InspectionTemplate;
use App\Models\InspectionFinding;
use App\Models\InspectionFindingGroup;
use App\Models\JobOrder;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VehicleInspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VehicleInspection::with(['customer', 'vehicle', 'technician', 'jobOrder', 'appointment', 'repairOrderPayments'])
            ->latest();
        
        // By default, only show active inspections (not completed/converted)
        // User can override with status filter
        if (!$request->filled('status')) {
            $query->whereNotIn('inspection_status', ['completed'])
                  ->whereNull('job_order_id');
        }
        
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
        if ($request->filled('job_order_id')) {
            $query->where('job_order_id', $request->job_order_id);
        }
        
        $inspections = $query->paginate(20);
        
        // Get technicians for filter dropdown
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        
        // Get statistics — driven by the repair-status pipeline
        // (Pending = not yet in repair, In Progress = being worked/in QC, Completed = released).
        // Sales Amount = ₱ value of every order not yet Paid (Cancelled excluded), i.e. an order
        // keeps counting until its repair status is set to "Paid".
        $stats = [
            'total' => VehicleInspection::count(),
            'pending' => VehicleInspection::whereIn('repair_status', ['received', 'diagnosing', 'awaiting_approval', 'awaiting_parts'])->count(),
            'in_progress' => VehicleInspection::whereIn('repair_status', ['in_progress', 'quality_check', 'ready_for_pickup'])->count(),
            'completed' => VehicleInspection::whereIn('repair_status', ['released', 'paid'])->count(),
            'sales_amount' => VehicleInspection::with('appointment')
                ->whereNotIn('repair_status', ['paid', 'cancelled'])
                ->get()
                ->sum(fn ($i) => $i->repair_total),
            'approved' => VehicleInspection::approved()->count(),
            'with_urgent' => VehicleInspection::withUrgentIssues()->count(),
            'customer_approved' => VehicleInspection::customerApproved()->count(),
        ];
        
        return view('inspections.index', compact('inspections', 'technicians', 'stats'));
    }

    /**
     * Update the production-style repair status of a repair order (inline tag).
     */
    public function updateRepairStatus(Request $request, VehicleInspection $inspection)
    {
        $validated = $request->validate([
            'repair_status' => 'required|string|in:' . implode(',', array_keys(VehicleInspection::REPAIR_STATUSES)),
        ]);

        $inspection->repair_status = $validated['repair_status'];

        // Stop/restart the workshop clock: tagging Released (or Cancelled) freezes the
        // days-in-workshop count; moving back to an active stage resumes counting.
        if (in_array($validated['repair_status'], ['released', 'cancelled', 'paid'], true)) {
            if (is_null($inspection->workshop_released_at)) {
                $inspection->workshop_released_at = now();
            }
        } else {
            $inspection->workshop_released_at = null;
        }

        $inspection->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'repair_status' => $inspection->repair_status,
                'label' => $inspection->repair_status_label,
                'bg' => $inspection->repair_status_bg,
                'text' => $inspection->repair_status_text,
                'border' => $inspection->repair_status_border,
                'is_released' => $inspection->is_released,
                'days_label' => $inspection->days_in_workshop_label,
            ]);
        }

        return back()->with('success', 'Repair status updated.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $vehicles = Vehicle::with('customer')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $inspectors = User::where('role', 'technician')->where('is_active', true)->get(); // Same as technicians for now
        $advisors = User::whereIn('role', ['service_advisor', 'office_staff'])->where('is_active', true)->get();
        $jobOrders = JobOrder::whereIn('job_order_status', ['draft', 'pending_approval', 'approved'])
            ->with(['customer', 'vehicle'])
            ->get();
        $appointments = Appointment::whereIn('appointment_status', ['scheduled', 'confirmed', 'checked_in'])
            ->with(['customer', 'vehicle'])
            ->get();
        $templates = InspectionTemplate::active()->get();
        
        // Pre-select work order if provided
        $selectedJobOrder = $request->filled('job_order_id') 
            ? JobOrder::with(['customer', 'vehicle'])->find($request->job_order_id)
            : null;
        
        // Pre-select appointment if provided
        $selectedAppointment = $request->filled('appointment_id') 
            ? Appointment::with(['customer', 'vehicle'])->find($request->appointment_id)
            : null;
        
        // Pre-select vehicle if provided
        $selectedVehicle = $request->filled('vehicle_id') 
            ? Vehicle::with('customer')->find($request->vehicle_id)
            : null;
        
        // Pre-select customer if provided OR get from selected vehicle/appointment/work order
        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);
        } elseif ($selectedVehicle && $selectedVehicle->customer) {
            // If vehicle is provided but customer isn't, get customer from vehicle
            $selectedCustomer = $selectedVehicle->customer;
        } elseif ($selectedAppointment && $selectedAppointment->customer) {
            // If appointment is provided but customer isn't, get customer from appointment
            $selectedCustomer = $selectedAppointment->customer;
        } elseif ($selectedJobOrder && $selectedJobOrder->customer) {
            // If work order is provided but customer isn't, get customer from work order
            $selectedCustomer = $selectedJobOrder->customer;
        } else {
            $selectedCustomer = null;
        }
        
        // Get customer vehicles and history
        $customerVehicles = $selectedCustomer ? $selectedCustomer->vehicles()->orderBy('created_at', 'desc')->get() : collect();
        $customerHistory = $selectedCustomer ? VehicleInspection::where('customer_id', $selectedCustomer->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get() : collect();

        // Load quotation data for auto-fill
        $quotationData = null;
        if ($request->filled('quotation_id')) {
            $quotation = \App\Models\Quotation::find($request->quotation_id);
            if ($quotation && $quotation->customer_id == ($selectedCustomer->id ?? null)) {
                $quotationData = $quotation;
            }
        } elseif ($selectedCustomer) {
            $latestQuotation = $selectedCustomer->quotations()->latest()->first();
            if ($latestQuotation) {
                $quotationData = $latestQuotation;
            }
        }
        
        // All technicians for multi-select
        $allTechnicians = $technicians;
        
        // Check for active transactions on the selected vehicle
        $activeTransaction = null;
        if ($selectedVehicle) {
            $activeTransaction = \App\Services\ActiveTransactionService::checkActiveTransaction($selectedVehicle->id);
        }
        
        return view('inspections.create', compact(
            'customers', 
            'vehicles', 
            'technicians', 
            'inspectors',
            'advisors', 
            'jobOrders',
            'appointments',
            'templates',
            'selectedJobOrder',
            'selectedAppointment',
            'selectedCustomer',
            'selectedVehicle',
            'customerVehicles',
            'customerHistory',
            'allTechnicians',
            'quotationData',
            'activeTransaction'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_order_id' => 'nullable|exists:job_orders,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'customer_id' => 'nullable|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'date_received' => 'nullable|date',
            'service_types' => 'nullable|array',
            'service_types.*' => 'string|max:100',
            'job_description_items' => 'nullable|array',
            'job_description_items.*.description' => 'nullable|string|max:255',
            'job_description_items.*.mh' => 'nullable|numeric|min:0',
            'job_description_items.*.unit_price' => 'nullable|numeric|min:0',
            'job_description_items.*.labor_cost' => 'nullable|numeric|min:0',
            'parts_items' => 'nullable|array',
            'parts_items.*.description' => 'nullable|string|max:255',
            'parts_items.*.qty' => 'nullable|numeric|min:0',
            'parts_items.*.unit_price' => 'nullable|numeric|min:0',
            'parts_items.*.cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            // Walk-in intake (optional): inline customer / vehicle details, mirroring
            // the Appointment "Update Information" form. Used to create the customer
            // and/or vehicle when none is selected, or to complete/correct the
            // selected ones (only non-empty values are written).
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:4',
            'license_plate' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
            'engine_no' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:20',
            'fuel_type' => 'nullable|string|max:20',
            'odometer' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:50',
            'technician_id' => 'nullable|exists:users,id',
            'technicians' => 'nullable|array',
            // The multi-select always posts a hidden empty technicians[] entry; allow it
            'technicians.*' => 'nullable|exists:users,id',
            'service_advisor_id' => 'nullable|exists:users,id',
            'inspection_type' => 'nullable|array',
            'inspection_type.*' => 'in:pre_purchase,safety,emissions,routine,diagnostic,post_repair,comprehensive,custom',
            'inspection_name' => 'nullable|string|max:255',
            'inspection_notes' => 'nullable|string|max:2000',
            'customer_concerns' => 'nullable|string|max:2000',
            'requires_customer_approval' => 'boolean',
            'template_id' => 'nullable|exists:inspection_templates,id',
            'vehicle_mileage' => 'nullable|integer|min:0',
        
            'service_type' => 'nullable|array',
            'service_type.*' => 'string|in:' . implode(',', \App\Models\ServiceType::keys()),
            'categories' => 'nullable|array',
            'categories.*' => 'string',]);
        
        // The create page mirrors the Appointment "Update Information" form and
        // does not ask for an inspection type; default it so an RO can be created.
        if (empty($validated['inspection_type'])) {
            $validated['inspection_type'] = ['routine'];
        }

        // ---- Walk-in intake: resolve the customer (select existing or create) ----
        $customerId = $validated['customer_id'] ?? null;
        if (!$customerId) {
            if (empty($validated['first_name']) && empty($validated['last_name'])) {
                return back()->withErrors(['customer_id' => 'Select an existing customer, or enter the walk-in customer name.'])->withInput();
            }
            $customer = \App\Models\Customer::create([
                'first_name' => $validated['first_name'] ?? '',
                'last_name'  => $validated['last_name'] ?? '',
                'email'      => $validated['email'] ?? null,
                'phone'      => $validated['phone'] ?? null,
                'address'    => $validated['address'] ?? null,
                'city'       => $validated['city'] ?? null,
                'is_active'  => true,
            ]);
            $customerId = $customer->id;
        } else {
            $customer = \App\Models\Customer::find($customerId);
            if ($customer) {
                $updates = [];
                foreach (['first_name', 'last_name', 'phone', 'email', 'address', 'city'] as $f) {
                    if (!empty($validated[$f])) { $updates[$f] = $validated[$f]; }
                }
                if ($updates) { $customer->update($updates); }
            }
        }
        $validated['customer_id'] = $customerId;

        // ---- Walk-in intake: resolve the vehicle (select existing or create) ----
        $vehicleId = $validated['vehicle_id'] ?? null;
        if (!$vehicleId) {
            if (empty($validated['make']) && empty($validated['model'])) {
                return back()->withErrors(['vehicle_id' => 'Select an existing vehicle, or enter the walk-in vehicle details.'])->withInput();
            }
            $vehicle = \App\Models\Vehicle::create([
                'customer_id'   => $customerId,
                'make'          => $validated['make'] ?? '',
                'model'         => $validated['model'] ?? '',
                'year'          => (int) ($validated['year'] ?? 0),
                'license_plate' => $validated['license_plate'] ?? null,
                'vin'           => $validated['vin'] ?? null,
                'engine_no'     => $validated['engine_no'] ?? null,
                'transmission'  => $validated['transmission'] ?? null,
                'fuel_type'     => $validated['fuel_type'] ?? null,
                'odometer'      => (int) ($validated['odometer'] ?? 0),
                'color'         => $validated['color'] ?? null,
                'is_active'     => true,
            ]);
            $vehicleId = $vehicle->id;
        } else {
            $vehicle = \App\Models\Vehicle::find($vehicleId);
            if ($vehicle) {
                $updates = [];
                foreach (['make', 'model', 'license_plate', 'vin', 'engine_no', 'transmission', 'fuel_type', 'color'] as $f) {
                    if (!empty($validated[$f])) { $updates[$f] = $validated[$f]; }
                }
                if (!empty($validated['year'])) { $updates['year'] = (int) $validated['year']; }
                if (!empty($validated['odometer'])) { $updates['odometer'] = (int) $validated['odometer']; }
                if ($updates) { $vehicle->update($updates); }
            }
        }
        $validated['vehicle_id'] = $vehicleId;

        // Origin: linked to a schedule (Appointment / Job Order) or a walk-in.
        $validated['source'] = ($request->filled('appointment_id') || $request->filled('job_order_id'))
            ? 'scheduled'
            : 'walk_in';
        $validated['date_received'] = !empty($validated['date_received'])
            ? $validated['date_received']
            : now()->toDateString();

        // Intake-only fields are not columns on vehicle_inspections.
        foreach (['first_name', 'last_name', 'phone', 'email', 'address', 'city',
                  'make', 'model', 'year', 'license_plate', 'vin', 'engine_no',
                  'transmission', 'fuel_type', 'odometer', 'color'] as $intakeKey) {
            unset($validated[$intakeKey]);
        }

        // Intake line items (Services / Job Description / Parts / Discount).
        if (array_key_exists('job_description_items', $validated)) {
            $validated['job_description_items'] = $this->normalizeRepairRows(
                $validated['job_description_items'] ?? [],
                ['description', 'mh', 'unit_price', 'labor_cost']
            );
        }
        if (array_key_exists('parts_items', $validated)) {
            $validated['parts_items'] = $this->normalizeRepairRows(
                $validated['parts_items'] ?? [],
                ['description', 'qty', 'unit_price', 'cost']
            );
        }
        // Keep the single service_type column in sync with the (multi) service types.
        if (!empty($validated['service_types'])) {
            $validated['service_type'] = (string) reset($validated['service_types']);
        }

        // The discount column on vehicle_inspections is NOT NULL (default 0); a blank
        // field arrives as null, which MySQL rejects. Coerce it.
        if (!isset($validated['discount']) || $validated['discount'] === '' || $validated['discount'] === null) {
            $validated['discount'] = 0;
        }

        // Set default inspection name if not provided
        if (empty($validated['inspection_name'])) {
            // Handle multiple inspection types
            $typeLabels = [];
            foreach ($validated['inspection_type'] as $type) {
                $typeLabels[] = match($type) {
                    'pre_purchase' => 'Pre-Purchase',
                    'safety' => 'Safety',
                    'emissions' => 'Emissions',
                    'routine' => 'Routine',
                    'diagnostic' => 'Diagnostic',
                    'post_repair' => 'Post-Repair',
                    'comprehensive' => 'Comprehensive',
                    'custom' => 'Custom',
                    default => 'Inspection',
                };
            }
            
            if (count($typeLabels) === 1) {
                $validated['inspection_name'] = $typeLabels[0] . ' Inspection - ' . Carbon::today()->format('M d, Y');
            } else {
                $validated['inspection_name'] = 'Multiple Inspections (' . implode(', ', $typeLabels) . ') - ' . Carbon::today()->format('M d, Y');
            }
        }
        
        // Convert inspection_type array to JSON for storage
        $validated['inspection_type'] = json_encode($validated['inspection_type']);
        
        // Convert service_type array to JSON for storage
        if (isset($validated['service_type']) && is_array($validated['service_type'])) {
            $validated['service_type'] = json_encode($validated['service_type']);
        }
        
        // Set created by
        $validated['created_by'] = auth()->id();
        
        // Check for duplicate active transaction (skip if override_duplicate is set)
        if (!$request->filled('override_duplicate') || $request->override_duplicate !== '1') {
            if (!empty($vehicleId)) {
                $activeTransaction = \App\Services\ActiveTransactionService::checkActiveTransaction($vehicleId);
                if ($activeTransaction) {
                    return back()->withErrors(['duplicate' => 'This vehicle already has an active ' . $activeTransaction['stage'] . ' (' . $activeTransaction['reference_number'] . ').'])->withInput();
                }
            }
        }
        
        // Create inspection
        $inspection = VehicleInspection::create($validated);

        // Mirror the intake line items onto the linked appointment (the Repair
        // Order slip reads from the appointment). Walk-ins have none — the
        // values stay on the Repair Order itself.
        if ($inspection->appointment) {
            $apptData = [];
            if (!empty($validated['service_types'])) {
                $apptData['service_types'] = array_values($validated['service_types']);
            }
            if (array_key_exists('job_description_items', $validated)) {
                $apptData['job_description_items'] = $validated['job_description_items'];
            }
            if (array_key_exists('parts_items', $validated)) {
                $apptData['parts_items'] = $validated['parts_items'];
            }
            if (!empty($validated['date_received'])) {
                $apptData['date_received'] = $validated['date_received'];
            }
            if (array_key_exists('discount', $validated)) {
                $apptData['discount'] = $validated['discount'];
            }
            if (!empty($validated['customer_concerns'])) {
                $apptData['service_request'] = $validated['customer_concerns'];
            }
            if (!empty($validated['phone'])) { $apptData['phone'] = $validated['phone']; }
            if (!empty($validated['email'])) { $apptData['email'] = $validated['email']; }
            if ($apptData) {
                $inspection->appointment->update($apptData);
            }
        }
        
        // Sync multi-technician assignments
        if ($request->filled('technicians')) {
            $technicianIds = array_filter($request->input('technicians', []));
            if (!empty($technicianIds)) {
                $syncData = [];
                foreach ($technicianIds as $techId) {
                    $syncData[$techId] = ['role' => 'technician'];
                }
                $inspection->technicians()->sync($syncData);
            }
        }
        
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
        // Mark as viewed if not yet viewed
        if ($inspection->viewed_at === null) {
            $inspection->update(['viewed_at' => now()]);
        }
        
        $inspection->load([
            'customer',
            'vehicle',
            'technician',
            'serviceAdvisor',
            'jobOrder',
            'appointment',
            'items.category',
            'technicians',
            'createdBy',
            'approvedBy',
            'serviceProgress',
            'inspectionFindings.technician',
            'repairOrderPayments.uploadedBy',
            'repairOrderPayments.verifiedBy'
        ]);
        
        // Group items by category
        $itemsByCategory = $inspection->items->groupBy('category.category_name');
        
        // Get technicians and service advisors for dropdowns
        $technicians = User::where('role', 'technician')->where('is_active', true)->orderBy('name')->get();
        $serviceAdvisors = User::whereIn('role', ['service_advisor', 'office_staff'])->where('is_active', true)->orderBy('name')->get();
        
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
        
        // Get similar inspections for this vehicle
        $vehicleInspections = VehicleInspection::where('vehicle_id', $inspection->vehicle_id)
            ->where('id', '!=', $inspection->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $response = response()->view('inspections.show', compact(
            'inspection', 
            'itemsByCategory', 
            'itemStats',
            'technicians',
            'serviceAdvisors',
            'vehicleInspections'
        ));
        
        // Add headers to prevent caching
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleInspection $inspection)
    {
        // Mark as viewed if not yet viewed
        if ($inspection->viewed_at === null) {
            $inspection->update(['viewed_at' => now()]);
        }
        
        $inspection->load(['customer', 'vehicle', 'items', 'technicians', 'appointment', 'inspectionFindings', 'findingGroups.findings']);
        
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $vehicles = Vehicle::with('customer')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $advisors = User::whereIn('role', ['service_advisor', 'office_staff'])->where('is_active', true)->get();
        $jobOrders = JobOrder::whereIn('job_order_status', ['draft', 'pending_approval', 'approved'])
            ->with(['customer', 'vehicle'])
            ->get();
        $appointments = Appointment::where(function ($q) use ($inspection) {
                $q->whereIn('appointment_status', ['scheduled', 'confirmed', 'checked_in']);
                // Always keep the currently-linked appointment selectable. Otherwise the
                // Appointment dropdown renders with no matching option and a save would
                // silently clear the link (and wipe the Repair Order line items).
                if ($inspection->appointment_id) {
                    $q->orWhere('id', $inspection->appointment_id);
                }
            })
            ->with(['customer', 'vehicle'])
            ->get();
        
        // All technicians for multi-select
        $allTechnicians = $technicians;
        
        return view('inspections.edit', compact(
            'inspection', 
            'customers', 
            'vehicles', 
            'technicians', 
            'allTechnicians',
            'advisors', 
            'jobOrders', 
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
                'inspection_type' => 'nullable|in:pre_purchase,routine_maintenance,pre_service,safety,comprehensive,diagnostic,emissions,custom',
            ]);
            
            // Update only the provided fields
            $inspection->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Inspection updated successfully.',
                'data' => $validated
            ]);
        }
        
        // Check for update_type parameter for partial updates
        if ($request->has('update_type')) {
            switch ($request->update_type) {
                case 'status':
                    $validated = $request->validate([
                        'inspection_status' => 'required|in:draft,in_progress,completed,approved,rejected,cancelled',
                        'status_notes' => 'nullable|string|max:2000',
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
                    
                    // Update only the status field and timestamp if needed
                    $updateData = [
                        'inspection_status' => $validated['inspection_status'],
                        'updated_by' => $validated['updated_by'],
                    ];
                    
                    if ($statusField && isset($validated[$statusField])) {
                        $updateData[$statusField] = $validated[$statusField];
                    }
                    
                    $inspection->update($updateData);
                    
                    // If there are status notes, add them to inspection_notes
                    if (!empty($validated['status_notes'])) {
                        $currentNotes = $inspection->inspection_notes ?? '';
                        $newNotes = "Status changed to " . $validated['inspection_status'] . " at " . now()->format('Y-m-d H:i:s') . ":\n" . $validated['status_notes'];
                        
                        if (!empty($currentNotes)) {
                            $newNotes = $currentNotes . "\n\n---\n\n" . $newNotes;
                        }
                        
                        $inspection->update(['inspection_notes' => $newNotes]);
                    }
                    
                    return redirect()->route('inspections.show', $inspection)
                        ->with('success', 'Inspection status updated successfully.');
                    
                case 'checklist':
                    $validated = $request->validate([
                        'categories' => 'nullable|array',
                        'categories.*' => 'string|in:engine,brakes,suspension,electrical,tires,exhaust,interior,exterior,fluids,ac',
                    ]);
                    
                    // Update categories
                    $inspection->update([
                        'categories' => $validated['categories'] ?? [],
                        'updated_by' => auth()->id(),
                    ]);
                    
                    return redirect()->route('inspections.show', $inspection)
                        ->with('success', 'Inspection checklist updated successfully.');
                    
                case 'concerns':
                    $validated = $request->validate([
                        'customer_concerns' => 'nullable|string|max:2000',
                    ]);
                    
                    // Update customer concerns
                    $inspection->update([
                        'customer_concerns' => $validated['customer_concerns'] ?? null,
                        'updated_by' => auth()->id(),
                    ]);
                    
                    return redirect()->route('inspections.show', $inspection)
                        ->with('success', 'Customer concerns updated successfully.');
                    
                case 'findings':
                    $validated = $request->validate([
                        'findings_data' => 'required|json',
                    ]);
                    
                    // Decode JSON data
                    $findingsData = json_decode($validated['findings_data'], true);
                    
                    if (!is_array($findingsData)) {
                        return redirect()->back()->with('error', 'Invalid findings data format.');
                    }
                    
                    // Process findings data into structured array
                    $findingsArray = [];
                    foreach ($findingsData as $finding) {
                        if (!isset($finding['category'], $finding['part'], $finding['condition'])) {
                            continue; // Skip invalid entries
                        }
                        
                        // Map condition to status/color
                        $status = 'normal';
                        $color = 'info';
                        if ($finding['condition'] === 'Good') { $status = 'good'; $color = 'success'; }
                        if ($finding['condition'] === 'Needs Repair') { $status = 'repair'; $color = 'warning'; }
                        if ($finding['condition'] === 'Needs Replacement') { $status = 'replace'; $color = 'danger'; }
                        if ($finding['condition'] === 'Critical') { $status = 'critical'; $color = 'danger'; }
                        if ($finding['condition'] === 'Monitor') { $status = 'monitor'; $color = 'info'; }
                        
                        $findingsArray[] = [
                            'category' => $finding['category'],
                            'part' => $finding['part'],
                            'condition' => $finding['condition'],
                            'priority' => $finding['priority'] ?? 'Low',
                            'description' => $finding['description'] ?? '',
                            'action' => $finding['action'] ?? '',
                            'status' => $status,
                            'color' => $color,
                            'timestamp' => $finding['timestamp'] ?? now()->toISOString(),
                        ];
                    }
                    
                    // Update findings (casting will handle JSON encoding)
                    $inspection->findings = $findingsArray;
                    $inspection->updated_by = auth()->id();
                    $inspection->save();
                    
                    return redirect()->route('inspections.show', $inspection)
                        ->with('success', 'Inspection findings updated successfully.');
                    
                // Add other update_type cases here as needed
                // case 'notes':
                // case 'mileage':
                // etc.
            }
        }
        
        // Full form submission (non-AJAX)
        $validated = $request->validate([
            'technician_id' => 'nullable|exists:users,id',
            'technicians' => 'nullable|array',
            // The multi-select always posts a hidden empty technicians[] entry; allow it
            'technicians.*' => 'nullable|exists:users,id',
            // Reassignable links
            'customer_id' => 'nullable|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'service_advisor_id' => 'nullable|exists:users,id',
            'job_order_id' => 'nullable|exists:job_orders,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            // Customer detail (Arrived "Update Information" form)
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            // Vehicle detail
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:4',
            'license_plate' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
            'engine_no' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:20',
            'fuel_type' => 'nullable|string|max:20',
            'odometer' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:50',
            // Services + repair order line items (mirrored onto the linked appointment)
            'service_types' => 'nullable|array',
            'service_types.*' => 'string|max:100',
            'date_received' => 'nullable|date',
            'service_request' => 'nullable|string|max:1000',
            'discount' => 'nullable|numeric|min:0',
            'job_description_items' => 'nullable|array',
            'job_description_items.*.description' => 'nullable|string|max:255',
            'job_description_items.*.mh' => 'nullable|numeric|min:0',
            'job_description_items.*.unit_price' => 'nullable|numeric|min:0',
            'job_description_items.*.labor_cost' => 'nullable|numeric|min:0',
            'parts_items' => 'nullable|array',
            'parts_items.*.description' => 'nullable|string|max:255',
            'parts_items.*.qty' => 'nullable|numeric|min:0',
            'parts_items.*.unit_price' => 'nullable|numeric|min:0',
            'parts_items.*.cost' => 'nullable|numeric|min:0',
            'inspection_type' => 'nullable|in:pre_purchase,routine_maintenance,pre_service,safety,comprehensive,diagnostic,emissions,custom',
            'inspection_status' => 'nullable|in:draft,in_progress,completed,approved,rejected,cancelled',
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
        if (!empty($validated['inspection_status']) && $validated['inspection_status'] !== $inspection->inspection_status) {
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

        // The RO's own intake odometer: mirror the posted odometer so the Repair
        // Order's Overview shows *this* order's reading, not the vehicle master's
        // stale value (a quotation-promoted RO starts at 0 until re-read).
        if (filled($validated['odometer'] ?? null)) {
            $validated['vehicle_mileage'] = (int) $validated['odometer'];
        }

        // Keep the single service_type column in sync with the (multi) service types
        if (!empty($validated['service_types'])) {
            $validated['service_type'] = (string) reset($validated['service_types']);
        }

        try {
            DB::transaction(function () use ($validated, $request, $inspection) {
                // Persist the inspection's own (fillable) columns. Detail fields that
                // are not fillable here (first_name, make, service_types, ...) are
                // ignored by mass assignment and handled below.
                $inspection->update($validated);
                $inspection->refresh();

                // ---- Customer detail: only overwrite with non-empty values ----
                if ($inspection->customer) {
                    $customerData = [];
                    foreach (['first_name', 'last_name', 'phone', 'email', 'address', 'city'] as $field) {
                        if (!empty($validated[$field])) {
                            $customerData[$field] = $validated[$field];
                        }
                    }
                    if ($customerData) {
                        $inspection->customer->update($customerData);
                    }
                }

                // ---- Vehicle detail ----
                if ($inspection->vehicle) {
                    $vehicleData = [];
                    foreach (['make', 'model', 'year', 'license_plate', 'vin', 'engine_no', 'transmission', 'fuel_type', 'color'] as $field) {
                        if (!empty($validated[$field])) {
                            $vehicleData[$field] = $validated[$field];
                        }
                    }
                    if (!empty($validated['odometer'])) {
                        $vehicleData['odometer'] = (int) $validated['odometer'];
                    }
                    if ($vehicleData) {
                        $inspection->vehicle->update($vehicleData);
                    }
                }

                // ---- Mirror onto the linked appointment (slip source of truth) ----
                if ($inspection->appointment) {
                    $appt = $inspection->appointment;
                    $apptData = [];
                    if (!empty($validated['service_types'])) {
                        $apptData['service_types'] = array_values($validated['service_types']);
                    }
                    if (array_key_exists('job_description_items', $validated)) {
                        $apptData['job_description_items'] = $this->normalizeRepairRows(
                            $validated['job_description_items'] ?? [],
                            ['description', 'mh', 'unit_price', 'labor_cost']
                        );
                    }
                    if (array_key_exists('parts_items', $validated)) {
                        $apptData['parts_items'] = $this->normalizeRepairRows(
                            $validated['parts_items'] ?? [],
                            ['description', 'qty', 'unit_price', 'cost']
                        );
                    }
                    if (!empty($validated['date_received'])) {
                        $apptData['date_received'] = $validated['date_received'];
                    }
                    if ($request->filled('service_request')) {
                        $apptData['service_request'] = $validated['service_request'];
                    }
                    if (!empty($validated['phone'])) {
                        $apptData['phone'] = $validated['phone'];
                    }
                    if (!empty($validated['email'])) {
                        $apptData['email'] = $validated['email'];
                    }
                    if (array_key_exists('discount', $validated)) {
                        $apptData['discount'] = $validated['discount'];
                    }
                    if (!empty($validated['first_name']) || !empty($validated['last_name'])) {
                        $fullName = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));
                        if ($fullName !== '') {
                            $apptData['name'] = $fullName;
                        }
                    }
                    if ($apptData) {
                        $appt->update($apptData);
                    }
                }

                // ---- Sync multi-technician assignments ----
                if ($request->has('technicians')) {
                    $technicianIds = array_filter($request->input('technicians', []));
                    if (!empty($technicianIds)) {
                        $syncData = [];
                        foreach ($technicianIds as $techId) {
                            $syncData[$techId] = ['role' => 'technician'];
                        }
                        $inspection->technicians()->sync($syncData);
                    } else {
                        $inspection->technicians()->sync([]);
                    }
                }
            });
        } catch (\Throwable $e) {
            \Log::error('Repair Order update failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update Repair Order: ' . $e->getMessage());
        }

        return redirect()->route('inspections.show', $inspection)
            ->with('success', 'Repair Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleInspection $inspection)
    {
        // Archive the inspection record before deletion
        try {
            $archiveService = app(\App\Services\ArchiveService::class);
            $archiveService->archive(
                $inspection,
                'inspection',
                auth()->id(),
                []
            );
            
            // Also mark the source appointment as completed so the vehicle becomes available
            if ($inspection->appointment_id) {
                $appointment = \App\Models\Appointment::find($inspection->appointment_id);
                if ($appointment && !in_array($appointment->appointment_status, ['completed', 'cancelled', 'no_show'])) {
                    $appointment->update(['appointment_status' => 'completed']);
                }
            }
        } catch (\Exception $e) {
            // If archiving fails, fall back to simple delete
            $inspection->delete();
        }
        
        return redirect()->route('inspections.index')
            ->with('success', 'Inspection moved to archive successfully.');
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
            
            // Check if it's an AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inspection completed.',
                    'status' => $inspection->inspection_status,
                    'redirect' => route('inspections.show', ['inspection' => $inspection->id, '_' => time()])
                ]);
            }
            
            // Add timestamp to URL to bust cache
            $timestamp = time();
            return redirect()->route('inspections.show', ['inspection' => $inspection->id, '_' => $timestamp])
                ->with('success', 'Inspection completed.')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }
        
        \Log::error('Failed to complete inspection ID: ' . $inspection->id);
        
        // Check if it's an AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to complete inspection.',
                'status' => $inspection->inspection_status
            ], 400);
        }
        
        return redirect()->back()
            ->with('error', 'Unable to complete inspection.')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
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
            
            // Check if it's an AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inspection marked as incomplete. The technician has been notified.',
                    'status' => $inspection->inspection_status,
                    'redirect' => route('inspections.show', ['inspection' => $inspection->id, '_' => time()])
                ]);
            }
            
            // Add timestamp to URL to bust cache
            $timestamp = time();
            return redirect()->route('inspections.show', ['inspection' => $inspection->id, '_' => $timestamp])
                ->with('success', 'Inspection marked as incomplete. The technician has been notified.')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }
        
        \Log::error('Failed to undo completion for inspection ID: ' . $inspection->id);
        
        // Check if it's an AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to undo inspection completion.',
                'status' => $inspection->inspection_status
            ], 400);
        }
        
        return redirect()->back()
            ->with('error', 'Unable to undo inspection completion.')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
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
     * Store a new inspection item.
     */
        // ========================
    // FINDINGS CRUD METHODS
    // ========================

    /**
     * Refuse findings/group mutations on a locked Repair Order (one promoted from
     * a Repair Quotation, where the amounts are fixed). Returns a response to send
     * back, or null when the edit is allowed.
     */
    private function denyIfFindingsLocked($inspection, Request $request)
    {
        if ($inspection && $inspection->isFindingsLocked()) {
            $msg = 'Naka-lock ang findings — fixed na mula sa Repair Quotation. I-unlock muna para maka-edit.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 423);
            }
            return back()->with('error', $msg);
        }
        return null;
    }

    /**
     * Unlock a Repair Order whose findings were fixed from a Repair Quotation.
     * Restricted to admins so the approved amounts are not changed by accident.
     */
    public function unlockFindings(Request $request, VehicleInspection $inspection)
    {
        $role = auth()->user()->role ?? null;
        if (! in_array($role, ['admin', 'super_admin', 'owner'], true)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Admin lang ang pwedeng mag-unlock.'], 403);
            }
            return back()->with('error', 'Admin lang ang pwedeng mag-unlock ng findings.');
        }

        $inspection->unlockFindings();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Na-unlock na ang findings — pwede nang i-edit.');
    }

    public function storeFinding(Request $request, VehicleInspection $inspection)
    {
        if ($resp = $this->denyIfFindingsLocked($inspection, $request)) { return $resp; }
        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'issue_title' => 'required|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'quantity' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'group_id' => 'nullable|exists:inspection_finding_groups,id',
            'detailed_notes' => 'nullable|string',
            'severity' => 'required|in:low,medium,high,critical',
            'recommended_action' => 'nullable|string',
            'estimated_urgency' => 'required|in:routine,soon,urgent,immediate',
            'estimated_cost' => 'nullable|numeric|min:0',
            'tech_id' => 'nullable|exists:users,id',
            'is_quotation_added' => 'nullable|boolean',
        ]);

        $validated['tech_id'] = $validated['tech_id'] ?? auth()->id();
        $validated['is_quotation_added'] = (bool) ($validated['is_quotation_added'] ?? false);
        $validated['sort_order'] = ($inspection->inspectionFindings()->max('sort_order') ?? -1) + 1;

        $finding = $inspection->inspectionFindings()->create($validated);
        $finding->load('technician');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'finding' => $finding]);
        }

        return redirect()->route('inspections.show', $inspection)->with('success', 'Finding added successfully.');
    }

    public function updateFinding(Request $request, InspectionFinding $finding)
    {
        if ($resp = $this->denyIfFindingsLocked($finding->inspection, $request)) { return $resp; }
        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'issue_title' => 'required|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'quantity' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'group_id' => 'nullable|exists:inspection_finding_groups,id',
            'detailed_notes' => 'nullable|string',
            'severity' => 'required|in:low,medium,high,critical',
            'recommended_action' => 'nullable|string',
            'estimated_urgency' => 'required|in:routine,soon,urgent,immediate',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        $finding->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'finding' => $finding->fresh()->load('technician')]);
        }

        return redirect()->route('inspections.show', $finding->inspection_id)->with('success', 'Finding updated.');
    }

    public function destroyFinding(Request $request, InspectionFinding $finding)
    {
        if ($resp = $this->denyIfFindingsLocked($finding->inspection, $request)) { return $resp; }
        $inspectionId = $finding->inspection_id;
        $finding->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('inspections.show', $inspectionId)->with('success', 'Finding removed.');
    }

    public function reorderFindings(Request $request, VehicleInspection $inspection)
    {
        if ($resp = $this->denyIfFindingsLocked($inspection, $request)) { return $resp; }
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:inspection_findings,id',
        ]);

        foreach ($request->order as $index => $id) {
            InspectionFinding::where('id', $id)
                ->where('inspection_id', $inspection->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function bulkAddFindings(Request $request, VehicleInspection $inspection)
    {
        if ($resp = $this->denyIfFindingsLocked($inspection, $request)) { return $resp; }
        $request->validate([
            'findings' => 'required|array',
            'findings.*.category' => 'required|string|max:100',
            'findings.*.issue_title' => 'required|string|max:255',
        ]);

        $currentMax = $inspection->inspectionFindings()->max('sort_order') ?? -1;
        $techId = auth()->id();
        $created = [];

        foreach ($request->findings as $i => $findingData) {
            $finding = $inspection->inspectionFindings()->create([
                'category' => $findingData['category'],
                'issue_title' => $findingData['issue_title'],
                'detailed_notes' => $findingData['detailed_notes'] ?? null,
                'severity' => $findingData['severity'] ?? 'medium',
                'recommended_action' => $findingData['recommended_action'] ?? null,
                'estimated_urgency' => $findingData['estimated_urgency'] ?? 'routine',
                'estimated_cost' => $findingData['estimated_cost'] ?? null,
                'tech_id' => $techId,
                'sort_order' => $currentMax + 1 + $i,
            ]);
            $created[] = $finding;
        }

        return response()->json(['success' => true, 'count' => count($created), 'findings' => $created]);
    }

    // ===================== FINDING GROUPS (drag & drop / shared labor) =====================

    public function storeGroup(Request $request, VehicleInspection $inspection)
    {
        if ($resp = $this->denyIfFindingsLocked($inspection, $request)) { return $resp; }
        $validated = $request->validate([
            'name' => 'nullable|string|max:150',
            'auto_name' => 'nullable|boolean',
            'labor_cost' => 'nullable|numeric|min:0',
        ]);

        $group = $inspection->findingGroups()->create([
            'name' => $validated['name'] ?? 'New Group',
            'auto_name' => $validated['auto_name'] ?? true,
            'labor_cost' => $validated['labor_cost'] ?? 0,
            'sort_order' => ($inspection->findingGroups()->max('sort_order') ?? -1) + 1,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'group' => $group]);
        }

        return redirect()->route('inspections.show', $inspection)->with('success', 'Group added.');
    }

    public function updateGroup(Request $request, InspectionFindingGroup $group)
    {
        if ($resp = $this->denyIfFindingsLocked($group->inspection, $request)) { return $resp; }
        $validated = $request->validate([
            'name' => 'nullable|string|max:150',
            'auto_name' => 'nullable|boolean',
            'labor_cost' => 'nullable|numeric|min:0',
        ]);

        $group->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'group' => $group->fresh()]);
        }

        return redirect()->route('inspections.show', $group->inspection_id)->with('success', 'Group updated.');
    }

    public function destroyGroup(Request $request, InspectionFindingGroup $group)
    {
        if ($resp = $this->denyIfFindingsLocked($group->inspection, $request)) { return $resp; }
        $inspectionId = $group->inspection_id;
        // Detach findings (keep them, just ungroup)
        InspectionFinding::where('group_id', $group->id)->update(['group_id' => null]);
        $group->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('inspections.show', $inspectionId)->with('success', 'Group removed.');
    }

    /**
     * Drop handler: move a finding into a group (or to ungrouped) and persist order.
     */
    public function moveFindingToGroup(Request $request, InspectionFinding $finding)
    {
        if ($resp = $this->denyIfFindingsLocked($finding->inspection, $request)) { return $resp; }
        $validated = $request->validate([
            'group_id' => 'nullable|exists:inspection_finding_groups,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $finding->update([
            'group_id' => $validated['group_id'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $finding->sort_order,
            // Moving a card back onto the board (or into a group) means it is pursued again.
            'is_declined' => false,
        ]);

        return response()->json(['success' => true, 'finding' => $finding->fresh()]);
    }

    /**
     * Toggle a finding between "pursued" and "not pursued".
     *
     * Declining = the customer did NOT push through with this item, so it is separated
     * out of the Repair Quotation (excluded from totals / the printed quotation).
     * Toggled by dragging a card into (or out of) the "Not Pursued" zone on the
     * Repair Quotation edit page.
     */
    public function declineFinding(Request $request, InspectionFinding $finding)
    {
        if ($resp = $this->denyIfFindingsLocked($finding->inspection, $request)) { return $resp; }
        $validated = $request->validate([
            'declined' => 'required|boolean',
        ]);

        $finding->update([
            'is_declined' => (bool) $validated['declined'],
        ]);

        return response()->json([
            'success' => true,
            'finding' => $finding->fresh(),
            'is_declined' => (bool) $finding->fresh()->is_declined,
        ]);
    }

    /**
     * Save the per-finding Labor cost (for UNGROUPED findings).
     *
     * Grouped findings share a single labor cost at the group level, but an ungrouped
     * finding carries its own labor in `estimated_cost` (the same column the Repair
     * Quotation slip reads). This is a tiny dedicated endpoint so the inline board
     * input can save just the labor amount without re-sending the whole finding.
     */
    public function updateFindingCost(Request $request, InspectionFinding $finding)
    {
        if ($resp = $this->denyIfFindingsLocked($finding->inspection, $request)) { return $resp; }
        $validated = $request->validate([
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        $finding->update([
            'estimated_cost' => $validated['estimated_cost'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'finding' => $finding->fresh(),
        ]);
    }

    public function storeItem(Request $request, VehicleInspection $inspection)
    {
        // Validate the request
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_status' => 'required|in:pending,passed,failed,attention_needed,not_applicable',
            'category' => 'nullable|string|max:255',
            'technician_notes' => 'nullable|string|max:1000',
            'item_type' => 'nullable|string|in:check,measurement,test,visual',
            'requires_attention' => 'nullable|boolean',
        ]);
        
        // Create the inspection item
        $item = new InspectionItem([
            'inspection_id' => $inspection->id,
            'item_name' => $validated['item_name'],
            'item_status' => $validated['item_status'],
            'technician_notes' => $validated['technician_notes'] ?? null,
            'item_type' => $validated['item_type'] ?? 'check',
            'requires_attention' => $validated['requires_attention'] ?? ($validated['item_status'] === 'attention_needed'),
        ]);
        
        // Handle category if provided
        if (!empty($validated['category']) && $validated['category'] !== 'Uncategorized') {
            // Find or create category
            $category = InspectionCategory::firstOrCreate(
                ['category_name' => $validated['category']],
                ['is_active' => true]
            );
            $item->category_id = $category->id;
        }
        
        // Save the item
        $item->save();
        
        // Update inspection counts
        $inspection->updateItemCounts();
        
        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Inspection item added successfully.',
                'item' => $item
            ]);
        }
        
        return redirect()->back()->with('success', 'Inspection item added successfully.');
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
    
    /**
     * Upload multiple photos for inspection in a single request.
     */
    public function uploadPhotos(Request $request, VehicleInspection $inspection)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB each
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $uploaded = [];
            foreach ($request->file('photos', []) as $file) {
                $path = $file->store('inspections/photos', 'public');
                $inspection->addPhoto($path, $request->description);
                $uploaded[] = [
                    'path' => $path,
                    'path_url' => asset('storage/' . $path),
                    'description' => $request->description,
                    'uploaded_at' => now()->toISOString(),
                ];
            }

            return response()->json([
                'success' => true,
                'message' => count($uploaded) . ' photo(s) uploaded successfully',
                'count' => count($uploaded),
                'photos' => $uploaded,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload photos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a photo from an inspection (AJAX endpoint)
     */
    public function deletePhoto(Request $request, VehicleInspection $inspection, $photoIndex)
    {
        try {
            // Convert photoIndex to integer
            $photoIndex = (int) $photoIndex;
            
            // Get current photos
            $photos = $inspection->photos ?? [];
            
            // Check if photo index exists
            if (!isset($photos[$photoIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Photo not found at index ' . $photoIndex
                ], 404);
            }
            
            // Get the photo path for potential file deletion
            $photoToDelete = $photos[$photoIndex];
            $photoPath = $photoToDelete['path'] ?? null;
            
            // Remove the photo from the array
            array_splice($photos, $photoIndex, 1);
            
            // Update the inspection with the new photos array
            $inspection->update(['photos' => $photos]);
            
            // Optional: Delete the actual file from storage
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully',
                'photoIndex' => $photoIndex
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete photo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update vehicle mileage for an inspection (AJAX endpoint)
     */
    public function updateMileage(Request $request, VehicleInspection $inspection)
    {
        \Log::info('updateMileage called', [
            'inspection_id' => $inspection->id,
            'has_csrf_token' => $request->has('_token'),
            'csrf_token' => $request->input('_token'),
            'vehicle_mileage' => $request->input('vehicle_mileage'),
            'is_ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
            'headers' => $request->headers->all()
        ]);
        
        try {
            // Validate the request
            $validated = $request->validate([
                'vehicle_mileage' => 'required|integer|min:0'
            ]);
            
            // Update the inspection mileage
            $inspection->update([
                'vehicle_mileage' => $validated['vehicle_mileage']
            ]);
            
            // Also update the vehicle's current mileage if this is the latest inspection
            // Note: Temporarily commented out until we verify the vehicle relationship and field exist
            /*
            if ($inspection->vehicle) {
                $latestInspection = $inspection->vehicle->inspections()
                    ->where('inspection_status', 'completed')
                    ->orderBy('inspection_completed_at', 'desc')
                    ->first();
                
                // If this is the latest completed inspection, update vehicle mileage
                if ($latestInspection && $latestInspection->id === $inspection->id) {
                    $inspection->vehicle->update([
                        'current_mileage' => $validated['vehicle_mileage']
                    ]);
                }
            }
            */
            
            return response()->json([
                'success' => true,
                'message' => 'Mileage updated successfully',
                'vehicle_mileage' => $validated['vehicle_mileage']
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update mileage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update inspection team (technician and service advisor)
     */
    public function updateTeam(Request $request, VehicleInspection $inspection)
    {
        try {
            \Log::info('updateTeam called', [
                'inspection_id' => $inspection->id,
                'technician_id_input' => $request->input('technician_id'),
                'service_advisor_id_input' => $request->input('service_advisor_id'),
                'all_inputs' => $request->all()
            ]);
            
            // Validate the request
            $validated = $request->validate([
                'technician_id' => 'nullable|exists:users,id',
                'service_advisor_id' => 'nullable|exists:users,id'
            ]);
            
            \Log::info('updateTeam validated', [
                'validated_technician_id' => $validated['technician_id'] ?? null,
                'validated_service_advisor_id' => $validated['service_advisor_id'] ?? null
            ]);
            
            // Update the inspection team - convert empty strings to null
            $updateData = [];
            
            if (isset($validated['technician_id']) && $validated['technician_id'] !== '') {
                $updateData['technician_id'] = $validated['technician_id'];
            } else {
                $updateData['technician_id'] = null;
            }
            
            if (isset($validated['service_advisor_id']) && $validated['service_advisor_id'] !== '') {
                $updateData['service_advisor_id'] = $validated['service_advisor_id'];
            } else {
                $updateData['service_advisor_id'] = null;
            }
            
            $inspection->update($updateData);
            
            \Log::info('updateTeam updated', [
                'updated_technician_id' => $inspection->technician_id,
                'updated_service_advisor_id' => $inspection->service_advisor_id
            ]);
            
            // Load the updated relationships for the response
            $inspection->load(['technician', 'serviceAdvisor']);
            
            return response()->json([
                'success' => true,
                'message' => 'Inspection team updated successfully',
                'data' => [
                    'technician' => $inspection->technician ? [
                        'id' => $inspection->technician->id,
                        'name' => $inspection->technician->name
                    ] : null,
                    'service_advisor' => $inspection->serviceAdvisor ? [
                        'id' => $inspection->serviceAdvisor->id,
                        'name' => $inspection->serviceAdvisor->name
                    ] : null
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inspection team: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all findings for a customer's recent inspections.
     */
    public function findingsByCustomer(Customer $customer)
    {
        $findings = \App\Models\InspectionFinding::whereHas('inspection', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })->with(['inspection', 'technician', 'group'])
          ->orderBy('created_at', 'desc')
          ->get();

        return response()->json([
            'success' => true,
            'findings' => $findings,
        ]);
    }

    /**
     * Show a printable Repair Order slip for a Repair Order (inspection).
     *
     * Walk-in ROs have no appointment, so the slip is built from the RO's own
     * intake items; scheduled ROs still fall back to the linked appointment.
     * Additive — mirrors the appointment slip layout (shared partial).
     */
    public function showRepairOrderSlip(VehicleInspection $inspection)
    {
        $inspection->load(['customer', 'vehicle', 'appointment']);

        return view('inspections.repair-order-slip', $this->repairSlipData($inspection));
    }

    /**
     * Download the Repair Order slip as a PDF.
     */
    public function downloadRepairOrderSlipPdf(VehicleInspection $inspection)
    {
        $inspection->load(['customer', 'vehicle', 'appointment']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.inspection-repair-order-slip', $this->repairSlipData($inspection))
            ->setPaper('a4', 'portrait');

        $reference = $inspection->appointment->appointment_number ?? ('RO-' . str_pad($inspection->id, 6, '0', STR_PAD_LEFT));
        $filename = 'Repair-Order-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $reference) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Build the variables the shared Repair Order slip partial expects.
     * RO's own intake items win; the linked appointment is the fallback for
     * legacy appointment-driven orders.
     */
    protected function repairSlipData(VehicleInspection $inspection): array
    {
        $appointment = $inspection->appointment;

        $services = config('service-types.list', []);
        $selectedTypes = $inspection->service_types ?: ($appointment->service_types ?? []);
        if (is_string($selectedTypes)) {
            $decoded = json_decode($selectedTypes, true);
            $selectedTypes = is_array($decoded) ? $decoded : ($selectedTypes !== '' ? [$selectedTypes] : []);
        }
        $selectedTypes = is_array($selectedTypes) ? array_values(array_filter($selectedTypes, 'is_string')) : [];

        $jdItems = $inspection->job_description_items ?: ($appointment->job_description_items ?? []);
        $partsItems = $inspection->parts_items ?: ($appointment->parts_items ?? []);

        return [
            'inspection'    => $inspection,
            'services'      => $services,
            'selectedTypes' => $selectedTypes,
            'slipCustomer'  => $inspection->customer,
            'slipVehicle'   => $inspection->vehicle,
            'slipJdItems'   => is_array($jdItems) ? $jdItems : [],
            'slipPartsItems' => is_array($partsItems) ? $partsItems : [],
            'slipDiscount'  => $inspection->discount ?: ($appointment->discount ?? 0),
            'slipConcern'   => $inspection->customer_concerns ?: ($appointment->service_request ?? null),
            'slipReference' => $appointment->appointment_number ?? ('RO-' . str_pad($inspection->id, 6, '0', STR_PAD_LEFT)),
            'slipDate'      => $inspection->date_received ?? ($appointment->appointment_date ?? $inspection->created_at),
        ];
    }

    /**
     * Show a printable Repair Quotation for a Repair Order, built from the
     * Repair Order's findings (grouped or ungrouped). Additive — mirrors the
     * appointment Repair Order slip, nothing existing is touched.
     */
    public function showQuotationSlip(VehicleInspection $inspection)
    {
        [$inspection, $estimate] = $this->loadQuotationData($inspection);

        return view('inspections.quotation-slip', compact('inspection', 'estimate'));
    }

    /**
     * Download the Repair Quotation as a PDF.
     */
    public function downloadQuotationSlipPdf(VehicleInspection $inspection)
    {
        [$inspection, $estimate] = $this->loadQuotationData($inspection);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.repair-quotation', compact('inspection', 'estimate'))
            ->setPaper('a4', 'portrait');

        $no = ($estimate->estimate_number ?? null)
            ?: ($inspection->appointment->appointment_number ?? $inspection->id);

        $filename = $this->quotationFilename([
            $inspection->vehicle->make ?? null,
            $inspection->vehicle->model ?? null,
            $inspection->vehicle->year ?? null,
            $inspection->customer->first_name ?? null,
            $no,
        ], 'Repair-Quotation-' . $no);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Build a safe PDF filename from parts (Vehicle Brand, Model, Year, First name,
     * Reference number). Empty parts are skipped; illegal filesystem characters removed.
     */
    protected function quotationFilename(array $parts, string $fallback): string
    {
        $name = trim(collect($parts)->filter(function ($p) {
            return $p !== null && trim((string) $p) !== '';
        })->implode(' '));

        $name = preg_replace('/[\x00-\x1F\/\\:*?"<>|]+/', '', $name);
        $name = trim(preg_replace('/\s+/', ' ', (string) $name));

        return $name !== '' ? $name : $fallback;
    }

    /**
     * Load the inspection with everything the Repair Quotation sheet needs.
     */
    protected function loadQuotationData(VehicleInspection $inspection): array
    {
        $inspection->load([
            'customer',
            'vehicle',
            'appointment',
            'inspectionFindings.group',
            'findingGroups',
        ]);

        $estimate = \App\Models\Estimate::where('inspection_id', $inspection->id)
            ->latest('id')
            ->first();

        return [$inspection, $estimate];
    }

    /**
     * Normalise Repair Order slip line items coming from the Edit form:
     * trim text, cast numbers, and drop fully-empty rows.
     */
    protected function normalizeRepairRows($rows, array $fields): array
    {
        if (!is_array($rows)) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $clean = [];
            foreach ($fields as $f) {
                $val = $row[$f] ?? null;
                if ($f === 'description') {
                    $clean[$f] = is_string($val) ? trim($val) : '';
                } else {
                    $clean[$f] = ($val === '' || $val === null) ? null : (float) $val;
                }
            }
            $hasText = ($clean['description'] ?? '') !== '';
            $hasNum = false;
            foreach ($fields as $f) {
                if ($f !== 'description' && $clean[$f] !== null) {
                    $hasNum = true;
                    break;
                }
            }
            if ($hasText || $hasNum) {
                $out[] = $clean;
            }
        }
        return $out;
    }
}