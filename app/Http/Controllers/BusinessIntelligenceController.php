<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessIntelligenceMetric;
use App\Models\DashboardWidget;
use App\Models\RetentionAnalytics;
use App\Models\Appointment;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Technician;
use Carbon\Carbon;

class BusinessIntelligenceController extends Controller
{
    /**
     * Display the main BI dashboard.
     */
    public function dashboard()
    {
        // Get key metrics for the dashboard
        $metrics = $this->getKeyMetrics();
        $recentTrends = $this->getRecentTrends();
        $retentionStats = RetentionAnalytics::getRetentionStats();
        $technicianPerformance = $this->getTechnicianPerformance();
        $revenueTrend = $this->getRevenueTrend();
        
        // Get user's dashboard widgets
        $userId = auth()->id();
        $widgets = DashboardWidget::getUserWidgets($userId);
        
        return view('business-intelligence.dashboard', compact(
            'metrics', 
            'recentTrends', 
            'retentionStats', 
            'technicianPerformance',
            'revenueTrend',
            'widgets'
        ));
    }

    /**
     * Display detailed metrics view.
     */
    public function metrics(Request $request)
    {
        $category = $request->get('category', 'revenue');
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        
        $metrics = BusinessIntelligenceMetric::byCategory($category)
            ->byType($period)
            ->byDateRange($startDate, $endDate)
            ->orderBy('metric_date')
            ->get();
            
        $categories = ['revenue', 'appointments', 'jobs', 'customers', 'technicians'];
        $periods = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
        
        return view('business-intelligence.metrics', compact(
            'metrics', 
            'category', 
            'period', 
            'startDate', 
            'endDate',
            'categories',
            'periods'
        ));
    }

