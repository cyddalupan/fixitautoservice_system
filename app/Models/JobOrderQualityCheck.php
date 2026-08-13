<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderQualityCheck extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'work_order_id',
        'quality_check_id',
        'technician_id',
        'supervisor_id',
        'status',
        'results',
        'notes',
        'photos',
        'completed_at',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'results' => 'array',
        'photos' => 'array',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PASSED = 'passed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_NEEDS_REWORK = 'needs_rework';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Get all statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_PASSED => 'Passed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_NEEDS_REWORK => 'Needs Rework',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get status color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING, self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_PASSED, self::STATUS_APPROVED => 'success',
            self::STATUS_FAILED, self::STATUS_REJECTED => 'danger',
            self::STATUS_NEEDS_REWORK => 'info',
            default => 'secondary',
        };
    }

    /**
     * Check if quality check is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Check if quality check is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_PASSED, self::STATUS_FAILED, self::STATUS_NEEDS_REWORK]);
    }

    /**
     * Check if quality check is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if quality check needs supervisor approval
     */
    public function needsSupervisorApproval(): bool
    {
        return in_array($this->status, [self::STATUS_PASSED, self::STATUS_FAILED, self::STATUS_NEEDS_REWORK]);
    }

    /**
     * Calculate pass rate
     */
    public function calculatePassRate(): float
    {
        if (empty($this->results) || !is_array($this->results)) {
            return 0;
        }

        $totalItems = count($this->results);
        $passedItems = 0;

        foreach ($this->results as $result) {
            if (($result['passed'] ?? false) === true) {
                $passedItems++;
            }
        }

        return $totalItems > 0 ? round(($passedItems / $totalItems) * 100, 2) : 0;
    }

    /**
     * Get failed items
     */
    public function getFailedItems(): array
    {
        if (empty($this->results) || !is_array($this->results)) {
            return [];
        }

        $failedItems = [];
        foreach ($this->results as $index => $result) {
            if (($result['passed'] ?? false) === false) {
                $failedItems[] = [
                    'index' => $index,
                    'item' => $result['item'] ?? 'Unknown',
                    'notes' => $result['notes'] ?? '',
                ];
            }
        }

        return $failedItems;
    }

    /**
     * Mark as in progress
     */
    public function markAsInProgress(): bool
    {
        if ($this->status === self::STATUS_PENDING) {
            $this->status = self::STATUS_IN_PROGRESS;
            return $this->save();
        }
        return false;
    }

    /**
     * Complete quality check
     */
    public function complete(array $results, string $notes = null, array $photos = null): bool
    {
        $passRate = $this->calculatePassRateFromResults($results);
        
        $this->results = $results;
        $this->notes = $notes;
        $this->photos = $photos;
        $this->completed_at = now();
        
        if ($passRate >= 90) {
            $this->status = self::STATUS_PASSED;
        } elseif ($passRate >= 70) {
            $this->status = self::STATUS_NEEDS_REWORK;
        } else {
            $this->status = self::STATUS_FAILED;
        }

        return $this->save();
    }

    /**
     * Approve quality check
     */
    public function approve(int $supervisorId, string $notes = null): bool
    {
        if ($this->needsSupervisorApproval()) {
            $this->supervisor_id = $supervisorId;
            $this->status = self::STATUS_APPROVED;
            $this->approved_at = now();
            if ($notes) {
                $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "Supervisor Approval: " . $notes;
            }
            return $this->save();
        }
        return false;
    }

    /**
     * Reject quality check
     */
    public function reject(int $supervisorId, string $reason): bool
    {
        if ($this->needsSupervisorApproval()) {
            $this->supervisor_id = $supervisorId;
            $this->status = self::STATUS_REJECTED;
            $this->approved_at = now();
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "Rejection Reason: " . $reason;
            return $this->save();
        }
        return false;
    }

    /**
     * Calculate pass rate from results
     */
    private function calculatePassRateFromResults(array $results): float
    {
        $totalItems = count($results);
        $passedItems = 0;

        foreach ($results as $result) {
            if (($result['passed'] ?? false) === true) {
                $passedItems++;
            }
        }

        return $totalItems > 0 ? ($passedItems / $totalItems) * 100 : 0;
    }

    /**
     * Relationship: Work order
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Relationship: Quality check template
     */
    public function qualityCheck(): BelongsTo
    {
        return $this->belongsTo(QualityCheck::class);
    }

    /**
     * Relationship: Technician
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Relationship: Supervisor
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Scope: Pending approval
     */
    public function scopePendingApproval($query)
    {
        return $query->whereIn('status', [self::STATUS_PASSED, self::STATUS_FAILED, self::STATUS_NEEDS_REWORK]);
    }

    /**
     * Scope: By work order
     */
    public function scopeByWorkOrder($query, $workOrderId)
    {
        return $query->where('work_order_id', $workOrderId);
    }

    /**
     * Scope: By technician
     */
    public function scopeByTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    /**
     * Scope: By supervisor
     */
    public function scopeBySupervisor($query, $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    /**
     * Scope: Completed
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [self::STATUS_PASSED, self::STATUS_FAILED, self::STATUS_NEEDS_REWORK, self::STATUS_APPROVED, self::STATUS_REJECTED]);
    }

    /**
     * Scope: Needs rework
     */
    public function scopeNeedsRework($query)
    {
        return $query->where('status', self::STATUS_NEEDS_REWORK);
    }
}