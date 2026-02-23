<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class WorkOrderTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'task_name',
        'description',
        'task_status',
        'assigned_technician_id',
        'estimated_hours',
        'actual_hours',
        'start_time',
        'complete_time',
        'sequence',
        'is_critical_path',
        'technician_notes',
        'quality_check_notes',
        'quality_check_passed',
        'quality_check_by',
        'quality_check_at',
        'attachments',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'start_time' => 'datetime',
        'complete_time' => 'datetime',
        'quality_check_at' => 'datetime',
        'is_critical_path' => 'boolean',
        'quality_check_passed' => 'boolean',
        'attachments' => 'array',
    ];

    /**
     * Relationships
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function qualityChecker()
    {
        return $this->belongsTo(User::class, 'quality_check_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('task_status', 'pending');
    }

    public function scopeAssigned($query)
    {
        return $query->where('task_status', 'assigned');
    }

    public function scopeInProgress($query)
    {
        return $query->where('task_status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('task_status', 'completed');
    }

    public function scopeOnHold($query)
    {
        return $query->where('task_status', 'on_hold');
    }

    public function scopeCriticalPath($query)
    {
        return $query->where('is_critical_path', true);
    }

    public function scopeForTechnician($query, $technicianId)
    {
        return $query->where('assigned_technician_id', $technicianId);
    }

    /**
     * Accessors
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->task_status) {
            'pending' => 'secondary',
            'assigned' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'on_hold' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->start_time || $this->task_status === 'completed') {
            return false;
        }

        $estimatedCompletion = $this->start_time->addHours($this->estimated_hours);
        return $estimatedCompletion->lt(now());
    }

    public function getHoursOverdueAttribute(): ?float
    {
        if (!$this->is_overdue) {
            return null;
        }

        $estimatedCompletion = $this->start_time->addHours($this->estimated_hours);
        return now()->diffInHours($estimatedCompletion, true);
    }

    public function getCompletionPercentageAttribute(): int
    {
        if ($this->task_status === 'completed') {
            return 100;
        }

        if ($this->actual_hours > 0 && $this->estimated_hours > 0) {
            return min(99, (int) (($this->actual_hours / $this->estimated_hours) * 100));
        }

        return 0;
    }

    public function getEstimatedLaborCostAttribute(): float
    {
        if ($this->assignedTechnician && $this->estimated_hours > 0) {
            return $this->estimated_hours * $this->assignedTechnician->hourly_rate;
        }
        return 0;
    }

    public function getActualLaborCostAttribute(): float
    {
        if ($this->assignedTechnician && $this->actual_hours > 0) {
            return $this->actual_hours * $this->assignedTechnician->hourly_rate;
        }
        return 0;
    }

    public function getFormattedEstimatedHoursAttribute(): string
    {
        return number_format($this->estimated_hours, 2) . ' hrs';
    }

    public function getFormattedActualHoursAttribute(): string
    {
        return number_format($this->actual_hours, 2) . ' hrs';
    }

    /**
     * Methods
     */
    public function assignToTechnician(User $technician): bool
    {
        if ($technician->role !== 'technician') {
            return false;
        }

        $this->update([
            'assigned_technician_id' => $technician->id,
            'task_status' => 'assigned',
        ]);

        return true;
    }

    public function startTask(): bool
    {
        if (in_array($this->task_status, ['assigned', 'pending'])) {
            $this->update([
                'task_status' => 'in_progress',
                'start_time' => now(),
            ]);
            return true;
        }
        return false;
    }

    public function completeTask(float $actualHours = null): bool
    {
        if ($this->task_status === 'in_progress') {
            $updates = [
                'task_status' => 'completed',
                'complete_time' => now(),
            ];

            if ($actualHours !== null) {
                $updates['actual_hours'] = $actualHours;
            } elseif (!$this->actual_hours) {
                $updates['actual_hours'] = $this->estimated_hours;
            }

            $this->update($updates);
            return true;
        }
        return false;
    }

    public function markQualityCheck(bool $passed, User $checker, string $notes = null): bool
    {
        $this->update([
            'quality_check_passed' => $passed,
            'quality_check_by' => $checker->id,
            'quality_check_at' => now(),
            'quality_check_notes' => $notes,
        ]);

        return true;
    }

    public function updateProgress(float $hoursWorked): bool
    {
        if ($this->task_status !== 'in_progress') {
            return false;
        }

        $this->update([
            'actual_hours' => $hoursWorked,
        ]);

        return true;
    }

    public function getTaskSummary(): array
    {
        return [
            'task_name' => $this->task_name,
            'status' => $this->task_status,
            'status_color' => $this->status_color,
            'assigned_technician' => $this->assignedTechnician ? $this->assignedTechnician->name : 'Not assigned',
            'estimated_hours' => $this->formatted_estimated_hours,
            'actual_hours' => $this->formatted_actual_hours,
            'completion_percentage' => $this->completion_percentage,
            'is_critical_path' => $this->is_critical_path,
            'is_overdue' => $this->is_overdue,
            'hours_overdue' => $this->hours_overdue,
            'quality_check_passed' => $this->quality_check_passed,
            'start_time' => $this->start_time ? $this->start_time->format('M d, Y H:i') : 'Not started',
            'complete_time' => $this->complete_time ? $this->complete_time->format('M d, Y H:i') : null,
        ];
    }
}