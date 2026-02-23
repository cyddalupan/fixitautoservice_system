<?php

namespace App\Http\Controllers;

use App\Models\QualityAudit;
use App\Models\QualityControlChecklist;
use App\Models\NonConformanceReport;
use App\Models\CorrectiveAction;
use App\Models\WorkOrder;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuditController extends Controller
{
    /**
     * Display audit management dashboard
     */
    public function dashboard()
    {
        // Get audit metrics
        $metrics = $this->getAuditMetrics();
        
        // Get recent audits
        $recentAudits = QualityAudit::with(['checklist', 'technician', 'auditor', 'vehicle'])
            ->orderBy('audit_date', 'desc')
            ->limit(10)
            ->get();
        
        // Get upcoming audits
        $upcomingAudits = QualityAudit::with(['checklist', 'technician', 'auditor', 'vehicle'])
            ->where('audit_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('audit_date', 'asc')
            ->limit(10)
            ->get();
        
        // Get audit schedule by week
        $auditSchedule = $this->getAuditSchedule();
        
        // Get audit performance by checklist
        $checklistPerformance = $this->getChecklistPerformance();
        
        return view('audit.dashboard', compact(
            'metrics',
            'recentAudits',
            'upcomingAudits',
            'auditSchedule',
            'checklistPerformance'
        ));
    }
    
    /**
     * Display all audits with filtering
     */
    public function index(Request $request)
    {
        $query = QualityAudit::with(['checklist', 'technician', 'auditor', 'vehicle', 'workOrder']);
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('checklist_id')) {
            $query->where('checklist_id', $request->checklist_id);
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
        
        if ($request->has('score_min')) {
            $query->where('percentage_score', '>=', $request->score_min);
        }
        
        if ($request->has('score_max')) {
            $query->where('percentage_score', '<=', $request->score_max);
        }
        
        $audits = $query->orderBy('audit_date', 'desc')->paginate(20);
        
        $checklists = QualityControlChecklist::active()->get();
        $technicians = User::whereHas('roles', function($q) {
            $q->where('name', 'technician');
        })->get();
        
        $auditors = User::whereHas('roles', function($q) {
            $q->where('name', 'quality_auditor');
        })->get();
        
        return view('audit.index', compact('audits', 'checklists', 'technicians', 'auditors'));
    }
    
    /**
     * Show form to create new audit
     */
    public function create()
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
        
        $vehicles = Vehicle::with('customer')
            ->orderBy('make')
            ->orderBy('model')
            ->limit(100)
            ->get();
        
        return view('audit.create', compact('checklists', 'workOrders', 'technicians', 'auditors', 'vehicles'));
    }
    
    /**
     * Store new audit
     */
    public function store(Request $request)
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
        
        // Create NCR if audit failed
        if (!$scoreResult['passed']) {
            $this->createNcrFromAudit($audit, $scoreResult);
        }
        
        return redirect()->route('audit.show', $audit->id)
            ->with('success', 'Audit created successfully.');
    }
    
    /**
     * Show audit details
     */
    public function show($id)
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
        
        return view('audit.show', compact('audit', 'detailedResults'));
    }
    
    /**
     * Show form to edit audit
     */
    public function edit($id)
    {
        $audit = QualityAudit::findOrFail($id);
        
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
        
        $vehicles = Vehicle::with('customer')
            ->orderBy('make')
            ->orderBy('model')
            ->limit(100)
            ->get();
        
        return view('audit.edit', compact('audit', 'checklists', 'workOrders', 'technicians', 'auditors', 'vehicles'));
    }
    
    /**
     * Update audit
     */
    public function update(Request $request, $id)
    {
        $audit = QualityAudit::findOrFail($id);
        
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
        
        $validated['audit_results'] = json_encode($validated['audit_results']);
        $validated['total_score'] = $scoreResult['total_score'];
        $validated['max_score'] = $scoreResult['max_score'];
        $validated['percentage_score'] = $scoreResult['percentage'];
        $validated['status'] = $scoreResult['passed'] ? 'completed' : 'failed';
        
        $audit->update($validated);
        
        return redirect()->route('audit.show', $audit->id)
            ->with('success', 'Audit updated successfully.');
    }
    
    /**
     * Update audit status
     */
    public function updateStatus(Request $request, $id)
    {
        $audit = QualityAudit::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,failed,cancelled',
            'notes' => 'nullable|string'
        ]);
        
        $audit->updateStatus($validated['status'], $validated['notes'] ?? null);
        
        return redirect()->route('audit.show', $audit->id)
            ->with('success', 'Audit status updated successfully.');
    }
    
    /**
     * Generate audit report
     */
    public function generateReport($id)
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
     * Create NCR from failed audit
     */
    public function createNcrFromAudit(QualityAudit $audit, $scoreResult)
    {
        $failedItems = collect($scoreResult['items'])
            ->where('passed', false)
            ->map(function($item) {
                return $item['description'] . ': ' . $item['requirement'];
            })
            ->implode("\n");
        
        $ncr = NonConformanceReport::create([
            'ncr_number' => 'NCR-' . strtoupper(uniqid()),
            'title' => 'Audit Failure: ' . $audit->title,
            'description' => "Audit failed with score: {$scoreResult['percentage']}%\n\nFailed Items:\n{$failedItems}",
            'type' => 'quality_audit',
            'severity' => 'major',
            'audit_id' => $audit->id,
            'work_order_id' => $audit->work_order_id,
            'vehicle_id' => $audit->vehicle_id,
            'technician_id' => $audit->technician_id,
            'reported_by' => $audit->auditor_id,
            'reported_date' => now(),
            'status' => 'open',
            'created_by' => $audit->created_by
        ]);
        
        return $ncr;
    }
    
    /**
     * Schedule recurring audits
     */
    public function scheduleRecurring(Request $request)
    {
        $validated = $request->validate([
            'checklist_id' => 'required|exists:quality_control_checklists,id',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'auditor_id' => 'required|exists:users,id',
            'technician_ids' => 'nullable|array',
            'technician_ids.*' => 'exists:users,id'
        ]);
        
        $scheduledAudits = [];
        $currentDate = Carbon::parse($validated['start_date']);
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : $currentDate->copy()->addYear();
        
        while ($currentDate <= $endDate) {
            foreach ($validated['technician_ids'] ?? [null] as $technicianId) {
                $audit = QualityAudit::create([
                    'audit_number' => 'AUD-' . strtoupper(uniqid()),
                    'title' => 'Scheduled Audit - ' . $currentDate->format('Y-m-d'),
                    'description' => 'Recurring audit scheduled via system',
                    'checklist_id' => $validated['checklist_id'],
                    'technician_id' => $technicianId,
                    'auditor_id' => $validated['auditor_id'],
                    'audit_date' => $currentDate->format('Y-m-d'),
                    'status' => 'scheduled',
                    'created_by' => auth()->id()
                ]);
                
                $scheduledAudits[] = $audit;
            }
            
            // Increment date based on frequency
            switch ($validated['frequency']) {
                case 'daily':
                    $currentDate->addDay();
                    break;
                case 'weekly':
                    $currentDate->addWeek();
                    break;
                case 'monthly':
                    $currentDate->addMonth();
                    break;
                case 'quarterly':
                    $currentDate->addMonths(3);
                    break;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => count($scheduledAudits) . ' audits scheduled',
            'audits' => $scheduledAudits
        ]);
    }
    
    /**
     * Get audit metrics for dashboard
     */
    private function getAuditMetrics()
    {
        $thirtyDaysAgo = now()->subDays(30);
        
        // Overall metrics
        $overallMetrics = QualityAudit::select(
            DB::raw('COUNT(*) as total_audits'),
            DB::raw('SUM(CASE WHEN percentage_score >= (SELECT passing_score FROM quality_control_checklists WHERE id = quality_audits.checklist_id) THEN 1 ELSE 0 END) as passed_audits'),
            DB::raw('AVG(percentage_score) as avg_score'),
            DB::raw('SUM(CASE WHEN status = "scheduled" THEN 1 ELSE 0 END) as scheduled_audits'),
            DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_audits')
        )->where('audit_date', '>=', $thirtyDaysAgo)
         ->first();
        
        // Trend metrics
        $trendMetrics = QualityAudit::select(
            DB::raw('DATE(audit_date) as audit_day'),
            DB::raw('COUNT(*) as daily_audits'),
            DB::raw('AVG(percentage_score) as daily_avg_score')
        )->where('audit_date', '>=', $thirtyDaysAgo)
         ->groupBy('audit_day')
         ->orderBy('audit_day', 'desc')
         ->limit(7)
         ->get();
        
        // NCR metrics from audits
        $ncrMetrics = NonConformanceReport::whereHas('audit')
            ->select(
                DB::raw('COUNT(*) as total_ncrs'),
                DB::raw('SUM(CASE WHEN severity = "critical" THEN 1 ELSE 0 END) as critical_ncrs'),
                DB::raw('SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END) as open_ncrs')
            )->where('reported_date', '>=', $thirtyDaysAgo)
             ->first();
        
        // Calculate pass rate
        $passRate = $overallMetrics->total_audits > 0 
            ? ($overallMetrics->passed_audits / $overallMetrics->total_audits) * 100 
            : 0;
        
        // Calculate trend
        $trend = $this->calculateAuditTrend($trendMetrics);
        
        return [
            'overall' => [
                'total' => $overallMetrics->total_audits ?? 0,
                'passed' => $overallMetrics->passed_audits ?? 0,
                'avg_score' => round($overallMetrics->avg_score ?? 0, 2),
                'pass_rate' => round($passRate, 2),
                'scheduled' => $overallMetrics->scheduled_audits ?? 0,
                'in_progress' => $overallMetrics->in_progress_audits ?? 0
            ],
            'trend' => $trend,
            'ncrs' => [
                'total' => $ncrMetrics->total_ncrs ?? 0,
                'critical' => $ncrMetrics->critical_ncrs ?? 0,
                'open' => $ncrMetrics->open_ncrs ?? 0
            ]
        ];
    }
    
    /**
     * Calculate audit trend
     */
    private function calculateAuditTrend($trendMetrics)
    {
        if ($trendMetrics->count() < 2) {
            return 'stable';
        }
        
        $scores = $trendMetrics->pluck('daily_avg_score')->toArray();
        $recentScores = array_slice($scores, 0, 3);
        $olderScores = array_slice($scores, 3);
        
        if (empty($olderScores)) {
            return 'stable';
        }
        
        $recentAvg = array_sum($recentScores) / count($recentScores);
        $olderAvg = array_sum($olderScores) / count($olderScores);
        
        if ($recentAvg > $olderAvg + 5) {
            return 'improving';
        } elseif ($recentAvg < $olderAvg - 5) {
            return 'declining';
        } else {
            return 'stable';
        }
    }
    
    /**
     * Get audit schedule by week
     */
    private function getAuditSchedule()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        
        return QualityAudit::with(['checklist', 'technician', 'auditor'])
            ->whereBetween('audit_date', [$startOfWeek, $endOfWeek])
            ->orderBy('audit_date')
            ->orderBy('auditor_id')
            ->get()
            ->groupBy(function($audit) {
                return $audit->audit_date->format('l');
            })
            ->map(function($dayAudits) {
                return $dayAudits->groupBy('auditor_id')
                    ->map(function($auditorAudits) {
                        return [
                            'auditor' => $auditorAudits->first()->auditor->name ?? 'Unknown',
                            'audits' => $auditorAudits->count(),
                            'details' => $auditorAudits->map(function($audit) {
                                return [
                                    'time' => $audit->audit_date->format('H:i'),
                                    'title' => $audit->title,
                                    'technician' => $audit->technician->name ?? 'N/A',
                                    'status' => $audit->status
                                ];
                            })
                        ];
                    });
            });
    }
    
    /**
     * Get checklist performance
     */
    private function getChecklistPerformance()
    {
        return QualityControlChecklist::withCount(['audits as total_audits' => function($query) {
                $query->where('audit_date', '>=', now()->subDays(30));
            }])
            ->withCount(['audits as passed_audits' => function($query) {
                $query->where('audit_date', '>=', now()->subDays(30))
                      ->whereColumn('percentage_score', '>=', 'passing_score');
            }])
            ->with(['audits' => function($query) {
                $query->where('audit_date', '>=', now()->subDays(30))
                      ->orderBy('audit_date', 'desc');
            }])
            ->having('total_audits', '>', 0)
            ->orderByDesc('total_audits')
            ->limit(10)
            ->get()
            ->map(function($checklist) {
                $avgScore = $checklist->audits->avg('percentage_score') ?? 0;
                $passRate = $checklist->total_audits > 0 
                    ? ($checklist->passed_audits / $checklist->total_audits) * 100 
                    : 0;
                
                return [
                    'id' => $checklist->id,
                    'name' => $checklist->name,
                    'service_type' => $checklist->service_type,
                    'total_audits' => $checklist->total_audits,
                    'passed_audits' => $checklist->passed_audits,
                    'avg_score' => round($avgScore, 2),
                    'pass_rate' => round($passRate, 2),
                    'performance' => $passRate >= 90 ? 'excellent' : ($passRate >= 80 ? 'good' : ($passRate >= 70 ? 'fair' : 'poor'))
                ];
            });
    }
    
    /**
     * Export audit data
     */
    public function export(Request $request)
    {
        $query = QualityAudit::with(['checklist', 'technician', 'auditor', 'vehicle']);
        
        if ($request->has('start_date')) {
            $query->where('audit_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('audit_date', '<=', $request->end_date);
        }
        
        $audits = $query->orderBy('audit_date', 'desc')->get();
        
        $data = $audits->map(function($audit) {
            return [
                'Audit Number' => $audit->audit_number,
                'Title' => $audit->title,
                'Checklist' => $audit->checklist->name ?? 'N/A',
                'Technician' => $audit->technician->name ?? 'N/A',
                'Auditor' => $audit->auditor->name ?? 'N/A',
                'Vehicle' => $audit->vehicle ? $audit->vehicle->make . ' ' . $audit->vehicle->model : 'N/A',
                'Date' => $audit->audit_date->format('Y-m-d'),
                'Score' => $audit->percentage_score . '%',
                'Status' => $audit->status,
                'Passed' => $audit->passed() ? 'Yes' : 'No',
                'Findings' => $audit->findings ? 'Yes' : 'No',
                'Follow-up Date' => $audit->follow_up_date ? $audit->follow_up_date->format('Y-m-d') : 'N/A'
            ];
        });
        
        $filename = 'audit_export_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
            }
            
            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Get audit statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        // Daily statistics
        $dailyStats = QualityAudit::select(
            DB::raw('DATE(audit_date) as audit_day'),
            DB::raw('COUNT(*) as total_audits'),
            DB::raw('AVG(percentage_score) as avg_score'),
            DB::raw('SUM(CASE WHEN percentage_score >= (SELECT passing_score FROM quality_control_checklists WHERE id = quality_audits.checklist_id) THEN 1 ELSE 0 END) as passed_audits'),
            DB::raw('SUM(CASE WHEN percentage_score < (SELECT passing_score FROM quality_control_checklists WHERE id = quality_audits.checklist_id) THEN 1 ELSE 0 END) as failed_audits')
        )
        ->whereBetween('audit_date', [$startDate, $endDate])
        ->groupBy('audit_day')
        ->orderBy('audit_day')
        ->get();
        
        // Checklist statistics
        $checklistStats = QualityAudit::select(
            'checklist_id',
            DB::raw('COUNT(*) as total_audits'),
            DB::raw('AVG(percentage_score) as avg_score'),
            DB::raw('MIN(percentage_score) as min_score'),
            DB::raw('MAX(percentage_score) as max_score'),
            DB::raw('SUM(CASE WHEN percentage_score >= (SELECT passing_score FROM quality_control_checklists WHERE id = quality_audits.checklist_id) THEN 1 ELSE 0 END) as passed_audits')
        )
        ->whereBetween('audit_date', [$startDate, $endDate])
        ->groupBy('checklist_id')
        ->with('checklist')
        ->get();
        
        // Technician statistics
        $technicianStats = QualityAudit::select(
            'technician_id',
            DB::raw('COUNT(*) as total_audits'),
            DB::raw('AVG(percentage_score) as avg_score'),
            DB::raw('SUM(CASE WHEN percentage_score >= (SELECT passing_score FROM quality_control_checklists WHERE id = quality_audits.checklist_id) THEN 1 ELSE 0 END) as passed_audits')
        )
        ->whereBetween('audit_date', [$startDate, $endDate])
        ->whereNotNull('technician_id')
        ->groupBy('technician_id')
        ->with('technician')
        ->get();
        
        // Auditor statistics
        $auditorStats = QualityAudit::select(
            'auditor_id',
            DB::raw('COUNT(*) as total_audits'),
            DB::raw('AVG(percentage_score) as avg_score'),
            DB::raw('SUM(CASE WHEN percentage_score >= (SELECT passing_score FROM quality_control_checklists WHERE id = quality_audits.checklist_id) THEN 1 ELSE 0 END) as passed_audits')
        )
        ->whereBetween('audit_date', [$startDate, $endDate])
        ->groupBy('auditor_id')
        ->with('auditor')
        ->get();
        
        return response()->json([
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'daily_stats' => $dailyStats,
            'checklist_stats' => $checklistStats,
            'technician_stats' => $technicianStats,
            'auditor_stats' => $auditorStats,
            'summary' => [
                'total_audits' => $dailyStats->sum('total_audits'),
                'avg_score' => round($dailyStats->avg('avg_score') ?? 0, 2),
                'pass_rate' => $dailyStats->sum('total_audits') > 0 
                    ? round(($dailyStats->sum('passed_audits') / $dailyStats->sum('total_audits')) * 100, 2)
                    : 0
            ]
        ]);
    }
}