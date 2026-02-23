<?php

namespace App\Http\Controllers;

use App\Models\ComplianceStandard;
use App\Models\ComplianceDocument;
use App\Models\QualityAudit;
use App\Models\NonConformanceReport;
use App\Models\CorrectiveAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComplianceController extends Controller
{
    /**
     * Display compliance dashboard
     */
    public function dashboard()
    {
        // Get compliance metrics
        $metrics = $this->getComplianceMetrics();
        
        // Get expiring standards
        $expiringStandards = ComplianceStandard::active()
            ->where('expiration_date', '<=', now()->addDays(30))
            ->where('expiration_date', '>', now())
            ->orderBy('expiration_date', 'asc')
            ->limit(10)
            ->get();
        
        // Get expired standards
        $expiredStandards = ComplianceStandard::where('expiration_date', '<', now())
            ->orderBy('expiration_date', 'desc')
            ->limit(10)
            ->get();
        
        // Get recent compliance audits
        $recentAudits = QualityAudit::with(['checklist', 'technician', 'vehicle'])
            ->whereHas('checklist', function($query) {
                $query->where('service_type', 'like', '%compliance%');
            })
            ->orderBy('audit_date', 'desc')
            ->limit(10)
            ->get();
        
        // Get compliance documents needing renewal
        $documentsNeedingRenewal = ComplianceDocument::where('expiry_date', '<=', now()->addDays(60))
            ->where('expiry_date', '>', now())
            ->with(['standard', 'uploadedBy'])
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get();
        
        return view('compliance.dashboard', compact(
            'metrics',
            'expiringStandards',
            'expiredStandards',
            'recentAudits',
            'documentsNeedingRenewal'
        ));
    }
    
    /**
     * Display compliance standards management
     */
    public function standards(Request $request)
    {
        $query = ComplianceStandard::with(['creator', 'updater']);
        
        // Apply filters
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('is_mandatory')) {
            $query->where('is_mandatory', $request->is_mandatory);
        }
        
        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'expired':
                    $query->where('expiration_date', '<', now());
                    break;
                case 'expiring':
                    $query->where('expiration_date', '<=', now()->addDays(30))
                          ->where('expiration_date', '>', now());
                    break;
            }
        }
        
        $standards = $query->orderBy('code')->paginate(20);
        
        $categories = ComplianceStandard::distinct('category')
            ->pluck('category')
            ->map(function($category) {
                return [
                    'value' => $category,
                    'label' => ucfirst($category)
                ];
            });
        
        return view('compliance.standards', compact('standards', 'categories'));
    }
    
    /**
     * Show form to create new standard
     */
    public function createStandard()
    {
        $categories = $this->getStandardCategories();
        return view('compliance.standards-create', compact('categories'));
    }
    
    /**
     * Store new standard
     */
    public function storeStandard(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'requirements' => 'nullable|string',
            'effective_date' => 'required|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'is_mandatory' => 'boolean',
            'revision_number' => 'integer|min:1'
        ]);
        
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        $validated['revision_number'] = $validated['revision_number'] ?? 1;
        
        $standard = ComplianceStandard::create($validated);
        
        return redirect()->route('compliance.standards')
            ->with('success', 'Compliance standard created successfully.');
    }
    
    /**
     * Show standard details
     */
    public function showStandard($id)
    {
        $standard = ComplianceStandard::with(['creator', 'updater', 'complianceDocuments', 'correctiveActions'])->findOrFail($id);
        $complianceStats = $this->getStandardComplianceStats($standard);
        
        return view('compliance.standards-show', compact('standard', 'complianceStats'));
    }
    
    /**
     * Show form to edit standard
     */
    public function editStandard($id)
    {
        $standard = ComplianceStandard::findOrFail($id);
        $categories = $this->getStandardCategories();
        
        return view('compliance.standards-edit', compact('standard', 'categories'));
    }
    
    /**
     * Update standard
     */
    public function updateStandard(Request $request, $id)
    {
        $standard = ComplianceStandard::findOrFail($id);
        
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'requirements' => 'nullable|string',
            'effective_date' => 'required|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'is_mandatory' => 'boolean'
        ]);
        
        $validated['updated_by'] = auth()->id();
        
        $standard->update($validated);
        
        return redirect()->route('compliance.standards.show', $standard->id)
            ->with('success', 'Compliance standard updated successfully.');
    }
    
    /**
     * Create new revision of standard
     */
    public function createStandardRevision(Request $request, $id)
    {
        $standard = ComplianceStandard::findOrFail($id);
        
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'requirements' => 'nullable|string',
            'effective_date' => 'required|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'is_mandatory' => 'boolean'
        ]);
        
        $newStandard = $standard->createNewRevision($validated);
        
        return redirect()->route('compliance.standards.show', $newStandard->id)
            ->with('success', 'New standard revision created successfully.');
    }
    
    /**
     * Display compliance documents
     */
    public function documents(Request $request)
    {
        $query = ComplianceDocument::with(['standard', 'uploadedBy']);
        
        // Apply filters
        if ($request->has('standard_id')) {
            $query->where('standard_id', $request->standard_id);
        }
        
        if ($request->has('document_type')) {
            $query->where('document_type', $request->document_type);
        }
        
        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('expiry_date', '>', now())
                          ->orWhereNull('expiry_date');
                    break;
                case 'expired':
                    $query->where('expiry_date', '<', now());
                    break;
                case 'expiring':
                    $query->where('expiry_date', '<=', now()->addDays(30))
                          ->where('expiry_date', '>', now());
                    break;
            }
        }
        
        $documents = $query->orderBy('expiry_date', 'asc')->paginate(20);
        
        $standards = ComplianceStandard::active()->get();
        $documentTypes = $this->getDocumentTypes();
        
        return view('compliance.documents', compact('documents', 'standards', 'documentTypes'));
    }
    
    /**
     * Show form to upload document
     */
    public function createDocument()
    {
        $standards = ComplianceStandard::active()->get();
        $documentTypes = $this->getDocumentTypes();
        
        return view('compliance.documents-create', compact('standards', 'documentTypes'));
    }
    
    /**
     * Store uploaded document
     */
    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'standard_id' => 'required|exists:compliance_standards,id',
            'document_type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'version' => 'nullable|string|max:50'
        ]);
        
        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('compliance_documents', $filename, 'public');
            
            $validated['file_path'] = $path;
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_size'] = $file->getSize();
            $validated['mime_type'] = $file->getMimeType();
        }
        
        $validated['uploaded_by'] = auth()->id();
        
        $document = ComplianceDocument::create($validated);
        
        return redirect()->route('compliance.documents')
            ->with('success', 'Compliance document uploaded successfully.');
    }
    
    /**
     * Show document details
     */
    public function showDocument($id)
    {
        $document = ComplianceDocument::with(['standard', 'uploadedBy'])->findOrFail($id);
        
        return view('compliance.documents-show', compact('document'));
    }
    
    /**
     * Download document
     */
    public function downloadDocument($id)
    {
        $document = ComplianceDocument::findOrFail($id);
        
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found');
        }
        
        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
    
    /**
     * Update document
     */
    public function updateDocument(Request $request, $id)
    {
        $document = ComplianceDocument::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'version' => 'nullable|string|max:50'
        ]);
        
        // Handle file update if provided
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
            ]);
            
            // Delete old file
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('compliance_documents', $filename, 'public');
            
            $validated['file_path'] = $path;
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_size'] = $file->getSize();
            $validated['mime_type'] = $file->getMimeType();
        }
        
        $document->update($validated);
        
        return redirect()->route('compliance.documents.show', $document->id)
            ->with('success', 'Compliance document updated successfully.');
    }
    
    /**
     * Display compliance reports
     */
    public function reports(Request $request)
    {
        $reportType = $request->get('type', 'summary');
        
        switch ($reportType) {
            case 'summary':
                $data = $this->generateComplianceSummaryReport($request);
                $view = 'compliance.reports-summary';
                break;
            case 'audits':
                $data = $this->generateComplianceAuditReport($request);
                $view = 'compliance.reports-audits';
                break;
            case 'ncrs':
                $data = $this->generateComplianceNcrReport($request);
                $view = 'compliance.reports-ncrs';
                break;
            case 'documents':
                $data = $this->generateComplianceDocumentReport($request);
                $view = 'compliance.reports-documents';
                break;
            default:
                abort(404, 'Report type not found');
        }
        
        return view($view, compact('data', 'reportType'));
    }
    
    /**
     * Export compliance data
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'standards');
        $format = $request->get('format', 'csv');
        
        switch ($type) {
            case 'standards':
                $data = $this->exportStandards($request);
                $filename = 'compliance_standards_' . date('Y-m-d') . '.' . $format;
                break;
            case 'documents':
                $data = $this->exportDocuments($request);
                $filename = 'compliance_documents_' . date('Y-m-d') . '.' . $format;
                break;
            case 'audits':
                $data = $this->exportComplianceAudits($request);
                $filename = 'compliance_audits_' . date('Y-m-d') . '.' . $format;
                break;
            default:
                return response()->json(['error' => 'Invalid export type'], 400);
        }
        
        if ($format === 'csv') {
            return $this->exportToCsv($data, $filename);
        } else {
            return response()->json($data);
        }
    }
    
    /**
     * Send compliance alerts
     */
    public function sendAlerts()
    {
        // Get standards expiring in next 30 days
        $expiringStandards = ComplianceStandard::active()
            ->where('expiration_date', '<=', now()->addDays(30))
            ->where('expiration_date', '>', now())
            ->get();
        
        // Get documents expiring in next 30 days
        $expiringDocuments = ComplianceDocument::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->get();
        
        // Get overdue corrective actions
        $overdueActions = CorrectiveAction::overdue()->get();
        
        // Prepare alert data
        $alerts = [
            'expiring_standards' => $expiringStandards->count(),
            'expiring_documents' => $expiringDocuments->count(),
            'overdue_actions' => $overdueActions->count(),
            'total_alerts' => $expiringStandards->count() + $expiringDocuments->count() + $overdueActions->count()
        ];
        
        // In a real application, you would send emails or notifications here
        // For now, we'll just return the alert data
        
        return response()->json([
            'success' => true,
            'message' => 'Compliance alerts generated',
            'alerts' => $alerts
        ]);
    }
    
    /**
     * Get compliance metrics for dashboard
     */
    private function getComplianceMetrics()
    {
        // Standards metrics
        $standardsMetrics = ComplianceStandard::select(
            DB::raw('COUNT(*) as total_standards'),
            DB::raw('SUM(CASE WHEN expiration_date IS NULL OR expiration_date > NOW() THEN 1 ELSE 0 END) as active_standards'),
            DB::raw('SUM(CASE WHEN expiration_date <= NOW() THEN 1 ELSE 0 END) as expired_standards'),
            DB::raw('SUM(CASE WHEN expiration_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND expiration_date > NOW() THEN 1 ELSE 0 END) as expiring_standards')
        )->first();
        
        // Documents metrics
        $documentsMetrics = ComplianceDocument::select(
            DB::raw('COUNT(*) as total_documents'),
            DB::raw('SUM(CASE WHEN expiry_date IS NULL OR expiry_date > NOW() THEN 1 ELSE 0 END) as active_documents'),
            DB::raw('SUM(CASE WHEN expiry_date <= NOW() THEN 1 ELSE 0 END) as expired_documents'),
            DB::raw('SUM(CASE WHEN expiry_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND expiry_date > NOW() THEN 1 ELSE 0 END) as expiring_documents')
        )->first();
        
        // Compliance audit metrics
        $auditMetrics = QualityAudit::whereHas('checklist', function($query) {
                $query->where('service_type', 'like', '%compliance%');
            })
            ->select(
                DB::raw('COUNT(*) as total_audits'),
                DB::raw('AVG(percentage_score) as avg_score'),
                DB::raw('SUM(CASE WHEN percentage_score >= (SELECT passing_score FROM quality_control_checklists WHERE id = quality_audits.checklist_id) THEN 1 ELSE 0 END) as passed_audits')
            )->first();
        
        // Calculate compliance rate
        $complianceRate = $auditMetrics->total_audits > 0 
            ? ($auditMetrics->passed_audits / $auditMetrics->total_audits) * 100 
            : 100; // If no audits, assume 100% compliance
        
        return [
            'standards' => [
                'total' => $standardsMetrics->total_standards ?? 0,
                'active' => $standardsMetrics->active_standards ?? 0,
                'expired' => $standardsMetrics->expired_standards ?? 0,
                'expiring' => $standardsMetrics->expiring_standards ?? 0,
                'compliance_rate' => round($complianceRate, 2)
            ],
            'documents' => [
                'total' => $documentsMetrics->total_documents ?? 0,
                'active' => $documentsMetrics->active_documents ?? 0,
                'expired' => $documentsMetrics->expired_documents ?? 0,
                'expiring' => $documentsMetrics->expiring_documents ?? 0
            ],
            'audits' => [
                'total' => $auditMetrics->total_audits ?? 0,
                'passed' => $auditMetrics->passed_audits ?? 0,
                'avg_score' => round($auditMetrics->avg_score ?? 0, 2)
            ]
        ];
    }
    
    /**
     * Get standard compliance statistics
     */
    private function getStandardComplianceStats(ComplianceStandard $standard)
    {
        $thirtyDaysAgo = now()->subDays(30);
        
        // Get related audits
        $auditStats = QualityAudit::whereHas('checklist', function($query) use ($standard) {
                $query->where('service_type', 'like', '%compliance%')
                      ->where('name', 'like', '%' . $standard->code . '%');
            })
            ->select(
                DB::raw('COUNT(*) as total_audits'),
                DB::raw('AVG(percentage_score) as avg_score'),
                DB::raw('MIN(percentage_score) as min_score'),
                DB::raw('MAX(percentage_score) as max_score'),
                DB::raw('SUM(CASE WHEN percentage_score >= 80 THEN 1 ELSE 0 END) as compliant_audits')
            )
            ->where('audit_date', '>=', $thirtyDaysAgo)
            ->first();
        
        // Get related NCRs
        $ncrStats = NonConformanceReport::where('type', 'like', '%' . $standard->code . '%')
            ->orWhere('title', 'like', '%' . $standard->name . '%')
            ->select(
                DB::raw('COUNT(*) as total_ncrs'),
                DB::raw('SUM(CASE WHEN severity = "critical" THEN 1 ELSE 0 END) as critical_ncrs'),
                DB::raw('SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END) as open_ncrs')
            )
            ->where('reported_date', '>=', $thirtyDaysAgo)
            ->first();
        
        $complianceRate = $auditStats->total_audits > 0 
            ? ($auditStats->compliant_audits / $auditStats->total_audits) * 100 
            : 100;
        
        return [
            'audits' => [
                'total' => $auditStats->total_audits ?? 0,
                'compliant' => $auditStats->compliant_audits ?? 0,
                'avg_score' => round($auditStats->avg_score ?? 0, 2),
                'min_score' => round($auditStats->min_score ?? 0, 2),
                'max_score' => round($auditStats->max_score ?? 0, 2),
                'compliance_rate' => round($complianceRate, 2)
            ],
            'ncrs' => [
                'total' => $ncrStats->total_ncrs ?? 0,
                'critical' => $ncrStats->critical_ncrs ?? 0,
                'open' => $ncrStats->open_ncrs ?? 0
            ]
        ];
    }
    
    /**
     * Generate compliance summary report
     */
    private function generateComplianceSummaryReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        // Standards summary
        $standardsSummary = ComplianceStandard::select(
            'category',
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN expiration_date IS NULL OR expiration_date > NOW() THEN 1 ELSE 0 END) as active'),
            DB::raw('SUM(CASE WHEN expiration_date <= NOW() THEN 1 ELSE 0 END) as expired'),
            DB::raw('SUM(CASE WHEN expiration_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND expiration_date > NOW() THEN 1 ELSE 0 END) as expiring')
        )
        ->groupBy('category')
        ->get();
        
        // Audit summary
        $auditSummary = QualityAudit::whereHas('checklist', function($query) {
                $query->where('service_type', 'like', '%compliance%');
            })
            ->whereBetween('audit_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(audit_date) as audit_day'),
                DB::raw('COUNT(*) as total_audits'),
                DB::raw('AVG(percentage_score) as avg_score'),
                DB::raw('SUM(CASE WHEN percentage_score >= 80 THEN 1 ELSE 0 END) as compliant_audits')
            )
            ->groupBy('audit_day')
            ->orderBy('audit_day')
            ->get();
        
        // NCR summary
        $ncrSummary = NonConformanceReport::whereBetween('reported_date', [$startDate, $endDate])
            ->select(
                'severity',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END) as open'),
                DB::raw('AVG(daysOpen()) as avg_days_open')
            )
            ->groupBy('severity')
            ->get();
        
        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'standards_summary' => $standardsSummary,
            'audit_summary' => $auditSummary,
            'ncr_summary' => $ncrSummary,
            'metrics' => $this->getComplianceMetrics()
        ];
    }
    
    /**
     * Generate compliance audit report
     */
    private function generateComplianceAuditReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $audits = QualityAudit::whereHas('checklist', function($query) {
                $query->where('service_type', 'like', '%compliance%');
            })
            ->with(['checklist', 'technician', 'auditor', 'vehicle'])
            ->whereBetween('audit_date', [$startDate, $endDate])
            ->orderBy('audit_date', 'desc')
            ->get();
        
        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'audits' => $audits,
            'summary' => [
                'total_audits' => $audits->count(),
                'avg_score' => round($audits->avg('percentage_score') ?? 0, 2),
                'compliant_audits' => $audits->where('percentage_score', '>=', 80)->count(),
                'compliance_rate' => $audits->count() > 0 
                    ? round(($audits->where('percentage_score', '>=', 80)->count() / $audits->count()) * 100, 2)
                    : 0
            ]
        ];
    }
    
    /**
     * Generate compliance NCR report
     */
    private function generateComplianceNcrReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $ncrs = NonConformanceReport::with(['technician', 'assignee', 'vehicle'])
            ->whereBetween('reported_date', [$startDate, $endDate])
            ->orderBy('reported_date', 'desc')
            ->get();
        
        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'ncrs' => $ncrs,
            'summary' => [
                'total_ncrs' => $ncrs->count(),
                'by_severity' => $ncrs->groupBy('severity')->map->count(),
                'by_status' => $ncrs->groupBy('status')->map->count(),
                'by_type' => $ncrs->groupBy('type')->map->count(),
                'avg_days_open' => round($ncrs->avg(function($ncr) {
                    return $ncr->daysOpen();
                }) ?? 0, 2)
            ]
        ];
    }
    
    /**
     * Generate compliance document report
     */
    private function generateComplianceDocumentReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $documents = ComplianceDocument::with(['standard', 'uploadedBy'])
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->orderBy('expiry_date', 'asc')
            ->get();
        
        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'documents' => $documents,
            'summary' => [
                'total_documents' => $documents->count(),
                'by_type' => $documents->groupBy('document_type')->map->count(),
                'by_status' => [
                    'active' => $documents->where('expiry_date', '>', now())->count(),
                    'expired' => $documents->where('expiry_date', '<=', now())->count(),
                    'expiring' => $documents->where('expiry_date', '<=', now()->addDays(30))
                                           ->where('expiry_date', '>', now())
                                           ->count()
                ]
            ]
        ];
    }
    
    /**
     * Export standards data
     */
    private function exportStandards(Request $request)
    {
        $query = ComplianceStandard::with(['creator', 'updater']);
        
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        $standards = $query->orderBy('code')->get();
        
        return $standards->map(function($standard) {
            return [
                'Code' => $standard->code,
                'Name' => $standard->name,
                'Category' => $standard->category,
                'Effective Date' => $standard->effective_date->format('Y-m-d'),
                'Expiration Date' => $standard->expiration_date ? $standard->expiration_date->format('Y-m-d') : 'N/A',
                'Mandatory' => $standard->is_mandatory ? 'Yes' : 'No',
                'Status' => $standard->isActive() ? 'Active' : ($standard->isExpired() ? 'Expired' : 'Expiring'),
                'Days to Expiry' => $standard->daysToExpiry(),
                'Created By' => $standard->creator->name ?? 'System',
                'Last Updated' => $standard->updated_at->format('Y-m-d H:i')
            ];
        });
    }
    
    /**
     * Export documents data
     */
    private function exportDocuments(Request $request)
    {
        $query = ComplianceDocument::with(['standard', 'uploadedBy']);
        
        if ($request->has('standard_id')) {
            $query->where('standard_id', $request->standard_id);
        }
        
        $documents = $query->orderBy('expiry_date', 'asc')->get();
        
        return $documents->map(function($document) {
            return [
                'Document ID' => $document->id,
                'Title' => $document->title,
                'Standard' => $document->standard->code ?? 'N/A',
                'Document Type' => $document->document_type,
                'Issue Date' => $document->issue_date->format('Y-m-d'),
                'Expiry Date' => $document->expiry_date ? $document->expiry_date->format('Y-m-d') : 'N/A',
                'Status' => $document->isActive() ? 'Active' : ($document->isExpired() ? 'Expired' : 'Expiring'),
                'Days to Expiry' => $document->daysToExpiry(),
                'File Name' => $document->file_name,
                'File Size' => round($document->file_size / 1024, 2) . ' KB',
                'Uploaded By' => $document->uploadedBy->name ?? 'System',
                'Uploaded Date' => $document->created_at->format('Y-m-d H:i')
            ];
        });
    }
    
    /**
     * Export compliance audits data
     */
    private function exportComplianceAudits(Request $request)
    {
        $query = QualityAudit::whereHas('checklist', function($query) {
                $query->where('service_type', 'like', '%compliance%');
            })
            ->with(['checklist', 'technician', 'auditor', 'vehicle']);
        
        if ($request->has('start_date')) {
            $query->where('audit_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('audit_date', '<=', $request->end_date);
        }
        
        $audits = $query->orderBy('audit_date', 'desc')->get();
        
        return $audits->map(function($audit) {
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
                'Compliant' => $audit->percentage_score >= 80 ? 'Yes' : 'No',
                'Findings' => $audit->findings ? 'Yes' : 'No',
                'Follow-up Required' => $audit->follow_up_date ? 'Yes' : 'No'
            ];
        });
    }
    
    /**
     * Export data to CSV
     */
    private function exportToCsv($data, $filename)
    {
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
     * Get standard categories for dropdown
     */
    private function getStandardCategories()
    {
        return [
            ['value' => 'safety', 'label' => 'Safety'],
            ['value' => 'environmental', 'label' => 'Environmental'],
            ['value' => 'quality', 'label' => 'Quality'],
            ['value' => 'technical', 'label' => 'Technical'],
            ['value' => 'administrative', 'label' => 'Administrative'],
            ['value' => 'legal', 'label' => 'Legal'],
            ['value' => 'operational', 'label' => 'Operational'],
            ['value' => 'financial', 'label' => 'Financial']
        ];
    }
    
    /**
     * Get document types for dropdown
     */
    private function getDocumentTypes()
    {
        return [
            ['value' => 'certificate', 'label' => 'Certificate'],
            ['value' => 'license', 'label' => 'License'],
            ['value' => 'permit', 'label' => 'Permit'],
            ['value' => 'policy', 'label' => 'Policy'],
            ['value' => 'procedure', 'label' => 'Procedure'],
            ['value' => 'report', 'label' => 'Report'],
            ['value' => 'manual', 'label' => 'Manual'],
            ['value' => 'guideline', 'label' => 'Guideline'],
            ['value' => 'standard', 'label' => 'Standard'],
            ['value' => 'specification', 'label' => 'Specification']
        ];
    }
}
