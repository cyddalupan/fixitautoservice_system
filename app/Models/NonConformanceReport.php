<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonConformanceReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ncr_number',
        'title',
        'description',
        'type',
        'severity',
        'audit_id',
        'work_order_id',
        'vehicle_id',
        'technician_id',
        'reported_by',
        'evidence',
        'status',
        'reported_date',
        'due_date',
        'resolved_date',
        'root_cause',
        'containment_actions',
        'assigned_to',
        'created_by'
    ];

    protected $casts = [
        'evidence' => 'array',
        'reported_date' => 'date',
        'due_date' => 'date',
        'resolved_date' => 'date'
    ];

    protected $dates = [
        'deleted_at'
    ];

    /**
     * Get the audit that generated this NCR
     */
    public function audit()
    {
        return $this->belongsTo(QualityAudit::class, 'audit_id');
    }

    /**
     * Get the work order associated with this NCR
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * Get the vehicle associated with this NCR
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Get the technician associated with this NCR
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the user who reported the NCR
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the user assigned to handle this NCR
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created the NCR
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get corrective actions for this NCR
     */
    public function correctiveActions()
    {
        return $this->hasMany(CorrectiveAction::class, 'ncr_id');
    }

    /**
     * Scope for NCRs by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for NCRs by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for NCRs by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for overdue NCRs
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'closed')
                     ->where('status', '!=', 'resolved')
                     ->whereNotNull('due_date')
                     ->where('due_date', '<', now());
    }

    /**
     * Scope for NCRs assigned to specific user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Check if NCR is overdue
     */
    public function isOverdue()
    {
        return $this->due_date && 
               $this->due_date < now() && 
               !in_array($this->status, ['closed', 'resolved']);
    }

    /**
     * Get days overdue
     */
    public function daysOverdue()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return now()->diffInDays($this->due_date);
    }

    /**
     * Get days open
     */
    public function daysOpen()
    {
        return now()->diffInDays($this->reported_date);
    }

    /**
     * Update NCR status
     */
    public function updateStatus($status, $notes = null)
    {
        $oldStatus = $this->status;
        $this->status = $status;
        
        if ($status === 'resolved' && !$this->resolved_date) {
            $this->resolved_date = now();
        }
        
        if ($notes) {
            $this->containment_actions = $notes;
        }
        
        $this->save();
        
        // Log status change
        $this->logStatusChange($oldStatus, $status);
        
        return $this;
    }

    /**
     * Assign NCR to user
     */
    public function assignTo($userId, $dueDate = null)
    {
        $this->assigned_to = $userId;
        
        if ($dueDate) {
            $this->due_date = $dueDate;
        }
        
        $this->save();
        
        // Log assignment
        $this->logAssignment($userId);
        
        return $this;
    }

    /**
     * Add root cause analysis
     */
    public function addRootCause($rootCause)
    {
        $this->root_cause = $rootCause;
        $this->status = 'action_required';
        $this->save();
        
        return $this;
    }

    /**
     * Get NCR summary for reporting
     */
    public function getSummary()
    {
        return [
            'ncr_number' => $this->ncr_number,
            'title' => $this->title,
            'type' => $this->type,
            'severity' => $this->severity,
            'status' => $this->status,
            'reported_date' => $this->reported_date->format('Y-m-d'),
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null,
            'days_open' => $this->daysOpen(),
            'is_overdue' => $this->isOverdue(),
            'days_overdue' => $this->daysOverdue(),
            'assigned_to' => $this->assignee ? $this->assignee->name : 'Unassigned',
            'reported_by' => $this->reporter ? $this->reporter->name : 'Unknown'
        ];
    }

    /**
     * Log status change
     */
    protected function logStatusChange($oldStatus, $newStatus)
    {
        // Implementation would depend on your logging system
        // This could be an audit trail entry or activity log
    }

    /**
     * Log assignment
     */
    protected function logAssignment($userId)
    {
        // Implementation would depend on your logging system
    }
}