    /**
     * Display technician performance analytics.
     */
    public function technicianPerformance()
    {
        $performanceData = $this->getTechnicianPerformance();
        $timePeriod = request()->get('period', 'monthly');
        $startDate = request()->get('start_date', Carbon::now()->subMonth()->toDateString());
        $endDate = request()->get('end_date', Carbon::now()->toDateString());
        
        // Get technician details
        $technicians = Technician::with(['timeLogs' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate]);
        }])->get();
        
        return view('business-intelligence.technician-performance', compact(
            'performanceData',
            'technicians',
            'timePeriod',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display customer retention analytics.
     */
    public function customerRetention()
    {
        $retentionStats = RetentionAnalytics::getRetentionStats();
        $customersNeedingAttention = RetentionAnalytics::getCustomersNeedingAttention(20);
        $retentionTrend = $this->getRetentionTrend();
        
        return view('business-intelligence.customer-retention', compact(
            'retentionStats',
            'customersNeedingAttention',
            'retentionTrend'
        ));
    }

    /**
     * Calculate and display metric details.
     */
    public function calculateMetric(Request $request)
    {
        $metricName = $request->get('metric_name');
        $periodType = $request->get('period_type', 'daily');
        $date = $request->get('date', Carbon::now()->toDateString());
        
        $value = $this->calculateMetricValue($metricName, $periodType, $date);
        $breakdown = $this->getMetricBreakdown($metricName, $periodType, $date);
        
        return response()->json([
            'metric_name' => $metricName,
            'period_type' => $periodType,
            'date' => $date,
            'value' => $value,
            'breakdown' => $breakdown,
            'calculated_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get metric trend data.
     */
    public function getMetricTrend(Request $request)
    {
        $metricName = $request->get('metric_name');
        $periodType = $request->get('period_type', 'daily');
        $periodsBack = $request->get('periods_back', 7);
        
        $trend = BusinessIntelligenceMetric::getMetricTrend($metricName, $periodType, $periodsBack);
        
        return response()->json([
            'metric_name' => $metricName,
            'period_type' => $periodType,
            'trend' => $trend,
        ]);
    }

    /**
     * Get key metrics for the dashboard.
     */
    private function getKeyMetrics()
    {
        $today = Carbon::now()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        
        return [
            'daily_revenue' => [
                'value' => BusinessIntelligenceMetric::getLatestValue('daily_revenue', 'daily') ?? 0,
                'trend' => BusinessIntelligenceMetric::getMetricTrend('daily_revenue', 'daily', 1),
                'icon' => 'currency-dollar',
                'color' => 'success',
            ],
            'appointments_today' => [
                'value' => Appointment::whereDate('appointment_date', $today)->count(),
                'trend' => $this->calculateAppointmentTrend(),
                'icon' => 'calendar',
                'color' => 'primary',
            ],
            'active_jobs' => [
                'value' => WorkOrder::whereIn('status', ['in_progress', 'scheduled'])->count(),
                'trend' => $this->calculateJobTrend(),
                'icon' => 'wrench',
                'color' => 'warning',
            ],
            'customer_satisfaction' => [
                'value' => $this->calculateCustomerSatisfaction(),
                'trend' => $this->calculateSatisfactionTrend(),
                'icon' => 'emoji-happy',
                'color' => 'info',
            ],
            'technician_productivity' => [
                'value' => $this->calculateTechnicianProductivity(),
                'trend' => $this->calculateProductivityTrend(),
                'icon' => 'users',
                'color' => 'purple',
            ],
            'retention_rate' => [
                'value' => $this->calculateRetentionRate(),
                'trend' => $this->calculateRetentionTrend(),
                'icon' => 'refresh',
                'color' => 'pink',
            ],
        ];
    }

    /**
     * Get recent trends for key metrics.
     */
    private function getRecentTrends()
    {
        $trends = [];
        $metrics = ['daily_revenue', 'appointments', 'completed_jobs', 'new_customers'];
        
        foreach ($metrics as $metric) {
            $trends[$metric] = BusinessIntelligenceMetric::getMetricTrend($metric, 'daily', 7);
        }
        
        return $trends;
    }

    /**
     * Get technician performance data.
     */
    private function getTechnicianPerformance()
    {
        $startDate = Carbon::now()->subMonth()->toDateString();
        $endDate = Carbon::now()->toDateString();
        
        $technicians = Technician::with(['timeLogs' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate])
                  ->whereNotNull('end_time');
        }])->get();
        
        $performance = [];
        foreach ($technicians as $tech) {
            $totalHours = $tech->timeLogs->sum(function($log) {
                return $log->start_time->diffInHours($log->end_time);
            });
            
            $completedJobs = $tech->workOrders()->whereBetween('created_at', [$startDate, $endDate])->count();
            
            $performance[] = [
                'id' => $tech->id,
                'name' => $tech->name,
                'total_hours' => round($totalHours, 2),
                'completed_jobs' => $completedJobs,
                'efficiency' => $totalHours > 0 ? round($completedJobs / $totalHours, 2) : 0,
                'rating' => $tech->average_rating ?? 0,
            ];
        }
        
        // Sort by efficiency
        usort($performance, function($a, $b) {
            return $b['efficiency'] <=> $a['efficiency'];
        });
        
        return $performance;
    }

    /**
     * Get revenue trend data.
     */
    private function getRevenueTrend()
    {
        $startDate = Carbon::now()->subDays(30)->toDateString();
        $endDate = Carbon::now()->toDateString();
        
        $revenueData = BusinessIntelligenceMetric::where('metric_name', 'daily_revenue')
            ->where('metric_type', 'daily')
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->orderBy('metric_date')
            ->get();
        
        $labels = [];
        $data = [];
        
        foreach ($revenueData as $metric) {
            $labels[] = $metric->metric_date->format('M d');
            $data[] = $metric->metric_value;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'average' => count($data) > 0 ? array_sum($data) / count($data) : 0,
        ];
    }

    /**
     * Get retention trend data.
     */
    private function getRetentionTrend()
    {
        $startDate = Carbon::now()->subMonths(3)->toDateString();
        $endDate = Carbon::now()->toDateString();
        
        // This would typically come from calculated metrics
        // For now, return sample data
        return [
            'labels' => ['Jan', 'Feb', 'Mar'],
            'active' => [85, 82, 78],
            'at_risk' => [8, 10, 12],
            'lapsed' => [5, 6, 8],
            'lost' => [2, 2, 2],
        ];
    }

    /**
     * Calculate metric value.
     */
    private function calculateMetricValue($metricName, $periodType, $date)
    {
        // This would contain the actual calculation logic for each metric
        // For now, return a placeholder
        switch ($metricName) {
            case 'daily_revenue':
                return Invoice::whereDate('created_at', $date)->sum('total_amount');
            case 'appointments':
                return Appointment::whereDate('appointment_date', $date)->count();
            case 'completed_jobs':
                return WorkOrder::whereDate('completed_at', $date)->count();
            default:
                return 0;
        }
    }

    /**
     * Get metric breakdown.
     */
    private function getMetricBreakdown($metricName, $periodType, $date)
    {
        // This would contain the breakdown logic for each metric
        // For now, return a placeholder
        return [];
    }

    /**
     * Calculate appointment trend.
     */
    private function calculateAppointmentTrend()
    {
        $today = Carbon::now()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        
        $todayCount = Appointment::whereDate('appointment_date', $today)->count();
        $yesterdayCount = Appointment::whereDate('appointment_date', $yesterday)->count();
        
        return $this->calculateTrendPercentage($todayCount, $yesterdayCount);
    }

    /**
     * Calculate job trend.
     */
    private function calculateJobTrend()
    {
        $today = Carbon::now()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        
        $todayCount = WorkOrder::whereDate('created_at', $today)->count();
        $yesterdayCount = WorkOrder::whereDate('created_at', $yesterday)->count();
        
        return $this->calculateTrendPercentage($todayCount, $yesterdayCount);
    }

    /**
     * Calculate customer satisfaction.
     */
    private function calculateCustomerSatisfaction()
    {
        // This would come from customer satisfaction surveys
        // For now, return a placeholder
        return 4.2; // out of 5
    }

    /**
     * Calculate satisfaction trend.
     */
    private function calculateSatisfactionTrend()
    {
        // This would calculate trend from historical data
        // For now, return a placeholder
        return [
            'current' => 4.2,
            'previous' => 4.1,
            'change' => 0.1,
            'percentage' => 2.44,
            'direction' => 'up',
        ];
    }

    /**
     * Calculate technician productivity.
     */
    private function calculateTechnicianProductivity()
    {
        $performance = $this->getTechnicianPerformance();
        if (empty($performance)) {
            return 0;
        }
        
        $totalEfficiency = array_sum(array_column($performance, 'efficiency'));
        return round($totalEfficiency / count($performance), 2);
    }

    /**
     * Calculate productivity trend.
     */
    private function calculateProductivityTrend()
    {
        // This would calculate trend from historical data
        // For now, return a placeholder
        return [
            'current' => 1.8,
            'previous' => 1.7,
            'change' => 0.1,
            'percentage' => 5.88,
            'direction' => 'up',
        ];
    }

    /**
     * Calculate retention rate.
     */
    private function calculateRetentionRate()
    {
        $stats = RetentionAnalytics::getRetentionStats();
        $active = $stats['active']->count ?? 0;
        $total = $stats['total_customers'] ?? 1;
        
        return round(($active / $total) * 100, 2);
    }

    /**
     * Calculate trend percentage.
     */
    private function calculateTrendPercentage($current, $previous)
    {
        if ($previous == 0) {
            return [
                'current' => $current,
                'previous' => $previous,
                'change' => $current,
                'percentage' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'neutral',
            ];
        }
        
        $change = $current - $previous;
        $percentage = ($change / abs($previous)) * 100;
        
        return [
            'current' => $current,
            'previous' => $previous,
            'change' => $change,
            'percentage' => round($percentage, 2),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }
}
