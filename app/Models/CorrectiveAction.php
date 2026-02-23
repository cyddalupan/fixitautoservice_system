<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorrectiveAction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'action_number',
        'title',
        'description',
        'ncr_id',
        'standard_id',
        'action_type',
        'required_actions',
        'assigned_to',
        'assigned_by',
        'assigned_date',
        'due_date',
        'completed_date',
        'status',
        'completion_notes',
        'supporting_docs',
        'verified_by',
        'verification_date',
        'verification_notes',
        'effectiveness_verified',
        'effectiveness_check_date',
        'effectiveness_notes',
        'created_by'
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
        'completed_date' => 'date',
        'supporting_docs' => 'array',
        'verification_date' => 'date',
        'effectiveness_check_date' => 'date',
        'effectiveness_verified' => 'boolean'
    ];

    protected $dates = [
        'deleted_at'
    ];

    /**
     * Get the NCR that triggered this corrective action
     */
    public function nonConformanceReport()
    {
        return $this->belongsTo(NonConformanceReport::class, 'ncr_id');
    }

    /**
     * Get the compliance standard related to this action
     */
    public function standard()
    {
        return $this->belongsTo(ComplianceStandard::class, 'standard_id');
    }

    /**
     * Get the user assigned to complete this action
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who assigned this action
     */
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user who verified completion
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the user who created this action
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for actions by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for actions by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('action_type', $type);
    }

    /**
     * Scope for overdue actions
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->where('status', '!=', 'verified')
                     ->where('status', '!=', 'cancelled')
                     ->where('due_date', '<', now());
    }

    /**
     * Scope for actions assigned to specific user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope for actions requiring effectiveness verification
     */
    public function scopeRequiresEffectivenessCheck($query)
    {
        return $query->where('status', 'verified')
                     ->where('effectiveness_verified', false)
                     ->whereNotNull('completed_date')
                     ->where('completed_date', '<=', now()->subDays(30));
    }

    /**
     * Check if action is overdue
     */
    public function isOverdue()
    {
        return $this->due_date && 
               $this->due_date < now() && 
               !in_array($this->status, ['completed', 'verified', 'cancelled']);
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
     * Get days since assignment
     */
    public function daysSinceAssignment()
    {
        return now()->diffInDays($this->assigned_date);
    }

    /**
     * Update action status
     */
    public function updateStatus($status, $notes = null)
    {
        $oldStatus = $this->status;
        $this->status = $status;
        
        if ($status === 'completed' && !$this->completed_date) {
            $this->completed_date = now();
        }
        
        if ($notes) {
            $this->completion_notes = $notes;
        }
        
        $this->save();
        
        // Update NCR status if all actions are completed
        if ($status === 'completed' || $status === 'verified') {
            $this->updateNcrStatus();
        }
        
        return $this;
    }

    /**
     * Verify action completion
     */
    public function verifyCompletion($verifiedBy, $notes = null)
    {
        $this->status = 'verified';
        $this->verified_by = $verifiedBy;
        $this->verification_date = now();
        $this->verification_notes = $notes;
        $this->save();
        
        return $this;
    }

    /**
     * Verify effectiveness
     */
    public function verifyEffectiveness($verifiedBy, $notes = null)
    {
        $this->effectiveness_verified = true;
        $this->effectiveness_check_date = now();
        $this->effectiveness_notes = $notes;
        $this->save();
        
        return $this;
    }

    /**
     * Get action progress
     */
    public function getProgress()
    {
        $statusWeights = [
            'assigned' => 0,
            'in_progress' => 50,
            'completed' => 80,
            'verified' => 90,
            'effectiveness_verified' => 100
        ];
        
        $progress = $statusWeights[$this->status] ?? 0;
        
        // If effectiveness is verified, we're at 100%
        if ($this->effectiveness_verified) {
            $progress = 100;
        }
        
        return $progress;
    }

    /**
     * Get action timeline
     */
    public function getTimeline()
    {
        $timeline = [];
        
        $timeline[] = [
            'date' => $this->assigned_date,
            'event' => 'Assigned',
            'user' => $this->assigner ? $this->assigner->name : 'System'
        ];
        
        if ($this->completed_date) {
            $timeline[] = [
                'date' => $this->completed_date,
                'event' => 'Completed',
                'user' => $this->assignee ? $this->assignee->name : 'Unknown',
                'notes' => $this->completion_notes
            ];
        }
        
        if ($this->verification_date) {
            $timeline[] = [
                'date' => $this->verification_date,
                'event' => 'Verified',
                'user' => $this->verifier ? $this->verifier->name : 'Unknown',
                'notes' => $this->verification_notes
            ];
        }
        
        if ($this->effectiveness_check_date) {
            $timeline[] = [
                'date' => $this->effectiveness_check_date,
                'event' => 'Effectiveness Verified',
                'user' => 'Quality Manager',
                'notes' => $this->effectiveness_notes
            ];
        }
        
        // Sort by date
        usort($timeline, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });
        
        return $timeline;
    }

    /**
     * Update NCR status based on action completion
     */
    protected function updateNcrStatus()
    {
        $ncr = $this->nonConformanceReport;
        if (!$ncr) {
            return;
        }
        
        // Check if all actions for this NCR are completed or verified
        $incompleteActions = $ncr->correctiveActions()
            ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
            ->count();
        
        if ($incompleteActions === 0) {
            $ncr->updateStatus('resolved', 'All corrective actions completed.');
        }
    }

    /**
     * Get action summary for reporting
     */
    public function getSummary()
    {
        return [
            'action_number' => $this->action_number,
            'title' => $this->title,
            'action_type' => $this->action_type,
            'status' => $this->status,
            'assigned_date' => $this->assigned_date->format('Y-m-d'),
            'due_date' => $this->due_date->format('Y-m-d'),
            'completed_date' => $this->completed_date ? $this->completed_date->format('Y-m-d') : null,
            'progress' => $this->getProgress(),
            'is_overdue' => $this->isOverdue(),
            'days_overdue' => $this->daysOverdue(),
            'assigned_to' => $this->assignee ? $this->assignee->name : 'Unassigned',
            'ncr_number' => $this->nonConformanceReport ? $this->nonConformanceReport->ncr_number : 'N/A'
        ];
    }
}