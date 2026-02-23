<?php

namespace App\Http\Controllers;

use App\Services\ReportGenerator;
use App\Models\ReportSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    protected $reportGenerator;
    
    public function __construct(ReportGenerator $reportGenerator)
    {
        $this->reportGenerator = $reportGenerator;
        $this->middleware('auth');
    }
    
    /**
     * Display the reports dashboard.
     */
    public function dashboard()
    {
        return view('reports.dashboard');
    }
    
    /**
     * Display daily activity report.
     */
    public function dailyActivity(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $filters = $request->only(['technician_id', 'service_type']);
        
        // Get report settings
        $settings = ReportSetting::getEffectiveSettings('daily_activity', Auth::id());
        
        // Generate report
        $report = $this->reportGenerator->generateDailyActivityReport(
            Carbon::parse($date),
            $filters
        );
        
        return view('reports.daily-activity', [
            'report' => $report,
            'settings' => $settings,
            'date' => $date,
            'filters' => $filters,
        ]);
    }
    
    /**
     * Display monthly performance report.
     */
    public function monthlyPerformance(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $filters = $request->only(['service_type']);
        
        // Get report settings
        $settings = ReportSetting::getEffectiveSettings('monthly_performance', Auth::id());
        
        // Generate report
        $report = $this->reportGenerator->generateMonthlyPerformanceReport(
            Carbon::parse($date),
            $filters
        );
        
        return view('reports.monthly-performance', [
            'report' => $report,
            'settings' => $settings,
            'date' => $date,
            'filters' => $filters,
        ]);
    }
    
    /**
     * Display customer history report.
     */
    public function customerHistory(Request $request)
    {
        $customerId = $request->input('customer_id');
        $filters = $request->only(['date_from', 'date_to', 'service_type']);
        
        // Get report settings
        $settings = ReportSetting::getEffectiveSettings('customer_history', Auth::id());
        
        // Generate report
        $report = $this->reportGenerator->generateCustomerHistoryReport(
            $customerId,
            $filters
        );
        
        return view('reports.customer-history', [
            'report' => $report,
            'settings' => $settings,
            'customer_id' => $customerId,
            'filters' => $filters,
        ]);
    }
    
    /**
     * Export report in various formats.
     */
    public function export(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:daily_activity,monthly_performance,customer_history',
            'format' => 'required|in:pdf,csv,excel',
            'date' => 'required_if:report_type,daily_activity,monthly_performance',
        ]);
        
        $reportType = $request->input('report_type');
        $format = $request->input('format');
        $date = $request->input('date');
        $filters = $request->except(['report_type', 'format', 'date', '_token']);
        
        // Generate the appropriate report
        switch ($reportType) {
            case 'daily_activity':
                $reportData = $this->reportGenerator->generateDailyActivityReport(
                    Carbon::parse($date),
                    $filters
                );
                break;
                
            case 'monthly_performance':
                $reportData = $this->reportGenerator->generateMonthlyPerformanceReport(
                    Carbon::parse($date),
                    $filters
                );
                break;
                
            case 'customer_history':
                $customerId = $request->input('customer_id');
                $reportData = $this->reportGenerator->generateCustomerHistoryReport(
                    $customerId,
                    $filters
                );
                break;
                
            default:
                return back()->with('error', 'Invalid report type.');
        }
        
        // Export the report
        $exportData = $this->reportGenerator->exportReport($reportData, $format);
        
        // For now, return JSON response (in real implementation, would generate file)
        return response()->json([
            'success' => true,
            'message' => 'Report exported successfully',
            'export' => $exportData,
        ]);
    }
    
    /**
     * Save report settings.
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:daily_activity,monthly_performance,customer_history',
            'columns' => 'nullable|array',
            'filters' => 'nullable|array',
            'schedule' => 'nullable|in:daily,weekly,monthly',
            'format' => 'required|in:pdf,csv,excel',
            'is_default' => 'boolean',
        ]);
        
        $userId = Auth::id();
        $reportType = $request->input('report_type');
        
        // Check if user already has settings for this report type
        $existingSettings = ReportSetting::where('report_type', $reportType)
            ->where('user_id', $userId)
            ->first();
        
        if ($existingSettings) {
            // Update existing settings
            $existingSettings->update($request->only([
                'columns', 'filters', 'schedule', 'format', 'is_default'
            ]));
            $settings = $existingSettings;
        } else {
            // Create new settings
            $settings = ReportSetting::create([
                'report_type' => $reportType,
                'user_id' => $userId,
                'columns' => $request->input('columns', []),
                'filters' => $request->input('filters', []),
                'schedule' => $request->input('schedule'),
                'format' => $request->input('format'),
                'is_default' => $request->input('is_default', false),
            ]);
        }
        
        return back()->with('success', 'Report settings saved successfully.');
    }
    
    /**
     * Get report settings for a specific report type.
     */
    public function getSettings(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:daily_activity,monthly_performance,customer_history',
        ]);
        
        $settings = ReportSetting::getEffectiveSettings(
            $request->input('report_type'),
            Auth::id()
        );
        
        return response()->json($settings);
    }
    
    /**
     * Schedule automated reports.
     */
    public function schedule(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:daily_activity,monthly_performance,customer_history',
            'schedule' => 'required|in:daily,weekly,monthly',
            'recipients' => 'required|string',
            'format' => 'required|in:pdf,csv,excel',
        ]);
        
        // In a real implementation, this would create a scheduled job
        // For now, just save the schedule settings
        
        $settings = ReportSetting::updateOrCreate(
            [
                'report_type' => $request->input('report_type'),
                'user_id' => Auth::id(),
            ],
            [
                'schedule' => $request->input('schedule'),
                'recipients' => $request->input('recipients'),
                'format' => $request->input('format'),
            ]
        );
        
        return back()->with('success', 'Report scheduled successfully.');
    }
    
    /**
     * Get available report types.
     */
    public function getReportTypes()
    {
        return response()->json([
            'report_types' => [
                [
                    'id' => 'daily_activity',
                    'name' => 'Daily Activity Report',
                    'description' => 'Summary of daily appointments, work orders, invoices, and revenue',
                    'icon' => 'calendar-day',
                ],
                [
                    'id' => 'monthly_performance',
                    'name' => 'Monthly Performance Report',
                    'description' => 'Monthly revenue, expenses, profit, and customer metrics',
                    'icon' => 'chart-line',
                ],
                [
                    'id' => 'customer_history',
                    'name' => 'Customer History Report',
                    'description' => 'Complete service history for customers and their vehicles',
                    'icon' => 'users',
                ],
            ],
        ]);
    }
    
    /**
     * Get report preview data.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:daily_activity,monthly_performance,customer_history',
            'date' => 'required_if:report_type,daily_activity,monthly_performance',
        ]);
        
        $reportType = $request->input('report_type');
        $date = $request->input('date');
        $filters = $request->except(['report_type', 'date', '_token']);
        
        // Generate preview data
        switch ($reportType) {
            case 'daily_activity':
                $previewData = $this->reportGenerator->generateDailyActivityReport(
                    Carbon::parse($date),
                    $filters
                );
                break;
                
            case 'monthly_performance':
                $previewData = $this->reportGenerator->generateMonthlyPerformanceReport(
                    Carbon::parse($date),
                    $filters
                );
                break;
                
            case 'customer_history':
                $customerId = $request->input('customer_id');
                $previewData = $this->reportGenerator->generateCustomerHistoryReport(
                    $customerId,
                    $filters
                );
                break;
                
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
        
        // Return limited preview data
        $limitedData = $this->limitPreviewData($previewData);
        
        return response()->json([
            'success' => true,
            'preview' => $limitedData,
            'report_type' => $reportType,
        ]);
    }
    
    /**
     * Limit preview data to avoid large responses.
     */
    private function limitPreviewData(array $data): array
    {
        if (isset($data['details'])) {
            foreach ($data['details'] as $key => $detail) {
                if (is_array($detail) && count($detail) > 10) {
                    $data['details'][$key] = array_slice($detail, 0, 10);
                    $data['details'][$key . '_truncated'] = true;
                    $data['details'][$key . '_total'] = count($detail);
                }
            }
        }
        
        if (isset($data['customers']) && count($data['customers']) > 5) {
            $data['customers'] = array_slice($data['customers'], 0, 5);
            $data['customers_truncated'] = true;
            $data['customers_total'] = count($data['customers']);
        }
        
        return $data;
    }
}