<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMetric;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    /**
     * Display a listing of performance metrics.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = PerformanceMetric::with(['technician', 'workOrder', 'appointment']);

        // Filter by technician
        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        // Filter by metric type
        if ($request->has('metric_type')) {
            $query->where('metric_type', $request->metric_type);
        }

        // Filter by period
        if ($request->has('period')) {
            $query->where('period', $request->period);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('metric_date', [$request->start_date, $request->end_date]);
        }

        // Filter by work order
        if ($request->has('work_order_id')) {
            $query->where('work_order_id', $request->work_order_id);
        }

        $perPage = $request->get('per_page', 20);
        $metrics = $query->orderBy('metric_date', 'desc')->paginate($perPage);

        return view('performance.index', compact('metrics'));
    }

    /**
     * Show the form for creating a new performance metric.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $workOrders = WorkOrder::where('status', '!=', 'completed')->get();
        $appointments = Appointment::where('status', '!=', 'completed')->get();
        $metricTypes = PerformanceMetric::getMetricTypes();
        $periods = PerformanceMetric::getPeriods();

        return view('performance.create', compact('technicians', 'workOrders', 'appointments', 'metricTypes', 'periods'));
    }

    /**
     * Store a newly created performance metric.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'metric_date' => 'required|date',
            'metric_type' => 'required|in:' . implode(',', array_keys(PerformanceMetric::getMetricTypes())),
            'metric_value' => 'required|numeric|min:0',
            'target_value' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0.1|max:5',
            'period' => 'required|in:' . implode(',', array_keys(PerformanceMetric::getPeriods())),
            'work_order_id' => 'nullable|exists:work_orders,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Calculate score if target is provided
        if (!empty($validated['target_value'])) {
            $metric = new PerformanceMetric($validated);
            $validated['score'] = $metric->calculateScore();
        }

        $performanceMetric = PerformanceMetric::create($validated);

        return redirect()->route('performance.show', $performanceMetric->id)
            ->with('success', 'Performance metric created successfully.');
    }

    /**
     * Display the specified performance metric.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $metric = PerformanceMetric::with(['technician', 'workOrder', 'appointment'])->findOrFail($id);
        
        return view('performance.show', compact('metric'));
    }

    /**
     * Show the form for editing the specified performance metric.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $metric = PerformanceMetric::findOrFail($id);
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $workOrders = WorkOrder::all();
        $appointments = Appointment::all();
        $metricTypes = PerformanceMetric::getMetricTypes();
        $periods = PerformanceMetric::getPeriods();

        return view('performance.edit', compact('metric', 'technicians', 'workOrders', 'appointments', 'metricTypes', 'periods'));
    }

    /**
     * Update the specified performance metric.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $metric = PerformanceMetric::findOrFail($id);

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'metric_date' => 'required|date',
            'metric_type' => 'required|in:' . implode(',', array_keys(PerformanceMetric::getMetricTypes())),
            'metric_value' => 'required|numeric|min:0',
            'target_value' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0.1|max:5',
            'period' => 'required|in:' . implode(',', array_keys(PerformanceMetric::getPeriods())),
            'work_order_id' => 'nullable|exists:work_orders,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Recalculate score if target or value changed
        if (!empty($validated['target_value']) && 
            ($validated['target_value'] != $metric->target_value || $validated['metric_value'] != $metric->metric_value)) {
            $tempMetric = new PerformanceMetric($validated);
            $validated['score'] = $tempMetric->calculateScore();
            $validated['calculated_at'] = Carbon::now();
        }

        $metric->update($validated);

        return redirect()->route('performance.show', $metric->id)
            ->with('success', 'Performance metric updated successfully.');
    }

    /**
     * Remove the specified performance metric.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $metric = PerformanceMetric::findOrFail($id);
        $metric->delete();

        return redirect()->route('performance.index')
            ->with('success', 'Performance metric deleted successfully.');
    }

    /**
     * Get technician performance dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Request $request)
    {
        $technicianId = $request->get('technician_id');
        $period = $request->get('period', 'monthly');

        if ($technicianId) {
            $technician = User::findOrFail($technicianId);
            $performanceReport = PerformanceMetric::generatePerformanceReport($technicianId, $period);
            
            // Get recent metrics
            $recentMetrics = PerformanceMetric::forTechnician($technicianId)
                ->latestMetrics(10)
                ->get();

            return view('performance.dashboard', compact('technician', 'performanceReport', 'recentMetrics', 'period'));
        }

        // Get all technicians with their latest composite scores
        $technicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->withCount(['performanceMetrics as latest_score' => function($query) {
                $query->select(DB::raw('COALESCE(AVG(score), 0)'))
                    ->where('metric_date', '>=', Carbon::now()->subMonth());
            }])
            ->orderBy('latest_score', 'desc')
            ->get();

        // Get overall statistics
        $overallStats = $this->getOverallStatistics();

        return view('performance.dashboard-overview', compact('technicians', 'overallStats'));
    }

    /**
     * Get overall performance statistics.
     *
     * @return array
     */
    private function getOverallStatistics(): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $stats = PerformanceMetric::where('metric_date', '>=', $thirtyDaysAgo)
            ->select([
                DB::raw('COUNT(*) as total_metrics'),
                DB::raw('AVG(score) as average_score'),
                DB::raw('COUNT(DISTINCT technician_id) as technicians_tracked'),
                DB::raw('SUM(CASE WHEN score >= 80 THEN 1 ELSE 0 END) as good_performers'),
                DB::raw('SUM(CASE WHEN score < 70 THEN 1 ELSE 0 END) as needs_improvement'),
            ])
            ->first();

        $metricTypeDistribution = PerformanceMetric::where('metric_date', '>=', $thirtyDaysAgo)
            ->select('metric_type', DB::raw('COUNT(*) as count'))
            ->groupBy('metric_type')
            ->get()
            ->pluck('count', 'metric_type');

        return [
            'total_metrics' => $stats->total_metrics ?? 0,
            'average_score' => round($stats->average_score ?? 0, 2),
            'technicians_tracked' => $stats->technicians_tracked ?? 0,
            'good_performers' => $stats->good_performers ?? 0,
            'needs_improvement' => $stats->needs_improvement ?? 0,
            'metric_type_distribution' => $metricTypeDistribution,
        ];
    }

    /**
     * Get performance analytics.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function analytics(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $metricType = $request->get('metric_type');

        $query = PerformanceMetric::whereBetween('metric_date', [$startDate, $endDate]);

        if ($metricType) {
            $query->where('metric_type', $metricType);
        }

        // Get daily averages
        $dailyAverages = $query->select([
                DB::raw('DATE(metric_date) as date'),
                DB::raw('AVG(score) as average_score'),
                DB::raw('COUNT(*) as metric_count'),
            ])
            ->groupBy(DB::raw('DATE(metric_date)'))
            ->orderBy('date')
            ->get();

        // Get technician rankings
        $technicianRankings = $query->select([
                'technician_id',
                DB::raw('AVG(score) as average_score'),
                DB::raw('COUNT(*) as metric_count'),
            ])
            ->groupBy('technician_id')
            ->with('technician')
            ->orderBy('average_score', 'desc')
            ->get();

        // Get metric type distribution
        $metricTypeDistribution = $query->select([
                'metric_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(score) as average_score'),
            ])
            ->groupBy('metric_type')
            ->get();

        return view('performance.analytics', compact(
            'dailyAverages',
            'technicianRankings',
            'metricTypeDistribution',
            'startDate',
            'endDate',
            'metricType'
        ));
    }

    /**
     * Generate performance report for a technician.
     *
     * @param Request $request
     * @param int $technicianId
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request, $technicianId)
    {
        $period = $request->get('period', 'monthly');
        
        $report = PerformanceMetric::generatePerformanceReport($technicianId, $period);
        $technician = User::findOrFail($technicianId);

        return view('performance.report', compact('report', 'technician', 'period'));
    }

    /**
     * Export performance data.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $query = PerformanceMetric::with(['technician', 'workOrder', 'appointment']);

        // Apply filters
        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('metric_date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('metric_type')) {
            $query->where('metric_type', $request->metric_type);
        }

        $metrics = $query->orderBy('metric_date', 'desc')->get();

        $filename = 'performance-metrics-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($metrics) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID',
                'Technician',
                'Date',
                'Metric Type',
                'Metric Value',
                'Target Value',
                'Score',
                'Weight',
                'Period',
                'Work Order',
                'Appointment',
                'Notes',
                'Calculated At',
                'Created At',
            ]);

            // Data
            foreach ($metrics as $metric) {
                fputcsv($file, [
                    $metric->id,
                    $metric->technician->name ?? 'N/A',
                    $metric->metric_date,
                    $metric->metric_type,
                    $metric->metric_value,
                    $metric->target_value,
                    $metric->score,
                    $metric->weight,
                    $metric->period,
                    $metric->workOrder->work_order_number ?? 'N/A',
                    $metric->appointment->appointment_number ?? 'N/A',
                    $metric->notes,
                    $metric->calculated_at,
                    $metric->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculate and store automated performance metrics.
     *
     * @return \Illuminate\Http\Response
     */
    public function calculateAutomatedMetrics()
    {
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $today = Carbon::today();

        $results = [
            'total_calculated' => 0,
            'technicians_processed' => 0,
            'errors' => [],
        ];

        foreach ($technicians as $technician) {
            try {
                // Calculate efficiency metric (based on work orders completed vs estimated time)
                $efficiencyScore = $this->calculateEfficiencyMetric($technician, $today);
                
                if ($efficiencyScore !== null) {
                    PerformanceMetric::create([
                        'technician_id' => $technician->id,
                        'metric_date' => $today,
                        'metric_type' => PerformanceMetric::TYPE_EFFICIENCY,
                        'metric_value' => $efficiencyScore,
                        'target_value' => 85, // Target 85% efficiency
                        'period' => PerformanceMetric::PERIOD_DAILY,
                        'calculated_at' => Carbon::now(),
                    ]);
                    $results['total_calculated']++;
                }

                // Calculate quality metric (based on rework rate)
                $qualityScore = $this->calculateQualityMetric($technician, $today);
                
                if ($qualityScore !== null) {
                    PerformanceMetric::create([
                        'technician_id' => $technician->id,
                        'metric_date' => $today,
                        'metric_type' => PerformanceMetric::TYPE_QUALITY,
                        'metric_value' => $qualityScore,
                        'target_value' => 95, // Target 95% quality
                        'period' => PerformanceMetric::PERIOD_DAILY,
                        'calculated_at' => Carbon::now(),
                    ]);
                    $results['total_calculated']++;
                }

                $results['technicians_processed']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Technician {$technician->id}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Automated metrics calculated successfully.',
            'results' => $results,
        ]);
    }

    /**
     * Calculate efficiency metric for a technician.
     *
     * @param User $technician
     * @param Carbon $date
     * @return float|null
     */
    private function calculateEfficiencyMetric(User $technician, Carbon $date): ?float
    {
        $workOrders = WorkOrder::where('technician_id', $technician->id)
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->get();

        if ($workOrders->isEmpty()) {
            return null;
        }

        $totalEstimatedHours = 0;
        $totalActualHours = 0;

        foreach ($workOrders as $workOrder) {
            $totalEstimatedHours += $workOrder->estimated_labor_hours ?? 0;
            $totalActualHours += $workOrder->actual_labor_hours ?? 0;
        }

        if ($totalEstimatedHours == 0) {
            return 100; // No estimated time, assume 100% efficiency
        }

        if ($totalActualHours == 0) {
            return 0; // No actual time recorded
        }

        // Efficiency = (Estimated Time / Actual Time) * 100
        $efficiency = ($totalEstimatedHours / $totalActualHours) * 100;
        
        // Cap at 150% to prevent unrealistic scores
        return min($efficiency, 150);
    }

    /**
     * Calculate quality metric for a technician.
     *
     * @param User $technician
     * @param Carbon $date
     * @return float|null
     */
    private function calculateQualityMetric(User $technician, Carbon $date): ?float
    {
        $workOrders = WorkOrder::where('technician_id', $technician->id)
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->get();

        if ($workOrders->isEmpty()) {
            return null;
        }

        $totalWorkOrders = $workOrders->count();
        $reworkWorkOrders = $workOrders->where('has_rework', true)->count();

        if ($totalWorkOrders == 0) {
            return 100; // No work orders, assume perfect quality
        }

        // Quality = 100 - (Rework Rate * 100)
        $reworkRate = ($reworkWorkOrders / $totalWorkOrders) * 100;
        $qualityScore = 100 - $reworkRate;

        return max($qualityScore, 0); // Ensure non-negative
    }

    /**
     * Get performance trends over time.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function trends(Request $request)
    {
        $technicianId = $request->get('technician_id');
        $metricType = $request->get('metric_type', 'efficiency');
        $days = $request->get('days', 30);

        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        $query = PerformanceMetric::whereBetween('metric_date', [$startDate, $endDate])
            ->where('metric_type', $metricType);

        if ($technicianId) {
            $query->where('technician_id', $technicianId);
        }

        $trends = $query->select([
                DB::raw('DATE(metric_date) as date'),
                DB::raw('AVG(score) as average_score'),
                DB::raw('COUNT(*) as data_points'),
            ])
            ->groupBy(DB::raw('DATE(metric_date)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'trends' => $trends,
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'metric_type' => $metricType,
                'technician_id' => $technicianId,
            ],
        ]);
    }
}