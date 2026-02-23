<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessIntelligenceMetric;
use App\Models\RetentionAnalytics;
use App\Models\Appointment;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Technician;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Generate and store daily metrics.
     */
    public function generateDailyMetrics()
    {
        $date = Carbon::now()->toDateString();
        
        // Calculate and store daily revenue
        $dailyRevenue = Invoice::whereDate('created_at', $date)->sum('total_amount');
        BusinessIntelligenceMetric::calculateMetric('daily_revenue', 'daily', $date, $dailyRevenue, [], 'revenue');
        
        // Calculate and store daily appointments
        $dailyAppointments = Appointment::whereDate('appointment_date', $date)->count();
        BusinessIntelligenceMetric::calculateMetric('appointments', 'daily', $date, $dailyAppointments, [], 'appointments');
        
        // Calculate and store daily completed jobs
        $completedJobs = WorkOrder::whereDate('completed_at', $date)->count();
        BusinessIntelligenceMetric::calculateMetric('completed_jobs', 'daily', $date, $completedJobs, [], 'jobs');
        
        // Calculate and store new customers
        $newCustomers = Customer::whereDate('created_at', $date)->count();
        BusinessIntelligenceMetric::calculateMetric('new_customers', 'daily', $date, $newCustomers, [], 'customers');
        
        // Calculate and store technician productivity
        $productivity = $this->calculateDailyProductivity($date);
        BusinessIntelligenceMetric::calculateMetric('technician_productivity', 'daily', $date, $productivity, [], 'technicians');
        
        return response()->json([
            'message' => 'Daily metrics generated successfully',
            'date' => $date,
            'metrics' => [
                'daily_revenue' => $dailyRevenue,
                'appointments' => $dailyAppointments,
                'completed_jobs' => $completedJobs,
                'new_customers' => $newCustomers,
                'technician_productivity' => $productivity,
            ],
        ]);
    }

    /**
     * Generate weekly metrics aggregation.
     */
    public function generateWeeklyMetrics()
    {
        $startDate = Carbon::now()->startOfWeek()->toDateString();
        $endDate = Carbon::now()->endOfWeek()->toDateString();
        
        // Aggregate daily metrics for the week
        $weeklyRevenue = BusinessIntelligenceMetric::where('metric_name', 'daily_revenue')
            ->where('metric_type', 'daily')
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->sum('metric_value');
        
        BusinessIntelligenceMetric::calculateMetric('weekly_revenue', 'weekly', $endDate, $weeklyRevenue, [], 'revenue');
        
        // Calculate average appointments per day
        $avgAppointments = BusinessIntelligenceMetric::where('metric_name', 'appointments')
            ->where('metric_type', 'daily')
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->avg('metric_value');
        
        BusinessIntelligenceMetric::calculateMetric('avg_daily_appointments', 'weekly', $endDate, $avgAppointments, [], 'appointments');
        
        return response()->json([
            'message' => 'Weekly metrics generated successfully',
            'week' => $startDate . ' to ' . $endDate,
            'metrics' => [
                'weekly_revenue' => $weeklyRevenue,
                'avg_daily_appointments' => $avgAppointments,
            ],
        ]);
    }

    /**
     * Generate monthly metrics aggregation.
     */
    public function generateMonthlyMetrics()
    {
        $month = Carbon::now()->format('Y-m');
        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();
        
        // Aggregate daily metrics for the month
        $monthlyRevenue = BusinessIntelligenceMetric::where('metric_name', 'daily_revenue')
            ->where('metric_type', 'daily')
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->sum('metric_value');
        
        BusinessIntelligenceMetric::calculateMetric('monthly_revenue', 'monthly', $endDate, $monthlyRevenue, [], 'revenue');
        
        // Calculate customer retention rate for the month
        $retentionRate = $this->calculateMonthlyRetentionRate($month);
        BusinessIntelligenceMetric::calculateMetric('retention_rate', 'monthly', $endDate, $retentionRate, [], 'customers');
        
        return response()->json([
            'message' => 'Monthly metrics generated successfully',
            'month' => $month,
            'metrics' => [
                'monthly_revenue' => $monthlyRevenue,
                'retention_rate' => $retentionRate,
            ],
        ]);
    }

    /**
     * Update retention analytics for all customers.
     */
    public function updateRetentionAnalytics()
    {
        $customers = Customer::all();
        $updatedCount = 0;
        
        foreach ($customers as $customer) {
            try {
                RetentionAnalytics::updateCustomerRetention($customer->id);
                $updatedCount++;
            } catch (\Exception $e) {
                // Log error but continue with other customers
                \Log::error("Failed to update retention analytics for customer {$customer->id}: " . $e->getMessage());
            }
        }
        
        return response()->json([
            'message' => 'Retention analytics updated successfully',
            'customers_updated' => $updatedCount,
            'total_customers' => $customers->count(),
        ]);
    }

    /**
     * Get detailed analytics report.
     */
    public function getAnalyticsReport(Request $request)
    {
        $reportType = $request->get('type', 'overview');
        $startDate = $request->get('start_date', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        
        switch ($reportType) {
            case 'revenue':
                return $this->getRevenueReport($startDate, $endDate);
            case 'appointments':
                return $this->getAppointmentsReport($startDate, $endDate);
            case 'technicians':
                return $this->getTechniciansReport($startDate, $endDate);
            case 'customers':
                return $this->getCustomersReport($startDate, $endDate);
            case 'overview':
            default:
                return $this->getOverviewReport($startDate, $endDate);
        }
    }

    /**
     * Get revenue analytics report.
     */
    private function getRevenueReport($startDate, $endDate)
    {
        $revenueData = BusinessIntelligenceMetric::where('metric_name', 'daily_revenue')
            ->where('metric_type', 'daily')
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->orderBy('metric_date')
            ->get();
        
        $invoices = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->with('items')
            ->get();
        
        $totalRevenue = $revenueData->sum('metric_value');
        $averageDailyRevenue = $revenueData->avg('metric_value');
        
        // Revenue by service type
        $revenueByService = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereBetween('invoices.created_at', [$startDate, $endDate])
            ->select('invoice_items.service_type', DB::raw('SUM(invoice_items.total) as total'))
            ->groupBy('invoice_items.service_type')
            ->get();
        
        return [
            'report_type' => 'revenue',
            'period' => $startDate . ' to ' . $endDate,
            'total_revenue' => $totalRevenue,
            'average_daily_revenue' => $averageDailyRevenue,
            'invoice_count' => $invoices->count(),
            'revenue_by_service' => $revenueByService,
            'daily_data' => $revenueData->map(function($metric) {
                return [
                    'date' => $metric->metric_date->format('Y-m-d'),
                    'revenue' => $metric->metric_value,
                ];
            }),
        ];
    }

    /**
     * Get appointments analytics report.
     */
    private function getAppointmentsReport($startDate, $endDate)
    {
        $appointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->with(['customer', 'vehicle'])
            ->get();
        
        $appointmentData = BusinessIntelligenceMetric::where('metric_name', 'appointments')
            ->where('metric_type', 'daily')
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->orderBy('metric_date')
            ->get();
        
        $totalAppointments = $appointments->count();
        $completedAppointments = $appointments->where('status', 'completed')->count();
        $cancelledAppointments = $appointments->where('status', 'cancelled')->count();
        $noShowAppointments = $appointments->where('status', 'no_show')->count();
        
        // Appointments by day of week
        $appointmentsByDay = $appointments->groupBy(function($appointment) {
            return $appointment->appointment_date->format('l');
        })->map->count();
        
        return [
            'report_type' => 'appointments',
            'period' => $startDate . ' to ' . $endDate,
            'total_appointments' => $totalAppointments,
            'completed_appointments' => $completedAppointments,
            'cancelled_appointments' => $cancelledAppointments,
            'no_show_appointments' => $noShowAppointments,
            'completion_rate' => $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100, 2) : 0,
            'appointments_by_day' => $appointmentsByDay,
            'daily_data' => $appointmentData->map(function($metric) {
                return [
                    'date' => $metric->metric_date->format('Y-m-d'),
                    'appointments' => $metric->metric_value,
                ];
            }),
        ];
    }

    /**
     * Get technicians analytics report.
     */
    private function getTechniciansReport($startDate, $endDate)
    {
        $technicians = Technician::with(['timeLogs' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate])
                  ->whereNotNull('end_time');
        }, 'workOrders' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();
        
        $performanceData = [];
        foreach ($technicians as $tech) {
            $totalHours = $tech->timeLogs->sum(function($log) {
                return $log->start_time->diffInHours($log->end_time);
            });
            
            $completedJobs = $tech->workOrders->where('status', 'completed')->count();
            $totalJobs = $tech->workOrders->count();
            
            $performanceData[] = [
                'id' => $tech->id,
                'name' => $tech->name,
                'total_hours' => round($totalHours, 2),
                'completed_jobs' => $completedJobs,
                'total_jobs' => $totalJobs,
                'completion_rate' => $totalJobs > 0 ? round(($completedJobs / $totalJobs) * 100, 2) : 0,
                'efficiency' => $totalHours > 0 ? round($completedJobs / $totalHours, 2) : 0,
                'average_rating' => $tech->average_rating ?? 0,
            ];
        }
        
        // Sort by efficiency
        usort($performanceData, function($a, $b) {
            return $b['efficiency'] <=> $a['efficiency'];
        });
        
        return [
            'report_type' => 'technicians',
            'period' => $startDate . ' to ' . $endDate,
            'total_technicians' => $technicians->count(),
            'performance_data' => $performanceData,
            'average_efficiency' => count($performanceData) > 0 ? 
                round(array_sum(array_column($performanceData, 'efficiency')) / count($performanceData), 2) : 0,
            'average_completion_rate' => count($performanceData) > 0 ? 
                round(array_sum(array_column($performanceData, 'completion_rate')) / count($performanceData), 2) : 0,
        ];
    }

    /**
     * Get customers analytics report.
     */
    private function getCustomersReport($startDate, $endDate)
    {
        $customers = Customer::whereBetween('created_at', [$startDate, $endDate])->get();
        $retentionStats = RetentionAnalytics::getRetentionStats();
        
        $newCustomers = $customers->count();
        $repeatCustomers = Customer::whereHas('serviceRecords', function($query) use ($startDate, $endDate) {
            $query->whereBetween('service_date', [$startDate, $endDate]);
        })->count();
        
        // Customer value analysis
        $customerValue = DB::table('customers')
            ->join('service_records', 'customers.id', '=', 'service_records.customer_id')
            ->whereBetween('service_records.service_date', [$startDate, $endDate])
            ->select('customers.id', 'customers.name', DB::raw('SUM(service_records.total_cost) as total_spent'))
            ->groupBy('customers.id', 'customers.name')
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get();
        
        return [
            'report_type' => 'customers',
            'period' => $startDate . ' to ' . $endDate,
            'new_customers' => $newCustomers,
            'repeat_customers' => $repeatCustomers,
            'retention_stats' => $retentionStats,
            'top_customers_by_value' => $customerValue,
            'repeat_rate' => $newCustomers > 0 ? round(($repeatCustomers / $newCustomers) * 100, 2) : 0,
        ];
    }

    /**
     * Get overview analytics report.
     */
    private function getOverviewReport($startDate, $endDate)
    {
        $revenueReport = $this->getRevenueReport($startDate, $endDate);
        $appointmentsReport = $this->getAppointmentsReport($startDate, $endDate);
        $techniciansReport = $this->getTechniciansReport($startDate, $endDate);
        $customersReport = $this->getCustomersReport($startDate, $endDate);
        
        return [
            'report_type' => 'overview',
            'period' => $startDate . ' to ' . $endDate,
            'revenue' => [
                'total' => $revenueReport['total_revenue'],
                'average_daily' => $revenueReport['average_daily_revenue'],
            ],
            'appointments' => [
                'total' => $appointmentsReport['total_appointments'],
                'completion_rate' => $appointmentsReport['completion_rate'],
            ],
            'technicians' => [
                'average_efficiency' => $techniciansReport['average_efficiency'],
                'average_completion_rate' => $techniciansReport['average_completion_rate'],
            ],
            'customers' => [
                'new' => $customersReport['new_customers'],
                'repeat_rate' => $customersReport['repeat_rate'],
                'retention_stats' => $customersReport['retention_stats'],
            ],
            'summary' => $this->generateSummary($revenueReport, $appointmentsReport, $techniciansReport, $customersReport),
        ];
    }

    /**
     * Generate summary insights.
     */
    private function generateSummary($revenueReport, $appointmentsReport, $techniciansReport, $customersReport)
    {
        $insights = [];
        
        // Revenue insight
        $revenueTrend = BusinessIntelligenceMetric::getMetricTrend('daily_revenue', 'daily', 7);
        if ($revenueTrend && $revenueTrend['direction'] == 'up') {
            $insights[] = "Revenue is trending upward by {$revenueTrend['percentage']}% compared to last week.";
        } elseif ($revenueTrend && $revenueTrend['direction'] == 'down') {
            $insights[] = "Revenue is trending downward by {$revenueTrend['percentage']}% compared to last week.";
        }
        
        // Appointments insight
        if ($appointmentsReport['completion_rate'] > 90) {
            $insights[] = "Excellent appointment completion rate of {$appointmentsReport['completion_rate']}%.";
        } elseif ($appointmentsReport['completion_rate'] < 70) {
            $insights[] = "Low appointment completion rate of {$appointmentsReport['completion_rate']}%. Consider follow-up strategies.";
        }
        
        // Technicians insight
        if ($techniciansReport['average_efficiency'] > 2.0) {
            $insights[] = "Technicians are highly efficient with an average of {$techniciansReport['average_efficiency']} jobs per hour.";
        } elseif ($techniciansReport['average_efficiency'] < 1.0) {
            $insights[] = "Technician efficiency is low at {$techniciansReport['average_efficiency']} jobs per hour. Consider training or process improvements.";
        }
        
        // Customers insight
        if ($customersReport['repeat_rate'] > 50) {
            $insights[] = "Strong customer loyalty with {$customersReport['repeat_rate']}% repeat rate.";
        } elseif ($customersReport['repeat_rate'] < 20) {
            $insights[] = "Low customer repeat rate of {$customersReport['repeat_rate']}%. Focus on retention strategies.";
        }
        
        // Retention insight
        $retentionStats = $customersReport['retention_stats'];
        if ($retentionStats['at_risk']->percentage > 15) {
            $insights[] = "High percentage of at-risk customers ({$retentionStats['at_risk']->percentage}%). Consider proactive outreach.";
        }
        
        return [
            'insights' => $insights,
            'recommendations' => $this->generateRecommendations($revenueReport, $appointmentsReport, $techniciansReport, $customersReport),
            'key_metrics' => [
                'revenue_per_appointment' => $revenueReport['total_revenue'] > 0 && $appointmentsReport['total_appointments'] > 0 ? 
                    round($revenueReport['total_revenue'] / $appointmentsReport['total_appointments'], 2) : 0,
                'customer_acquisition_cost' => 0, // Would need marketing spend data
                'lifetime_customer_value' => 0, // Would need historical data
            ],
        ];
    }

    /**
     * Generate business recommendations.
     */
    private function generateRecommendations($revenueReport, $appointmentsReport, $techniciansReport, $customersReport)
    {
        $recommendations = [];
        
        // Revenue recommendations
        if ($revenueReport['total_revenue'] < 10000) {
            $recommendations[] = "Consider upselling additional services to increase average transaction value.";
        }
        
        // Appointment recommendations
        if ($appointmentsReport['cancelled_appointments'] > 0) {
            $cancellationRate = ($appointmentsReport['cancelled_appointments'] / $appointmentsReport['total_appointments']) * 100;
            if ($cancellationRate > 10) {
                $recommendations[] = "High cancellation rate of {$cancellationRate}%. Implement reminder system and cancellation policies.";
            }
        }
        
        // Technician recommendations
        $efficiencyRange = max(array_column($techniciansReport['performance_data'], 'efficiency')) - 
                          min(array_column($techniciansReport['performance_data'], 'efficiency'));
        if ($efficiencyRange > 1.0) {
            $recommendations[] = "Large variation in technician efficiency. Consider sharing best practices among team.";
        }
        
        // Customer recommendations
        if ($customersReport['repeat_rate'] < 30) {
            $recommendations[] = "Implement customer loyalty program to increase repeat business.";
        }
        
        return $recommendations;
    }

    /**
     * Calculate daily technician productivity.
     */
    private function calculateDailyProductivity($date)
    {
        $technicians = Technician::with(['timeLogs' => function($query) use ($date) {
            $query->whereDate('start_time', $date)
                  ->whereNotNull('end_time');
        }])->get();
        
        if ($technicians->isEmpty()) {
            return 0;
        }
        
        $totalEfficiency = 0;
        $count = 0;
        
        foreach ($technicians as $tech) {
            $totalHours = $tech->timeLogs->sum(function($log) {
                return $log->start_time->diffInHours($log->end_time);
            });
            
            $completedJobs = $tech->workOrders()->whereDate('completed_at', $date)->count();
            
            if ($totalHours > 0) {
                $efficiency = $completedJobs / $totalHours;
                $totalEfficiency += $efficiency;
                $count++;
            }
        }
        
        return $count > 0 ? round($totalEfficiency / $count, 2) : 0;
    }

    /**
     * Calculate monthly retention rate.
     */
    private function calculateMonthlyRetentionRate($month)
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $endDate = Carbon::parse($month . '-01')->endOfMonth()->toDateString();
        
        // Get customers who had service in previous month
        $previousMonthStart = Carbon::parse($month . '-01')->subMonth()->startOfMonth()->toDateString();
        $previousMonthEnd = Carbon::parse($month . '-01')->subMonth()->endOfMonth()->toDateString();
        
        $previousMonthCustomers = Customer::whereHas('serviceRecords', function($query) use ($previousMonthStart, $previousMonthEnd) {
            $query->whereBetween('service_date', [$previousMonthStart, $previousMonthEnd]);
        })->pluck('id');
        
        if ($previousMonthCustomers->isEmpty()) {
            return 0;
        }
        
        // Count how many returned this month
        $returnedCustomers = Customer::whereIn('id', $previousMonthCustomers)
            ->whereHas('serviceRecords', function($query) use ($startDate, $endDate) {
                $query->whereBetween('service_date', [$startDate, $endDate]);
            })
            ->count();
        
        return round(($returnedCustomers / $previousMonthCustomers->count()) * 100, 2);
    }

    /**
     * Export analytics report.
     */
    public function exportReport(Request $request)
    {
        $reportType = $request->get('type', 'overview');
        $format = $request->get('format', 'json');
        $startDate = $request->get('start_date', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        
        $report = $this->getAnalyticsReport($request);
        
        if ($format === 'csv') {
            return $this->exportToCsv($report, $reportType);
        }
        
        // Default to JSON
        return response()->json($report);
    }

    /**
     * Export report to CSV.
     */
    private function exportToCsv($report, $reportType)
    {
        $filename = "analytics_report_{$reportType}_" . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($report, $reportType) {
            $file = fopen('php://output', 'w');
            
            // Write headers based on report type
            switch ($reportType) {
                case 'revenue':
                    fputcsv($file, ['Date', 'Revenue', 'Invoice Count', 'Service Type', 'Service Revenue']);
                    if (isset($report['daily_data'])) {
                        foreach ($report['daily_data'] as $data) {
                            fputcsv($file, [$data['date'], $data['revenue'], '', '', '']);
                        }
                    }
                    if (isset($report['revenue_by_service'])) {
                        foreach ($report['revenue_by_service'] as $service) {
                            fputcsv($file, ['', '', '', $service->service_type, $service->total]);
                        }
                    }
                    break;
                    
                case 'appointments':
                    fputcsv($file, ['Date', 'Appointments', 'Completed', 'Cancelled', 'No Show', 'Completion Rate']);
                    if (isset($report['daily_data'])) {
                        foreach ($report['daily_data'] as $data) {
                            fputcsv($file, [$data['date'], $data['appointments'], '', '', '', '']);
                        }
                    }
                    break;
                    
                default:
                    fputcsv($file, ['Metric', 'Value']);
                    foreach ($report as $key => $value) {
                        if (!is_array($value)) {
                            fputcsv($file, [$key, $value]);
                        }
                    }
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get real-time dashboard data.
     */
    public function getRealTimeData()
    {
        $today = Carbon::now()->toDateString();
        
        $data = [
            'today_revenue' => Invoice::whereDate('created_at', $today)->sum('total_amount'),
            'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
            'active_jobs' => WorkOrder::whereIn('status', ['in_progress', 'scheduled'])->count(),
            'technicians_working' => Technician::whereHas('timeLogs', function($query) use ($today) {
                $query->whereDate('start_time', $today)->whereNull('end_time');
            })->count(),
            'waiting_customers' => Appointment::whereDate('appointment_date', $today)
                ->where('status', 'waiting')
                ->count(),
        ];
        
        return response()->json($data);
    }

    /**
     * Get predictive analytics.
     */
    public function getPredictiveAnalytics()
    {
        $nextWeekStart = Carbon::now()->addWeek()->startOfWeek()->toDateString();
        $nextWeekEnd = Carbon::now()->addWeek()->endOfWeek()->toDateString();
        
        // Predict appointments based on historical data
        $historicalAppointments = BusinessIntelligenceMetric::where('metric_name', 'appointments')
            ->where('metric_type', 'weekly')
            ->orderBy('metric_date', 'desc')
            ->limit(4)
            ->get()
            ->pluck('metric_value');
        
        $predictedAppointments = $historicalAppointments->avg();
        
        // Predict revenue based on historical data
        $historicalRevenue = BusinessIntelligenceMetric::where('metric_name', 'weekly_revenue')
            ->where('metric_type', 'weekly')
            ->orderBy('metric_date', 'desc')
            ->limit(4)
            ->get()
            ->pluck('metric_value');
        
        $predictedRevenue = $historicalRevenue->avg();
        
        // Get upcoming expected services
        $upcomingServices = RetentionAnalytics::withUpcomingService(7)->count();
        
        return response()->json([
            'next_week' => [
                'start_date' => $nextWeekStart,
                'end_date' => $nextWeekEnd,
                'predicted_appointments' => round($predictedAppointments),
                'predicted_revenue' => round($predictedRevenue, 2),
                'upcoming_expected_services' => $upcomingServices,
            ],
            'confidence_level' => 'medium', // Based on data quality and consistency
            'data_points_used' => $historicalAppointments->count(),
        ]);
    }
}
