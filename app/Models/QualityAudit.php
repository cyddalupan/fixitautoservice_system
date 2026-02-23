<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityAudit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'audit_number',
        'title',
        'description',
        'checklist_id',
        'work_order_id',
        'vehicle_id',
        'technician_id',
        'auditor_id',
        'audit_date',
        'audit_results',
        'total_score',
        'max_score',
        'percentage_score',
        'status',
        'findings',
        'recommendations',
        'follow_up_date',
        'created_by'
    ];

    protected $casts = [
        'audit_date' => 'date',
        'audit_results' => 'array',
        'total_score' => 'integer',
        'max_score' => 'integer',
        'percentage_score' => 'decimal:2',
        'follow_up_date' => 'date'
    ];

    protected $dates = [
        'deleted_at'
    ];

    /**
     * Get the checklist used for this audit
     */
    public function checklist()
    {
        return $this->belongsTo(QualityControlChecklist::class, 'checklist_id');
    }

    /**
     * Get the work order associated with this audit
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * Get the vehicle associated with this audit
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Get the technician who performed the work
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the auditor who performed the audit
     */
    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    /**
     * Get the user who created the audit
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get non-conformance reports from this audit
     */
    public function nonConformanceReports()
    {
        return $this->hasMany(NonConformanceReport::class, 'audit_id');
    }

    /**
     * Scope for audits by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for audits by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('audit_date', [$startDate, $endDate]);
    }

    /**
     * Scope for audits by technician
     */
    public function scopeByTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    /**
     * Scope for audits by auditor
     */
    public function scopeByAuditor($query, $auditorId)
    {
        return $query->where('auditor_id', $auditorId);
    }

    /**
     * Check if audit passed
     */
    public function passed()
    {
        return $this->percentage_score >= $this->checklist->passing_score;
    }

    /**
     * Get audit results with details
     */
    public function getDetailedResults()
    {
        $checklist = $this->checklist;
        $checklistItems = $checklist->getChecklistItemsWithScores();
        $auditResults = $this->audit_results ?? [];
        
        $detailedResults = [];
        $totalScore = 0;
        $maxScore = 0;
        
        foreach ($checklistItems['items'] as $index => $item) {
            $result = $auditResults[$index] ?? ['passed' => false, 'notes' => ''];
            
            $detailedResults[] = [
                'item' => $item['description'] ?? 'Unknown item',
                'requirement' => $item['requirement'] ?? '',
                'max_score' => $item['max_score'] ?? 0,
                'score' => $result['passed'] ? ($item['score'] ?? 0) : 0,
                'passed' => $result['passed'] ?? false,
                'notes' => $result['notes'] ?? '',
                'evidence' => $result['evidence'] ?? null
            ];
            
            $totalScore += $detailedResults[count($detailedResults) - 1]['score'];
            $maxScore += $item['max_score'] ?? 0;
        }
        
        return [
            'results' => $detailedResults,
            'summary' => [
                'total_score' => $totalScore,
                'max_score' => $maxScore,
                'percentage' => $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0,
                'passed' => $this->passed(),
                'checklist_passing_score' => $checklist->passing_score
            ]
        ];
    }

    /**
     * Generate audit report
     */
    public function generateReport()
    {
        $detailedResults = $this->getDetailedResults();
        
        return [
            'audit_info' => [
                'audit_number' => $this->audit_number,
                'title' => $this->title,
                'date' => $this->audit_date->format('Y-m-d'),
                'auditor' => $this->auditor->name ?? 'Unknown',
                'technician' => $this->technician->name ?? 'N/A',
                'vehicle' => $this->vehicle ? $this->vehicle->make . ' ' . $this->vehicle->model . ' (' . $this->vehicle->license_plate . ')' : 'N/A',
                'work_order' => $this->workOrder ? $this->workOrder->work_order_number : 'N/A'
            ],
            'results' => $detailedResults['results'],
            'summary' => $detailedResults['summary'],
            'findings' => $this->findings,
            'recommendations' => $this->recommendations,
            'status' => $this->status,
            'follow_up_date' => $this->follow_up_date ? $this->follow_up_date->format('Y-m-d') : null
        ];
    }

    /**
     * Update audit status
     */
    public function updateStatus($status, $notes = null)
    {
        $this->status = $status;
        
        if ($status === 'completed' && !$this->audit_date) {
            $this->audit_date = now();
        }
        
        if ($notes) {
            $this->findings = $notes;
        }
        
        $this->save();
        
        // If audit failed, create non-conformance report
        if ($status === 'failed' && $this->percentage_score < $this->checklist->passing_score) {
            $this->createNonConformanceReport();
        }
        
        return $this;
    }

    /**
     * Create non-conformance report for failed audit
     */
    protected function createNonConformanceReport()
    {
        $ncr = NonConformanceReport::create([
            'ncr_number' => 'NCR-' . strtoupper(uniqid()),
            'title' => 'Quality Audit Failure: ' . $this->title,
            'description' => 'Audit failed with score of ' . $this->percentage_score . '%. Minimum required: ' . $this->checklist->passing_score . '%.',
            'type' => 'internal',
            'severity' => $this->percentage_score < 50 ? 'critical' : ($this->percentage_score < 70 ? 'major' : 'minor'),
            'audit_id' => $this->id,
            'work_order_id' => $this->work_order_id,
            'vehicle_id' => $this->vehicle_id,
            'technician_id' => $this->technician_id,
            'reported_by' => $this->auditor_id,
            'reported_date' => now(),
            'status' => 'open',
            'created_by' => $this->created_by
        ]);
        
        return $ncr;
    }
}