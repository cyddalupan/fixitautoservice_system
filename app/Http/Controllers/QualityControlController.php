<?php

namespace App\Http\Controllers;

use App\Models\QualityControlChecklist;
use App\Models\QualityAudit;
use App\Models\NonConformanceReport;
use App\Models\CorrectiveAction;
use App\Models\WorkOrder;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QualityControlController extends Controller
{
    /**
     * Display quality control dashboard
     */
    public function dashboard()
    {
        // Get quality metrics
        $metrics = $this->getQualityMetrics();
        
        // Get recent audits
        $recentAudits = QualityAudit::with(['checklist', 'technician', 'auditor', 'vehicle'])
            ->orderBy('audit_date', 'desc')
            ->limit(10)
            ->get();
        
        // Get open NCRs
        $openNcrs = NonConformanceReport::with(['technician', 'assignee', 'vehicle'])
            ->whereIn('status', ['open', 'investigating', 'action_required'])
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
        
        // Get overdue corrective actions
        $overdueActions = CorrectiveAction::with(['assignee', 'nonConformanceReport'])
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
        
        // Get technician performance
        $technicianPerformance = $this->getTechnicianQualityPerformance();
        
        return view('quality-control.dashboard', compact(
            'metrics',
            'recentAudits',
            'openNcrs',
            'overdueActions',
            'technicianPerformance'
        ));
    }
    
    /**
     * Display checklist management
     */
    public function checklists(Request $request)
    {
        $query = QualityControlChecklist::with(['creator', 'updater']);
        
        // Apply filters
        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $checklists = $query->orderBy('name')->paginate(20);
        
        $serviceTypes = QualityControlChecklist::distinct('service_type')
            ->pluck('service_type')
            ->map(function($type) {
                return [
                    'value' => $type,
                    'label' => str_replace('_', ' ', ucfirst($type))
                ];
            });
        
        return view('quality-control.checklists', compact('checklists', 'serviceTypes'));
    }
    
    /**
     * Show form to create new checklist
     */
    public function createChecklist()
    {
        $serviceTypes = $this->getServiceTypes();
        return view('quality-control.checklists-create', compact('serviceTypes'));
    }
    
    /**
     * Store new checklist
     */
    public function storeChecklist(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'service_type' => 'required|string|max:100',
            'checklist_items' => 'required|array',
            'checklist_items.*.description' => 'required|string',
            'checklist_items.*.requirement' => 'required|string',
            'checklist_items.*.score' => 'required|integer|min:1|max:100',
            'checklist_items.*.max_score' => 'required|integer|min:1|max:100',
            'passing_score' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean'
        ]);
        
        $validated['checklist_items'] = json_encode($validated['checklist_items']);
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        
        $checklist = QualityControlChecklist::create($validated);
        
        return redirect()->route('quality-control.checklists')
            ->with('success', 'Checklist created successfully.');
    }
    
    /**
     * Show checklist details
     */
    public function showChecklist($id)
    {
        $checklist = QualityControlChecklist::with(['creator', 'updater', 'audits'])->findOrFail($id);
        $auditStats = $this->getChecklistAuditStats($checklist);
        
        return view('quality-control.checklists-show', compact('checklist', 'auditStats'));
    }
    
    /**
     * Show form to edit checklist
     */
    public function editChecklist($id)
    {
        $checklist = QualityControlChecklist::findOrFail($id);
        $serviceTypes = $this->getServiceTypes();
        
        return view('quality-control.checklists-edit', compact('checklist', 'serviceTypes'));
    }
    
    /**
     * Update checklist
     */
    public function updateChecklist(Request $request, $id)
    {
        $checklist = QualityControlChecklist::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'service_type' => 'required|string|max:100',
            'checklist_items' => 'required|array',
            'checklist_items.*.description' => 'required|string',
            'checklist_items.*.requirement' => 'required|string',
            'checklist_items.*.score' => 'required|integer|min:1|max:100',
            'checklist_items.*.max_score' => 'required|integer|min:1|max:100',
            'passing_score' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean'
        ]);
        
        $validated['checklist_items'] = json_encode($validated['checklist_items']);
        $validated['updated_by'] = auth()->id();
        
        $checklist->update($validated);
        
        return redirect()->route('quality-control.checklists.show', $checklist->id)
            ->with('success', 'Checklist updated successfully.');
    }
    
    /**
     * Create new version of checklist
     */
    public function createChecklistVersion(Request $request, $id)
    {
        $checklist = QualityControlChecklist::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'checklist_items' => 'required|array',
            'checklist_items.*.description' => 'required|string',
            'checklist_items.*.requirement' => 'required|string',
            'checklist_items.*.score' => 'required|integer|min:1|max:100',
            'checklist_items.*.max_score' => 'required|integer|min:1|max:100',
            'passing_score' => 'required|integer|min:1|max:100'
        ]);
        
        $validated['checklist_items'] = json_encode($validated['checklist_items']);
        $validated['service_type'] = $checklist->service_type;
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        
        $newChecklist = $checklist->createNewVersion($validated);
        
        return redirect()->route('quality-control.checklists.show', $newChecklist->id)
            ->with('success', 'New checklist version created successfully.');
    }
    
    /**
     * Display audit management
     */
    public function audits(Request $request)
    {
        $query = QualityAudit::with(['checklist', 'technician', 'auditor', 'vehicle', 'workOrder']);
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }
        
        if ($request->has('auditor_id')) {
            $query->where('auditor_id', $request->auditor_id);
        }
        
        if ($request->has('start_date')) {
            $query->where('audit_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('audit_date', '<=', $request->end_date);
        }
        
        $audits = $query->orderBy('audit_date', 'desc')->paginate(20);
        
        $technicians = User::whereHas('roles', function($q) {
            $q->where('name', 'technician');
        })->get();
        
        $auditors = User::whereHas('roles', function($q) {
            $q->where('name', 'quality_auditor');
        })->get();
        
        return view('quality-control.audits', compact('audits', 'technicians', 'auditors'));
    }
    
    /**
     * Show form to create new audit
     */
    public function createAudit()
    {
        $checklists = QualityControlChecklist::active()->get();
        $workOrders = WorkOrder::where('status', 'completed')
            ->with(['vehicle', 'technician'])
            ->orderBy('completed_at', 'desc')
            ->limit(50)
            ->get();
        
        $technicians = User::whereHas('roles', function($q) {
            $q->where('name', 'technician');
        })->get();
        
        $auditors = User::whereHas('roles', function($q) {
            $q->where('name', 'quality_auditor');
        })->get();
        
        return view('quality-control.audits-create', compact('checklists', 'workOrders', 'technicians', 'auditors'));
    }
    
    /**
     * Store new audit
     */
    public function storeAudit(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'checklist_id' => 'required|exists:quality_control_checklists,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'technician_id' => 'nullable|exists:users,id',
            'auditor_id' => 'required|exists:users,id',
            'audit_date' => 'required|date',
            'audit_results' => 'required|array',
            'findings' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'follow_up_date' => 'nullable|date'
        ]);
        
        // Get checklist to calculate scores
        $checklist = QualityControlChecklist::findOrFail($validated['checklist_id']);
        $scoreResult = $checklist->calculateScore($validated['audit_results']);
        
        // Generate audit number
        $validated['audit_number'] = 'AUD-' . strtoupper(uniqid());
        $validated['audit_results'] = json_encode($validated['audit_results']);
        $validated['total_score'] = $scoreResult['total_score'];
        $validated['max_score'] = $scoreResult['max_score'];
        $validated['percentage_score'] = $scoreResult['percentage'];
        $validated['status'] = $scoreResult['passed'] ? 'completed' : 'failed';
        $validated['created_by'] = auth()->id();
        
        $audit = QualityAudit::create($validated);
        
        return redirect()->route('quality-control.audits.show', $audit->id)
            ->with('success', 'Audit created successfully.');
    }
    
    /**
     * Show audit details
     */
    public function showAudit($id)
    {
        $audit = QualityAudit::with([
            'checklist', 
            'technician', 
            'auditor', 
            'vehicle', 
            'workOrder',
            'nonConformanceReports',
            'creator'
        ])->findOrFail($id);
        
        $detailedResults = $audit->getDetailedResults();
        
        return view('quality-control.audits-show', compact('audit', 'detailedResults'));
    }
    
    /**
     * Update audit status
     */
    public function updateAuditStatus(Request $request, $id)
    {
        $audit = QualityAudit::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,failed,cancelled',
            'notes' => 'nullable|string'
        ]);
        
        $audit->updateStatus($validated['status'], $validated['notes'] ?? null);
        
        return redirect()->route('quality-control.audits.show', $audit->id)
            ->with('success', 'Audit status updated successfully.');
    }
    
    /**
     * Generate audit report
     */
    public function generateAuditReport($id)
    {
        $audit = QualityAudit::with([
            'checklist', 
            'technician', 
            'auditor', 
            'vehicle', 
            'workOrder'
        ])->findOrFail($id);
        
        $report = $audit->generateReport();
        
        // Return as JSON for now - could be PDF in production
        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }
    
    /**
     * Display non-conformance reports
     */
    public function nonConformanceReports(Request $request)
    {
        $query = NonConformanceReport::with(['technician', 'assignee', 'vehicle', 'audit', 'workOrder']);
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }
        
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        
        $ncrs = $query->orderBy('reported_date', 'desc')->paginate(20);
        
        $technicians = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['technician', 'quality_manager', 'service_manager']);
        })->get();
        
        return view('quality-control.non-conformance-reports', compact('ncrs', 'technicians'));
    }
    
    /**
     * Show NCR details
     */
    public function showNonConformanceReport($id)
    {
        $ncr = NonConformanceReport::with([
            'technician',
            'assignee',
            'reporter',
            'vehicle',
            'audit',
            'workOrder',
            'correctiveActions' => function($query) {
                $query->with(['assignee', 'assigner', 'verifier']);
            },
            'creator'
        ])->findOrFail($id);
        
        return view('quality-control.non-conformance-reports-show', compact('ncr'));
    }
    
    /**
     * Update NCR status
     */
    public function updateNcrStatus(Request $request, $id)
    {
        $ncr = NonConformanceReport::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:open,investigating,action_required,resolved,closed',
            'notes' => 'nullable|string'
        ]);
        
        $ncr->updateStatus($validated['status'], $validated['notes'] ?? null);
        
        return redirect()->route('quality-control.non-conformance-reports.show', $ncr->id)
            ->with('success', 'NCR status updated successfully.');
    }
    
    /**
     * Assign NCR to user
     */
    public function assignNcr(Request $request, $id)
    {
        $ncr = NonConformanceReport::findOrFail($id);
        
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'required|date'
        ]);
        
        $ncr->assignTo($validated['assigned_to'], $validated['due_date']);
        
        return redirect()->route('quality-control.non-conformance-reports.show', $ncr->id)
            ->with('success', 'NCR assigned successfully.');
    }
    
    /**
     * Add root cause to NCR
     */
    public function addRootCause(Request $request, $id)
    {
        $ncr = NonConformanceReport::findOrFail($id);
        
        $validated = $request->validate([
            'root_cause' => 'required|string'
        ]);
        
        $ncr->addRootCause($validated['root_cause']);
        
        return redirect()->route('quality-control.non-conformance-reports.show', $ncr->id)
            ->with('success', 'Root cause added successfully.');
    }
    
    /**
     * Display corrective actions
     */
    public function correctiveActions(Request $request)
    {
        $query = CorrectiveAction::with(['assignee', 'assigner', 'nonConformanceReport', 'standard']);
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('action_type')) {
            $query->where('action_type', $request->action_type);
        }
        
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        
        if ($request->has('overdue')) {
            $query->overdue();
        }
        
        $