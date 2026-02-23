<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ServiceRecord;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_customers' => Customer::count(),
            'total_vehicles' => Vehicle::count(),
            'total_services' => ServiceRecord::count(),
            'active_technicians' => User::where('role', 'technician')->where('is_active', true)->count(),
            'revenue_this_month' => ServiceRecord::whereMonth('service_date', now()->month)
                ->whereYear('service_date', now()->year)
                ->sum('final_amount'),
            'pending_services' => ServiceRecord::where('service_status', '!=', 'completed')->count(),
            'overdue_payments' => ServiceRecord::where('payment_status', 'overdue')->count(),
            'upcoming_services' => Vehicle::where('next_service_date', '>=', now())
                ->where('next_service_date', '<=', now()->addDays(30))
                ->count(),
        ];

        // Get recent services
        $recentServices = ServiceRecord::with(['customer', 'vehicle', 'technician'])
            ->orderBy('service_date', 'desc')
            ->limit(10)
            ->get();

        // Get upcoming services
        $upcomingServices = Vehicle::with('customer')
            ->where('next_service_date', '>=', now())
            ->where('next_service_date', '<=', now()->addDays(30))
            ->orderBy('next_service_date')
            ->limit(10)
            ->get();

        // Get revenue by month for the last 6 months
        $revenueByMonth = ServiceRecord::select(
                DB::raw('DATE_FORMAT(service_date, "%Y-%m") as month'),
                DB::raw('SUM(final_amount) as revenue')
            )
            ->where('service_date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('revenue', 'month');

        // Get service types distribution
        $serviceTypes = ServiceRecord::select('service_type', DB::raw('COUNT(*) as count'))
            ->groupBy('service_type')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Get top customers by revenue
        $topCustomers = Customer::withSum('serviceRecords', 'final_amount')
            ->orderBy('service_records_sum_final_amount', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'recentServices',
            'upcomingServices',
            'revenueByMonth',
            'serviceTypes',
            'topCustomers'
        ));
    }

    public function analytics()
    {
        // Monthly revenue trend
        $monthlyRevenue = ServiceRecord::select(
                DB::raw('DATE_FORMAT(service_date, "%Y-%m") as month'),
                DB::raw('SUM(final_amount) as revenue'),
                DB::raw('COUNT(*) as service_count'),
                DB::raw('AVG(final_amount) as average_ticket')
            )
            ->where('service_date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Customer acquisition trend
        $customerAcquisition = Customer::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as new_customers')
            )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Technician productivity
        $technicianProductivity = User::where('role', 'technician')
            ->withCount(['technicianServiceRecords as completed_services' => function($query) {
                $query->where('service_status', 'completed');
            }])
            ->withSum(['technicianServiceRecords as total_revenue' => function($query) {
                $query->where('service_status', 'completed');
            }], 'final_amount')
            ->orderBy('completed_services', 'desc')
            ->get();

        // Vehicle age distribution
        $vehicleAgeDistribution = Vehicle::select(
                DB::raw('FLOOR((YEAR(CURDATE()) - year) / 5) * 5 as age_range'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('age_range')
            ->orderBy('age_range')
            ->get();

        // Service type profitability
        $serviceProfitability = ServiceRecord::select(
                'service_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(final_amount) as revenue'),
                DB::raw('AVG(final_amount) as average_ticket'),
                DB::raw('SUM(labor_cost + parts_cost) as total_cost'),
                DB::raw('SUM(final_amount - (labor_cost + parts_cost)) as profit')
            )
            ->groupBy('service_type')
            ->orderBy('profit', 'desc')
            ->get();

        return view('analytics', compact(
            'monthlyRevenue',
            'customerAcquisition',
            'technicianProductivity',
            'vehicleAgeDistribution',
            'serviceProfitability'
        ));
    }

    public function reports()
    {
        return view('reports.index');
    }

    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:revenue,customers,services,inventory',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,csv,excel',
        ]);

        // Generate report based on type
        switch ($validated['report_type']) {
            case 'revenue':
                $data = $this->generateRevenueReport($validated['start_date'], $validated['end_date']);
                break;
            case 'customers':
                $data = $this->generateCustomerReport($validated['start_date'], $validated['end_date']);
                break;
            case 'services':
                $data = $this->generateServiceReport($validated['start_date'], $validated['end_date']);
                break;
            case 'inventory':
                $data = $this->generateInventoryReport($validated['start_date'], $validated['end_date']);
                break;
        }

        // Return appropriate response based on format
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Report generated successfully'
        ]);
    }

    private function generateRevenueReport($startDate, $endDate)
    {
        return ServiceRecord::whereBetween('service_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(service_date) as date'),
                DB::raw('COUNT(*) as service_count'),
                DB::raw('SUM(final_amount) as revenue'),
                DB::raw('SUM(labor_cost) as labor_revenue'),
                DB::raw('SUM(parts_cost) as parts_revenue'),
                DB::raw('AVG(final_amount) as average_ticket')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function generateCustomerReport($startDate, $endDate)
    {
        return Customer::whereBetween('created_at', [$startDate, $endDate])
            ->withCount('serviceRecords')
            ->withSum('serviceRecords', 'final_amount')
            ->orderBy('created_at')
            ->get();
    }

    private function generateServiceReport($startDate, $endDate)
    {
        return ServiceRecord::whereBetween('service_date', [$startDate, $endDate])
            ->with(['customer', 'vehicle', 'technician'])
            ->orderBy('service_date')
            ->get();
    }

    private function generateInventoryReport($startDate, $endDate)
    {
        // This would typically query an inventory table
        // For now, return placeholder
        return [
            'message' => 'Inventory report functionality to be implemented'
        ];
    }

    // ============================================
    // Dashboard Widget Management Methods
    // ============================================

    /**
     * Get user's dashboard widgets.
     */
    public function getWidgets()
    {
        $userId = auth()->id();
        $widgets = \App\Models\DashboardWidget::getUserWidgets($userId);
        
        return response()->json([
            'success' => true,
            'widgets' => $widgets,
        ]);
    }

    /**
     * Create a new dashboard widget.
     */
    public function createWidget(Request $request)
    {
        $validated = $request->validate([
            'widget_type' => 'required|in:metric_card,chart,table,kpi',
            'widget_title' => 'required|string|max:255',
            'metric_name' => 'nullable|string|max:255',
            'column_position' => 'required|integer|min:0|max:3',
            'row_position' => 'required|integer|min:0|max:10',
            'width' => 'required|integer|min:1|max:4',
            'height' => 'required|integer|min:1|max:4',
        ]);

        $userId = auth()->id();
        
        $widget = \App\Models\DashboardWidget::createDefaultWidget(
            $userId,
            $validated['widget_type'],
            $validated['widget_title'],
            $validated['metric_name'] ?? null,
            [],
            $validated['column_position'],
            $validated['row_position'],
            $validated['width'],
            $validated['height']
        );

        return response()->json([
            'success' => true,
            'message' => 'Widget created successfully',
            'widget' => $widget,
        ]);
    }

    /**
     * Update a dashboard widget.
     */
    public function updateWidget(Request $request, $widgetId)
    {
        $validated = $request->validate([
            'widget_title' => 'sometimes|string|max:255',
            'column_position' => 'sometimes|integer|min:0|max:3',
            'row_position' => 'sometimes|integer|min:0|max:10',
            'width' => 'sometimes|integer|min:1|max:4',
            'height' => 'sometimes|integer|min:1|max:4',
            'is_visible' => 'sometimes|boolean',
            'is_collapsed' => 'sometimes|boolean',
            'refresh_interval' => 'sometimes|integer|min:0|max:3600',
        ]);

        $widget = \App\Models\DashboardWidget::find($widgetId);
        
        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        // Check if user owns the widget
        if ($widget->user_id != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this widget',
            ], 403);
        }

        $widget->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Widget updated successfully',
            'widget' => $widget,
        ]);
    }

    /**
     * Delete a dashboard widget.
     */
    public function deleteWidget($widgetId)
    {
        $widget = \App\Models\DashboardWidget::find($widgetId);
        
        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        // Check if user owns the widget
        if ($widget->user_id != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this widget',
            ], 403);
        }

        $widget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Widget deleted successfully',
        ]);
    }

    /**
     * Update widget position.
     */
    public function updateWidgetPosition(Request $request)
    {
        $validated = $request->validate([
            'widget_id' => 'required|integer|exists:dashboard_widgets,id',
            'column' => 'required|integer|min:0|max:3',
            'row' => 'required|integer|min:0|max:10',
        ]);

        $widget = \App\Models\DashboardWidget::find($validated['widget_id']);
        
        // Check if user owns the widget
        if ($widget->user_id != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this widget',
            ], 403);
        }

        $success = \App\Models\DashboardWidget::updateWidgetPosition(
            $validated['widget_id'],
            $validated['column'],
            $validated['row']
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Widget position updated successfully' : 'Failed to update widget position',
        ]);
    }

    /**
     * Toggle widget visibility.
     */
    public function toggleWidgetVisibility($widgetId)
    {
        $widget = \App\Models\DashboardWidget::find($widgetId);
        
        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        // Check if user owns the widget
        if ($widget->user_id != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this widget',
            ], 403);
        }

        $success = \App\Models\DashboardWidget::toggleVisibility($widgetId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Widget visibility toggled successfully' : 'Failed to toggle widget visibility',
            'is_visible' => $widget->fresh()->is_visible,
        ]);
    }

    /**
     * Toggle widget collapsed state.
     */
    public function toggleWidgetCollapsed($widgetId)
    {
        $widget = \App\Models\DashboardWidget::find($widgetId);
        
        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        // Check if user owns the widget
        if ($widget->user_id != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this widget',
            ], 403);
        }

        $success = \App\Models\DashboardWidget::toggleCollapsed($widgetId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Widget state toggled successfully' : 'Failed to toggle widget state',
            'is_collapsed' => $widget->fresh()->is_collapsed,
        ]);
    }

    /**
     * Get available widget types and configurations.
     */
    public function getWidgetTypes()
    {
        $widgetTypes = [
            'metric_card' => [
                'name' => 'Metric Card',
                'description' => 'Display a single metric with trend indicator',
                'default_config' => \App\Models\DashboardWidget::getDefaultConfig('metric_card'),
                'supported_metrics' => [
                    'daily_revenue',
                    'appointments_today',
                    'active_jobs',
                    'customer_satisfaction',
                    'technician_productivity',
                    'retention_rate',
                ],
            ],
            'chart' => [
                'name' => 'Chart',
                'description' => 'Display data in various chart formats',
                'default_config' => \App\Models\DashboardWidget::getDefaultConfig('chart'),
                'chart_types' => ['line', 'bar', 'pie', 'doughnut'],
            ],
            'table' => [
                'name' => 'Data Table',
                'description' => 'Display tabular data with sorting and filtering',
                'default_config' => \App\Models\DashboardWidget::getDefaultConfig('table'),
            ],
            'kpi' => [
                'name' => 'KPI Indicator',
                'description' => 'Display key performance indicators with targets',
                'default_config' => \App\Models\DashboardWidget::getDefaultConfig('kpi'),
            ],
        ];

        return response()->json([
            'success' => true,
            'widget_types' => $widgetTypes,
        ]);
    }

    /**
     * Get widget data for a specific widget.
     */
    public function getWidgetData($widgetId)
    {
        $widget = \App\Models\DashboardWidget::find($widgetId);
        
        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }

        // Check if user owns the widget
        if ($widget->user_id != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this widget',
            ], 403);
        }

        // Get data based on widget type and configuration
        $data = $this->generateWidgetData($widget);

        return response()->json([
            'success' => true,
            'widget' => $widget,
            'data' => $data,
        ]);
    }

    /**
     * Generate data for a widget based on its type and configuration.
     */
    private function generateWidgetData($widget)
    {
        switch ($widget->widget_type) {
            case 'metric_card':
                return $this->generateMetricCardData($widget);
            case 'chart':
                return $this->generateChartData($widget);
            case 'table':
                return $this->generateTableData($widget);
            case 'kpi':
                return $this->generateKpiData($widget);
            default:
                return [];
        }
    }

    /**
     * Generate data for a metric card widget.
     */
    private function generateMetricCardData($widget)
    {
        $metricName = $widget->metric_name;
        
        if (!$metricName) {
            return [
                'value' => 0,
                'trend' => null,
                'formatted_value' => '0',
            ];
        }

        $value = \App\Models\BusinessIntelligenceMetric::getLatestValue($metricName, 'daily') ?? 0;
        $trend = \App\Models\BusinessIntelligenceMetric::getMetricTrend($metricName, 'daily', 1);

        // Format value based on metric type
        $formattedValue = $this->formatMetricValue($metricName, $value);

        return [
            'value' => $value,
            'trend' => $trend,
            'formatted_value' => $formattedValue,
            'last_updated' => now()->toDateTimeString(),
        ];
    }

    /**
     * Generate data for a chart widget.
     */
    private function generateChartData($widget)
    {
        $config = $widget->widget_config ?? [];
        $chartType = $config['chart_type'] ?? 'line';
        $metricName = $widget->metric_name;

        if (!$metricName) {
            return $this->generateDefaultChartData($chartType);
        }

        // Get historical data for the metric
        $startDate = now()->subDays(30)->toDateString();
        $endDate = now()->toDateString();

        $metrics = \App\Models\BusinessIntelligenceMetric::where('metric_name', $metricName)
            ->where('metric_type', 'daily')
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->orderBy('metric_date')
            ->get();

        $labels = [];
        $data = [];

        foreach ($metrics as $metric) {
            $labels[] = $metric->metric_date->format('M d');
            $data[] = $metric->metric_value;
        }

        return [
            'chart_type' => $chartType,
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $widget->widget_title,
                    'data' => $data,
                    'borderColor' => '#3498db',
                    'backgroundColor' => 'rgba(52, 152, 219, 0.1)',
                ],
            ],
        ];
    }

    /**
     * Generate default chart data when no metric is specified.
     */
    private function generateDefaultChartData($chartType)
    {
        $labels = [];
        $data = [];

        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');
            $data[] = rand(1000, 5000); // Random data for demo
        }

        return [
            'chart_type' => $chartType,
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Sample Data',
                    'data' => $data,
                    'borderColor' => '#3498db',
                    'backgroundColor' => 'rgba(52, 152, 219, 0.1)',
                ],
            ],
        ];
    }

    /**
     * Generate data for a table widget.
     */
    private function generateTableData($widget)
    {
        $config = $widget->widget_config ?? [];
        $tableType = $config['table_type'] ?? 'recent_services';

        switch ($tableType) {
            case 'recent_services':
                $data = \App\Models\ServiceRecord::with(['customer', 'vehicle'])
                    ->orderBy('service_date', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function($service) {
                        return [
                            'id' => $service->id,
                            'date' => $service->service_date->format('Y-m-d'),
                            'customer' => $service->customer->name,
                            'vehicle' => $service->vehicle->make . ' ' . $service->vehicle->model,
                            'service_type' => $service->service_type,
                            'amount' => $service->final_amount,
                            'status' => $service->service_status,
                        ];
                    });
                $columns = ['Date', 'Customer', 'Vehicle', 'Service Type', 'Amount', 'Status'];
                break;

            case 'upcoming_appointments':
                $data = \App\Models\Appointment::with(['customer', 'vehicle'])
                    ->where('appointment_date', '>=', now())
                    ->orderBy('appointment_date')
                    ->limit(10)
                    ->get()
                    ->map(function($appointment) {
                        return [
                            'id' => $appointment->id,
                            'date' => $appointment->appointment_date->format('Y-m-d H:i'),
                            'customer' => $appointment->customer->name,
                            'vehicle' => $appointment->vehicle->make . ' ' . $appointment->vehicle->model,
                            'service' => $appointment->service_type,
                            'status' => $appointment->status,
                        ];
                    });
                $columns = ['Date & Time', 'Customer', 'Vehicle', 'Service', 'Status'];
                break;

            case 'top_technicians':
                $data = \App\Models\Technician::withCount(['workOrders' => function($query) {
                    $query->where('status', 'completed');
                }])
                    ->withSum(['workOrders' => function($query) {
                        $query->where('status', 'completed');
                    }], 'total_cost')
                    ->orderBy('work_orders_count', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function($tech) {
                        return [
                            'id' => $tech->id,
                            'name' => $tech->name,
                            'completed_jobs' => $tech->work_orders_count,
                            'revenue_generated' => $tech->work_orders_sum_total_cost,
                            'rating' => $tech->average_rating ?? 'N/A',
                        ];
                    });
                $columns = ['Name', 'Completed Jobs', 'Revenue Generated', 'Rating'];
                break;

            default:
                $data = [];
                $columns = [];
        }

        return [
            'columns' => $columns,
            'rows' => $data,
            'total_rows' => count($data),
        ];
    }

    /**
     * Generate data for a KPI widget.
     */
    private function generateKpiData($widget)
    {
        $config = $widget->widget_config ?? [];
        $targetValue = $config['target_value'] ?? null;
        $kpiType = $config['kpi_type'] ?? 'revenue';

        switch ($kpiType) {
            case 'revenue':
                $currentValue = \App\Models\Invoice::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount');
                $previousValue = \App\Models\Invoice::whereMonth('created_at', now()->subMonth()->month)
                    ->whereYear('created_at', now()->subMonth()->year)
                    ->sum('total_amount');
                break;

            case 'appointments':
                $currentValue = \App\Models\Appointment::whereMonth('appointment_date', now()->month)
                    ->whereYear('appointment_date', now()->year)
                    ->count();
                $previousValue = \App\Models\Appointment::whereMonth('appointment_date', now()->subMonth()->month)
                    ->whereYear('appointment_date', now()->subMonth()->year)
                    ->count();
                break;

            case 'customer_satisfaction':
                // This would come from customer satisfaction surveys
                $currentValue = 4.2; // out of 5
                $previousValue = 4.1;
                break;

            default:
                $currentValue = 0;
                $previousValue = 0;
        }

        $change = $currentValue - $previousValue;
        $percentage = $previousValue > 0 ? ($change / $previousValue) * 100 : 0;

        return [
            'current_value' => $currentValue,
            'previous_value' => $previousValue,
            'target_value' => $targetValue,
            'change' => $change,
            'percentage_change' => round($percentage, 2),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
            'progress' => $targetValue > 0 ? round(($currentValue / $targetValue) * 100, 2) : null,
        ];
    }

    /**
     * Format metric value based on metric type.
     */
    private function formatMetricValue($metricName, $value)
    {
        // Check if it's a currency metric
        $currencyMetrics = ['daily_revenue', 'weekly_revenue', 'monthly_revenue', 'total_spent'];
        if (in_array($metricName, $currencyMetrics)) {
            return '$' . number_format($value, 2);
        }

        // Check if it's a percentage metric
        $percentageMetrics = ['retention_rate', 'customer_satisfaction', 'technician_productivity', 'completion_rate'];
        if (in_array($metricName, $percentageMetrics)) {
            return number_format($value, 2) . '%';
        }

        // Default formatting
        return number_format($value);
    }

    /**
     * Reset user's dashboard to default widgets.
     */
    public function resetDashboard()
    {
        $userId = auth()->id();
        
        // Delete existing widgets
        \App\Models\DashboardWidget::where('user_id', $userId)->delete();
        
        // Create default widgets
        $defaultWidgets = [
            [
                'type' => 'metric_card',
                'title' => 'Daily Revenue',
                'metric' => 'daily_revenue',
                'column' => 0,
                'row' => 0,
                'width' => 1,
                'height' => 1,
            ],
            [
                'type' => 'metric_card',
                'title' => 'Today\'s Appointments',
                'metric' => 'appointments_today',
                'column' => 1,
                'row' => 0,
                'width' => 1,
                'height' => 1,
            ],
            [
                'type' => 'metric_card',
                'title' => 'Active Jobs',
                'metric' => 'active_jobs',
                'column' => 2,
                'row' => 0,
                'width' => 1,
                'height' => 1,
            ],
            [
                'type' => 'chart',
                'title' => 'Revenue Trend',
                'metric' => 'daily_revenue',
                'column' => 0,
                'row' => 1,
                'width' => 2,
                'height' => 2,
                'config' => ['chart_type' => 'line'],
            ],
            [
                'type' => 'table',
                'title' => 'Recent Services',
                'metric' => null,
                'column' => 2,
                'row' => 1,
                'width' => 2,
                'height' => 2,
                'config' => ['table_type' => 'recent_services'],
            ],
        ];

        foreach ($defaultWidgets as $widgetConfig) {
            \App\Models\DashboardWidget::createDefaultWidget(
                $userId,
                $widgetConfig['type'],
                $widgetConfig['title'],
                $widgetConfig['metric'],
                $widgetConfig['config'] ?? [],
                $widgetConfig['column'],
                $widgetConfig['row'],
                $widgetConfig['width'],
                $widgetConfig['height']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard reset to default configuration',
            'widgets_created' => count($defaultWidgets),
        ]);
    }
}