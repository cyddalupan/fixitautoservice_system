<?php

namespace App\Http\Controllers;

use App\Models\QualityCheck;
use App\Models\WorkOrderQualityCheck;
use App\Models\ComplianceDocument;
use App\Models\CustomerSatisfactionSurvey;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QualityControlDashboardController extends Controller
{
    /**
     * Display the main quality control dashboard.
     */
    public function index()
    {
        // Quality Check Statistics
        $qualityCheckStats = $this->getQualityCheckStats();
        
        // Work Order Quality Statistics
        $workOrderQualityStats = $this->getWorkOrderQualityStats();
        
        // Compliance Statistics
        $complianceStats = $this->getComplianceStats();
        
        // Customer Satisfaction Statistics
        $customerSatisfactionStats = $this->getCustomerSatisfactionStats();
        
        // Recent Activity
        $recentActivity = $this->getRecentActivity();
        
        // Top Performers
        $topPerformers = $this->getTopPerformers();
        
        // Critical Alerts
        $criticalAlerts = $this->getCriticalAlerts();

        return view('quality-control.dashboard', compact(
            'qualityCheckStats',
            'workOrderQualityStats',
            'complianceStats',
            'customerSatisfactionStats',
            'recentActivity',
            'topPerformers',
            'criticalAlerts'
        ));
    }

    /**
     * Get quality check statistics.
     */
    private function getQualityCheckStats(): array
    {
        $totalChecks = QualityCheck::count();
        $activeChecks = QualityCheck::where('is_active', true)->count();
        
        // Checks by category
        $checksByCategory = QualityCheck::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();
        
        // Most used checks
        $mostUsedChecks = QualityCheck::withCount('workOrderQualityChecks')
            ->orderBy('work_order_quality_checks_count', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_checks' => $totalChecks,
            'active_checks' => $activeChecks,
            'inactive_checks' => $totalChecks - $activeChecks,
            'checks_by_category' => $checksByCategory,
            'most_used_checks' => $mostUsedChecks,
        ];
    }

    /**
     * Get work order quality statistics.
     */
    private function getWorkOrderQualityStats(): array
    {
        $totalQualityChecks = WorkOrderQualityCheck::count();
        $pendingChecks = WorkOrderQualityCheck::where('status', 'pending')->count();
        $inProgressChecks = WorkOrderQualityCheck::where('status', 'in_progress')->count();
        $completedChecks = WorkOrderQualityCheck::where('status', 'completed')->count();
        $approvedChecks = WorkOrderQualityCheck::where('status', 'approved')->count();
        $rejectedChecks = WorkOrderQualityCheck::where('status', 'rejected')->count();
        
        // Pass rate statistics
        $passedChecks = WorkOrderQualityCheck::where('status', 'approved')->count();
        $failedChecks = WorkOrderQualityCheck::where('status', 'rejected')->count();
        $needsReworkChecks = WorkOrderQualityCheck::where('status', 'needs_rework')->count();
        
        $passRate = $totalQualityChecks > 0 
            ? round($passedChecks / $totalQualityChecks * 100, 2)
            : 0;
        
        // Average pass rate
        $averagePassRate = WorkOrderQualityCheck::whereNotNull('pass_rate')
            ->avg('pass_rate') ?? 0;
        
        // Quality checks by technician
        $qualityByTechnician = WorkOrderQualityCheck::select('technician_id', DB::raw('count(*) as total'), DB::raw('avg(pass_rate) as avg_pass_rate'))
            ->whereNotNull('technician_id')
            ->groupBy('technician_id')
            ->with('technician')
            ->orderBy('avg_pass_rate', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_checks' => $totalQualityChecks,
            'pending_checks' => $pendingChecks,
            'in_progress_checks' => $inProgressChecks,
            'completed_checks' => $completedChecks,
            'approved_checks' => $approvedChecks,
            'rejected_checks' => $rejectedChecks,
            'passed_checks' => $passedChecks,
            'failed_checks' => $failedChecks,
            'needs_rework_checks' => $needsReworkChecks,
            'pass_rate' => $passRate,
            'average_pass_rate' => round($averagePassRate, 2),
            'quality_by_technician' => $qualityByTechnician,
        ];
    }

    /**
     * Get compliance statistics.
     */
    private function getComplianceStats(): array
    {
        $totalDocuments = ComplianceDocument::count();
        $activeDocuments = ComplianceDocument::active()->count();
        $expiredDocuments = ComplianceDocument::expired()->count();
        $expiringSoonDocuments = ComplianceDocument::expiringSoon()->count();
        $documentsNeedingRenewal = ComplianceDocument::needsRenewal()->count();
        
        // Documents by type
        $documentsByType = ComplianceDocument::select('document_type', DB::raw('count(*) as count'))
            ->groupBy('document_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->document_type => $item->count];
            })
            ->toArray();
        
        // Critical expirations (within 7 days)
        $criticalExpirations = ComplianceDocument::whereDate('expiration_date', '>', now())
            ->whereDate('expiration_date', '<=', now()->addDays(7))
            ->with('assignedUser')
            ->orderBy('expiration_date')
            ->limit(5)
            ->get();

        return [
            'total_documents' => $totalDocuments,
            'active_documents' => $activeDocuments,
            'expired_documents' => $expiredDocuments,
            'expiring_soon_documents' => $expiringSoonDocuments,
            'documents_needing_renewal' => $documentsNeedingRenewal,
            'compliance_rate' => $totalDocuments > 0 
                ? round(($totalDocuments - $expiredDocuments) / $totalDocuments * 100, 2)
                : 0,
            'documents_by_type' => $documentsByType,
            'critical_expirations' => $criticalExpirations,
        ];
    }

    /**
     * Get customer satisfaction statistics.
     */
    private function getCustomerSatisfactionStats(): array
    {
        $totalSurveys = CustomerSatisfactionSurvey::count();
        $positiveSurveys = CustomerSatisfactionSurvey::positive()->count();
        $negativeSurveys = CustomerSatisfactionSurvey::negative()->count();
        $neutralSurveys = $totalSurveys - $positiveSurveys - $negativeSurveys;
        
        // Average ratings
        $averageRatings = [
            'overall' => CustomerSatisfactionSurvey::where('overall_rating', '>', 0)->avg('overall_rating') ?? 0,
            'quality' => CustomerSatisfactionSurvey::where('quality_rating', '>', 0)->avg('quality_rating') ?? 0,
            'timeliness' => CustomerSatisfactionSurvey::where('timeliness_rating', '>', 0)->avg('timeliness_rating') ?? 0,
            'communication' => CustomerSatisfactionSurvey::where('communication_rating', '>', 0)->avg('communication_rating') ?? 0,
            'cleanliness' => CustomerSatisfactionSurvey::where('cleanliness_rating', '>', 0)->avg('cleanliness_rating') ?? 0,
            'value' => CustomerSatisfactionSurvey::where('value_rating', '>', 0)->avg('value_rating') ?? 0,
        ];
        
        // Recommendation rates
        $recommendationRate = $totalSurveys > 0 
            ? round(CustomerSatisfactionSurvey::where('would_recommend', true)->count() / $totalSurveys * 100, 2)
            : 0;
        
        $returnRate = $totalSurveys > 0
            ? round(CustomerSatisfactionSurvey::where('would_return', true)->count() / $totalSurveys * 100, 2)
            : 0;
        
        // Surveys needing follow-up
        $surveysNeedingFollowUp = CustomerSatisfactionSurvey::needsFollowUp()
            ->with(['customer', 'technician'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_surveys' => $totalSurveys,
            'positive_surveys' => $positiveSurveys,
            'negative_surveys' => $negativeSurveys,
            'neutral_surveys' => $neutralSurveys,
            'satisfaction_rate' => $totalSurveys > 0 
                ? round($positiveSurveys / $totalSurveys * 100, 2)
                : 0,
            'average_ratings' => array_map(function ($rating) {
                return round($rating, 2);
            }, $averageRatings),
            'recommendation_rate' => $recommendationRate,
            'return_rate' => $returnRate,
            'surveys_needing_follow_up' => $surveysNeedingFollowUp,
        ];
    }

    /**
     * Get recent activity across all quality control systems.
     */
    private function getRecentActivity(): array
    {
        $recentActivity = [];
        
        // Recent quality checks
        $recentQualityChecks = WorkOrderQualityCheck::with(['workOrder', 'qualityCheck', 'technician'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($check) {
                return [
                    'type' => 'quality_check',
                    'title' => 'Quality Check: ' . ($check->qualityCheck->name ?? 'Unknown'),
                    'description' => 'Work Order: ' . ($check->workOrder->work_order_number ?? 'N/A'),
                    'status' => $check->status,
                    'status_color' => $check->getStatusColor(),
                    'created_at' => $check->created_at,
                    'user' => $check->technician ? $check->technician->name : 'System',
                ];
            });
        
        $recentActivity = array_merge($recentActivity, $recentQualityChecks->toArray());
        
        // Recent compliance documents
        $recentComplianceDocs = ComplianceDocument::with(['assignedUser', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($doc) {
                return [
                    'type' => 'compliance_document',
                    'title' => 'Compliance Document: ' . $doc->document_name,
                    'description' => 'Type: ' . $doc->getDocumentTypeLabel(),
                    'status' => $doc->getExpirationStatus(),
                    'status_color' => $doc->getExpirationStatusColor(),
                    'created_at' => $doc->created_at,
                    'user' => $doc->creator ? $doc->creator->name : 'System',
                ];
            });
        
        $recentActivity = array_merge($recentActivity, $recentComplianceDocs->toArray());
        
        // Recent customer surveys
        $recentSurveys = CustomerSatisfactionSurvey::with(['customer', 'technician'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($survey) {
                return [
                    'type' => 'customer_survey',
                    'title' => 'Customer Survey: ' . ($survey->customer->full_name ?? 'Unknown'),
                    'description' => 'Rating: ' . $survey->overall_rating . '/5',
                    'status' => $survey->status,
                    'status_color' => $survey->getStatusColor(),
                    'created_at' => $survey->created_at,
                    'user' => $survey->customer ? $survey->customer->full_name : 'Customer',
                ];
            });
        
        $recentActivity = array_merge($recentActivity, $recentSurveys->toArray());
        
        // Sort by created_at descending
        usort($recentActivity, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Return only top 10
        return array_slice($recentActivity, 0, 10);
    }

    /**
     * Get top performers (technicians with best quality scores).
     */
    private function getTopPerformers(): array
    {
        $topPerformers = User::where('role', 'technician')
            ->whereHas('workOrderQualityChecks')
            ->withCount(['workOrderQualityChecks as total_checks'])
            ->withCount(['workOrderQualityChecks as approved_checks' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->with(['workOrderQualityChecks' => function ($query) {
                $query->select('technician_id', DB::raw('avg(pass_rate) as avg_pass_rate'))
                    ->whereNotNull('pass_rate')
                    ->groupBy('technician_id');
            }])
            ->orderBy('approved_checks', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($technician) {
                $avgPassRate = $technician->workOrderQualityChecks->first()->avg_pass_rate ?? 0;
                $approvalRate = $technician->total_checks > 0 
                    ? round($technician->approved_checks / $technician->total_checks * 100, 2)
                    : 0;
                
                return [
                    'id' => $technician->id,
                    'name' => $technician->name,
                    'total_checks' => $technician->total_checks,
                    'approved_checks' => $technician->approved_checks,
                    'avg_pass_rate' => round($avgPassRate, 2),
                    'approval_rate' => $approvalRate,
                ];
            })
            ->toArray();

        return $topPerformers;
    }

    /**
     * Get critical alerts that need immediate attention.
     */
    private function getCriticalAlerts(): array
    {
        $alerts = [];
        
        // Expired compliance documents
        $expiredDocuments = ComplianceDocument::expired()
            ->with('assignedUser')
            ->orderBy('expiration_date', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($expiredDocuments as $doc) {
            $alerts[] = [
                'type' => 'compliance_expired',
                'title' => 'Compliance Document Expired: ' . $doc->document_name,
                'description' => 'Expired on ' . $doc->expiration_date->format('Y-m-d'),
                'priority' => 'high',
                'assigned_to' => $doc->assignedUser ? $doc->assignedUser->name : 'Unassigned',
                'created_at' => $doc->expiration_date,
            ];
        }
        
        // Compliance documents expiring within 7 days
        $expiringSoonDocuments = ComplianceDocument::whereDate('expiration_date', '>', now())
            ->whereDate('expiration_date', '<=', now()->addDays(7))
            ->with('assignedUser')
            ->orderBy('expiration_date')
            ->limit(5)
            ->get();
        
        foreach ($expiringSoonDocuments as $doc) {
            $daysUntilExpiry = $doc->getDaysUntilExpiration();
            $alerts[] = [
                'type' => 'compliance_expiring',
                'title' => 'Compliance Document Expiring Soon: ' . $doc->document_name,
                'description' => 'Expires in ' . $daysUntilExpiry . ' days',
                'priority' => 'medium',
                'assigned_to' => $doc->assignedUser ? $doc->assignedUser->name : 'Unassigned',
                'created_at' => $doc->expiration_date,
            ];
        }
        
        // Quality checks pending approval for more than 24 hours
        $pendingChecks = WorkOrderQualityCheck::where('status', 'completed')
            ->where('created_at', '<=', now()->subHours(24))
            ->with(['workOrder', 'qualityCheck'])
            ->orderBy('created_at')
            ->limit(5)
            ->get();
        
        foreach ($pendingChecks as $check) {
            $hoursPending = now()->diffInHours($check->created_at);
            $alerts[] = [
                'type' => 'quality_check_pending',
                'title' => 'Quality Check Pending Approval: ' . ($check->qualityCheck->name ?? 'Unknown'),
                'description' => 'Pending for ' . $hoursPending . ' hours',
                'priority' => 'medium',
                'work_order' => $check->workOrder ? $check->workOrder->work_order_number : 'N/A',
                'created_at' => $check->created_at,
            ];
        }
        
        // Customer surveys needing follow-up
        $surveysNeedingFollowUp = CustomerSatisfactionSurvey::needsFollowUp()
            ->with(['customer', 'technician'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($surveysNeedingFollowUp as $survey) {
            $alerts[] = [
                'type' => 'survey_follow_up',
                'title' => 'Customer Survey Needs Follow-up: ' . ($survey->customer->full_name ?? 'Unknown'),
                'description' => 'Rating: ' . $survey->overall_rating . '/5 - ' . $survey->getSentimentLabel(),
                'priority' => 'medium',
                'customer' => $survey->customer ? $survey->customer->full_name : 'Unknown',
                'technician' => $survey->technician ? $survey->technician->name : 'Unknown',
                'created_at' => $survey->created_at,
            ];
        }
        
        // Sort by priority and created_at
        usort($alerts, function ($a, $b) {
            $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            $aPriority = $priorityOrder[$a['priority']] ?? 0;
            $bPriority = $priorityOrder[$b['priority']] ?? 0;
            
            if ($aPriority !== $bPriority) {
                return $bPriority - $aPriority;
            }
            
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($alerts, 0, 10);
    }

    /**
     * Get quality metrics for charts.
     */
    public function getQualityMetrics(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        $metric = $request->get('metric', 'pass_rate'); // pass_rate, approval_rate, satisfaction_rate
        
        $data = [];
        
        switch ($period) {
            case 'day':
                // Last 24 hours by hour
                for ($i = 23; $i >= 0; $i--) {
                    $hour = now()->subHours($i);
                    $nextHour = $hour->copy()->addHour();
                    
                    $value = $this->getMetricValue($metric, $hour, $nextHour);
                    $data[] = [
                        'label' => $hour->format('H:00'),
                        'value' => $value,
                    ];
                }
                break;
                
            case 'week':
                // Last 7 days
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $start = $date->copy()->startOfDay();
                    $end = $date->copy()->endOfDay();
                    
                    $value = $this->getMetricValue($metric, $start, $end);
                    $data[] = [
                        'label' => $date->format('D'),
                        'value' => $value,
                    ];
                }
                break;
                
            case 'month':
                // Last 30 days
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $start = $date->copy()->startOfDay();
                    $end = $date->copy()->endOfDay();
                    
                    $value = $this->getMetricValue($metric, $start, $end);
                    $data[] = [
                        'label' => $date->format('m/d'),
                        'value' => $value,
                    ];
                }
                break;
                
            case 'year':
                // Last 12 months
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $start = $date->copy()->startOfMonth();
                    $end = $date->copy()->endOfMonth();
                    
                    $value = $this->getMetricValue($metric, $start, $end);
                    $data[] = [
                        'label' => $date->format('M Y'),
                        'value' => $value,
                    ];
                }
                break;
        }
        
        return response()->json([
            'metric' => $metric,
            'period' => $period,
            'data' => $data,
        ]);
    }
    
    /**
     * Get metric value for a specific time period.
     */
    private function getMetricValue(string $metric, $start, $end): float
    {
        switch ($metric) {
            case 'pass_rate':
                $checks = WorkOrderQualityCheck::whereBetween('created_at', [$start, $end])
                    ->whereNotNull('pass_rate')
                    ->get();
                
                return $checks->count() > 0 
                    ? round($checks->avg('pass_rate'), 2)
                    : 0;
                
            case 'approval_rate':
                $totalChecks = WorkOrderQualityCheck::whereBetween('created_at', [$start, $end])->count();
                $approvedChecks = WorkOrderQualityCheck::whereBetween('created_at', [$start, $end])
                    ->where('status', 'approved')
                    ->count();
                
                return $totalChecks > 0 
                    ? round($approvedChecks / $totalChecks * 100, 2)
                    : 0;
                
            case 'satisfaction_rate':
                $totalSurveys = CustomerSatisfactionSurvey::whereBetween('created_at', [$start, $end])->count();
                $positiveSurveys = CustomerSatisfactionSurvey::whereBetween('created_at', [$start, $end])
                    ->positive()
                    ->count();
                
                return $totalSurveys > 0 
                    ? round($positiveSurveys / $totalSurveys * 100, 2)
                    : 0;
                
            default:
                return 0;
        }
    }

    /**
     * Export quality control report.
     */
    public function exportReport(Request $request)
    {
        $reportType = $request->get('report_type', 'summary'); // summary, detailed, compliance, satisfaction
        
        switch ($reportType) {
            case 'summary':
                return $this->exportSummaryReport();
            case 'detailed':
                return $this->exportDetailedReport();
            case 'compliance':
                return $this->exportComplianceReport();
            case 'satisfaction':
                return $this->exportSatisfactionReport();
            default:
                return redirect()->back()
                    ->with('error', 'Invalid report type.');
        }
    }
    
    /**
     * Export summary report.
     */
    private function exportSummaryReport()
    {
        $qualityCheckStats = $this->getQualityCheckStats();
        $workOrderQualityStats = $this->getWorkOrderQualityStats();
        $complianceStats = $this->getComplianceStats();
        $customerSatisfactionStats = $this->getCustomerSatisfactionStats();
        
        $csvData = [];
        $csvData[] = ['Quality Control Summary Report', 'Generated: ' . now()->format('Y-m-d H:i:s')];
        $csvData[] = [];
        
        // Quality Checks
        $csvData[] = ['QUALITY CHECKS'];
        $csvData[] = ['Total Checks', $qualityCheckStats['total_checks']];
        $csvData[] = ['Active Checks', $qualityCheckStats['active_checks']];
        $csvData[] = ['Inactive Checks', $qualityCheckStats['inactive_checks']];
        $csvData[] = [];
        
        // Work Order Quality
        $csvData[] = ['WORK ORDER QUALITY'];
        $csvData[] = ['Total Quality Checks', $workOrderQualityStats['total_checks']];
        $csvData[] = ['Pass Rate', $workOrderQualityStats['pass_rate'] . '%'];
        $csvData[] = ['Average Pass Rate', $workOrderQualityStats['average_pass_rate'] . '%'];
        $csvData[] = ['Approved Checks', $workOrderQualityStats['approved_checks']];
        $csvData[] = ['Rejected Checks', $workOrderQualityStats['rejected_checks']];
        $csvData[] = [];
        
        // Compliance
        $csvData[] = ['COMPLIANCE'];
        $csvData[] = ['Total Documents', $complianceStats['total_documents']];
        $csvData[] = ['Active Documents', $complianceStats['active_documents']];
        $csvData[] = ['Expired Documents', $complianceStats['expired_documents']];
        $csvData[] = ['Expiring Soon', $complianceStats['expiring_soon_documents']];
        $csvData[] = ['Compliance Rate', $complianceStats['compliance_rate'] . '%'];
        $csvData[] = [];
        
        // Customer Satisfaction
        $csvData[] = ['CUSTOMER SATISFACTION'];
        $csvData[] = ['Total Surveys', $customerSatisfactionStats['total_surveys']];
        $csvData[] = ['Positive Surveys', $customerSatisfactionStats['positive_surveys']];
        $csvData[] = ['Negative Surveys', $customerSatisfactionStats['negative_surveys']];
        $csvData[] = ['Satisfaction Rate', $customerSatisfactionStats['satisfaction_rate'] . '%'];
        $csvData[] = ['Recommendation Rate', $customerSatisfactionStats['recommendation_rate'] . '%'];
        $csvData[] = ['Return Rate', $customerSatisfactionStats['return_rate'] . '%'];
        
        $filename = 'quality_control_summary_' . date('Y-m-d_H-i-s') . '.csv';
        return $this->generateCsvResponse($csvData, $filename);
    }
    
    /**
     * Export detailed report.
     */
    private function exportDetailedReport()
    {
        // This would be a more detailed report with individual records
        // For now, return a placeholder
        $csvData = [];
        $csvData[] = ['Detailed Quality Control Report', 'Generated: ' . now()->format('Y-m-d H:i:s')];
        $csvData[] = ['Note: Detailed report generation would include individual records from all quality control systems.'];
        
        $filename = 'quality_control_detailed_' . date('Y-m-d_H-i-s') . '.csv';
        return $this->generateCsvResponse($csvData, $filename);
    }
    
    /**
     * Export compliance report.
     */
    private function exportComplianceReport()
    {
        $documents = ComplianceDocument::with(['assignedUser', 'creator'])->get();
        
        $csvData = [];
        $csvData[] = ['Compliance Documents Report', 'Generated: ' . now()->format('Y-m-d H:i:s')];
        $csvData[] = [];
        $csvData[] = ['Document Name', 'Type', 'Document Number', 'Issuing Authority', 'Issue Date', 'Expiration Date', 'Status', 'Assigned To', 'File', 'Notes'];
        
        foreach ($documents as $doc) {
            $csvData[] = [
                $doc->document_name,
                $doc->getDocumentTypeLabel(),
                $doc->document_number,
                $doc->issuing_authority,
                $doc->issue_date->format('Y-m-d'),
                $doc->expiration_date ? $doc->expiration_date->format('Y-m-d') : 'N/A',
                $doc->getExpirationStatusLabel(),
                $doc->assignedUser ? $doc->assignedUser->name : 'Unassigned',
                $doc->hasFile() ? 'Yes' : 'No',
                $doc->notes,
            ];
        }
        
        $filename = 'compliance_documents_' . date('Y-m-d_H-i-s') . '.csv';
        return $this->generateCsvResponse($csvData, $filename);
    }
    
    /**
     * Export satisfaction report.
     */
    private function exportSatisfactionReport()
    {
        $surveys = CustomerSatisfactionSurvey::with(['workOrder', 'customer', 'technician'])->get();
        
        $csvData = [];
        $csvData[] = ['Customer Satisfaction Report', 'Generated: ' . now()->format('Y-m-d H:i:s')];
        $csvData[] = [];
        $csvData[] = [
            'Survey ID', 'Work Order', 'Customer', 'Technician', 'Overall Rating', 
            'Quality Rating', 'Timeliness Rating', 'Communication Rating', 
            'Cleanliness Rating', 'Value Rating', 'Average Rating', 'Sentiment',
            'Would Recommend', 'Would Return', 'Status', 'Positive Comments',
            'Improvement Suggestions', 'Follow-up Notes', 'Created Date'
        ];
        
        foreach ($surveys as $survey) {
            $csvData[] = [
                $survey->id,
                $survey->workOrder ? $survey->workOrder->work_order_number : 'N/A',
                $survey->customer ? $survey->customer->full_name : 'N/A',
                $survey->technician ? $survey->technician->name : 'N/A',
                $survey->overall_rating,
                $survey->quality_rating,
                $survey->timeliness_rating,
                $survey->communication_rating,
                $survey->cleanliness_rating,
                $survey->value_rating,
                $survey->calculateAverageRating(),
                $survey->getSentimentLabel(),
                $survey->would_recommend ? 'Yes' : 'No',
                $survey->would_return ? 'Yes' : 'No',
                $survey->getStatusLabel(),
                $survey->positive_comments,
                $survey->improvement_suggestions,
                $survey->follow_up_notes,
                $survey->created_at->format('Y-m-d H:i:s'),
            ];
        }
        
        $filename = 'customer_satisfaction_' . date('Y-m-d_H-i-s') . '.csv';
        return $this->generateCsvResponse($csvData, $filename);
    }
    
    /**
     * Generate CSV response.
     */
    private function generateCsvResponse(array $data, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}