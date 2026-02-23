<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'technician_time_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'technician_id',
        'work_order_id',
        'appointment_id',
        'log_type',
        'log_time',
        'location',
        'device_id',
        'ip_address',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'notes',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'log_time' => 'datetime',
        'approved_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Log type constants
     */
    public const LOG_TYPE_CLOCK_IN = 'clock_in';
    public const LOG_TYPE_CLOCK_OUT = 'clock_out';
    public const LOG_TYPE_BREAK_START = 'break_start';
    public const LOG_TYPE_BREAK_END = 'break_end';
    public const LOG_TYPE_LUNCH_START = 'lunch_start';
    public const LOG_TYPE_LUNCH_END = 'lunch_end';
    public const LOG_TYPE_JOB_START = 'job_start';
    public const LOG_TYPE_JOB_END = 'job_end';
    public const LOG_TYPE_TRAINING = 'training';
    public const LOG_TYPE_MEETING = 'meeting';
    public const LOG_TYPE_MAINTENANCE = 'maintenance';
    public const LOG_TYPE_OTHER = 'other';

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ADJUSTED = 'adjusted';

    /**
     * Get the log types as an array
     */
    public static function getLogTypes(): array
    {
        return [
            self::LOG_TYPE_CLOCK_IN => 'Clock In',
            self::LOG_TYPE_CLOCK_OUT => 'Clock Out',
            self::LOG_TYPE_BREAK_START => 'Break Start',
            self::LOG_TYPE_BREAK_END => 'Break End',
            self::LOG_TYPE_LUNCH_START => 'Lunch Start',
            self::LOG_TYPE_LUNCH_END => 'Lunch End',
            self::LOG_TYPE_JOB_START => 'Job Start',
            self::LOG_TYPE_JOB_END => 'Job End',
            self::LOG_TYPE_TRAINING => 'Training',
            self::LOG_TYPE_MEETING => 'Meeting',
            self::LOG_TYPE_MAINTENANCE => 'Maintenance',
            self::LOG_TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get the statuses as an array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_ADJUSTED => 'Adjusted',
        ];
    }

    /**
     * Get the technician associated with the time log.
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the work order associated with the time log.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * Get the appointment associated with the time log.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Get the approver associated with the time log.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include clock in logs.
     */
    public function scopeClockIn($query)
    {
        return $query->where('log_type', self::LOG_TYPE_CLOCK_IN);
    }

    /**
     * Scope a query to only include clock out logs.
     */
    public function scopeClockOut($query)
    {
        return $query->where('log_type', self::LOG_TYPE_CLOCK_OUT);
    }

    /**
     * Scope a query to only include job-related logs.
     */
    public function scopeJobLogs($query)
    {
        return $query->whereIn('log_type', [
            self::LOG_TYPE_JOB_START,
            self::LOG_TYPE_JOB_END,
        ]);
    }

    /**
     * Scope a query to only include pending logs.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include approved logs.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to only include logs for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('log_time', $date);
    }

    /**
     * Scope a query to only include logs for a specific technician.
     */
    public function scopeForTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    /**
     * Check if the log is a clock in.
     */
    public function isClockIn(): bool
    {
        return $this->log_type === self::LOG_TYPE_CLOCK_IN;
    }

    /**
     * Check if the log is a clock out.
     */
    public function isClockOut(): bool
    {
        return $this->log_type === self::LOG_TYPE_CLOCK_OUT;
    }

    /**
     * Check if the log is job-related.
     */
    public function isJobLog(): bool
    {
        return in_array($this->log_type, [
            self::LOG_TYPE_JOB_START,
            self::LOG_TYPE_JOB_END,
        ]);
    }

    /**
     * Check if the log is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the log is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Get the human-readable log type.
     */
    public function getLogTypeLabelAttribute(): string
    {
        return self::getLogTypes()[$this->log_type] ?? $this->log_type;
    }

    /**
     * Get the human-readable status.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get the duration between this log and the next log for the same technician.
     */
    public function getDurationToNextLog(): ?int
    {
        $nextLog = self::where('technician_id', $this->technician_id)
            ->where('log_time', '>', $this->log_time)
            ->orderBy('log_time', 'asc')
            ->first();

        if (!$nextLog) {
            return null;
        }

        return $this->log_time->diffInMinutes($nextLog->log_time);
    }

    /**
     * Approve the time log.
     */
    public function approve(User $approver, string $notes = null): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->approval_notes = $notes;

        return $this->save();
    }

    /**
     * Reject the time log.
     */
    public function reject(User $rejector, string $notes = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->approved_by = $rejector->id;
        $this->approved_at = now();
        $this->approval_notes = $notes;

        return $this->save();
    }

    /**
     * Get the total hours worked for a technician on a specific date.
     */
    public static function getTotalHoursWorked(int $technicianId, string $date): float
    {
        $logs = self::forTechnician($technicianId)
            ->forDate($date)
            ->whereIn('log_type', [
                self::LOG_TYPE_CLOCK_IN,
                self::LOG_TYPE_CLOCK_OUT,
                self::LOG_TYPE_BREAK_START,
                self::LOG_TYPE_BREAK_END,
                self::LOG_TYPE_LUNCH_START,
                self::LOG_TYPE_LUNCH_END,
            ])
            ->orderBy('log_time', 'asc')
            ->get();

        $totalMinutes = 0;
        $clockInTime = null;
        $breakStartTime = null;
        $lunchStartTime = null;

        foreach ($logs as $log) {
            switch ($log->log_type) {
                case self::LOG_TYPE_CLOCK_IN:
                    $clockInTime = $log->log_time;
                    break;
                case self::LOG_TYPE_CLOCK_OUT:
                    if ($clockInTime) {
                        $totalMinutes += $clockInTime->diffInMinutes($log->log_time);
                        $clockInTime = null;
                    }
                    break;
                case self::LOG_TYPE_BREAK_START:
                    $breakStartTime = $log->log_time;
                    break;
                case self::LOG_TYPE_BREAK_END:
                    if ($breakStartTime) {
                        $totalMinutes -= $breakStartTime->diffInMinutes($log->log_time);
                        $breakStartTime = null;
                    }
                    break;
                case self::LOG_TYPE_LUNCH_START:
                    $lunchStartTime = $log->log_time;
                    break;
                case self::LOG_TYPE_LUNCH_END:
                    if ($lunchStartTime) {
                        $totalMinutes -= $lunchStartTime->diffInMinutes($log->log_time);
                        $lunchStartTime = null;
                    }
                    break;
            }
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Get the current status of a technician (clocked in/out).
     */
    public static function getTechnicianStatus(int $technicianId): ?string
    {
        $lastLog = self::forTechnician($technicianId)
            ->whereIn('log_type', [
                self::LOG_TYPE_CLOCK_IN,
                self::LOG_TYPE_CLOCK_OUT,
            ])
            ->orderBy('log_time', 'desc')
            ->first();

        if (!$lastLog) {
            return null;
        }

        return $lastLog->isClockIn() ? 'clocked_in' : 'clocked_out';
    }

    /**
     * Get the last clock in time for a technician.
     */
    public static function getLastClockIn(int $technicianId): ?self
    {
        return self::forTechnician($technicianId)
            ->clockIn()
            ->orderBy('log_time', 'desc')
            ->first();
    }

    /**
     * Create a new time log with validation.
     */
    public static function createLog(array $data): self
    {
        // Validate that clock out follows clock in
        if ($data['log_type'] === self::LOG_TYPE_CLOCK_OUT) {
            $lastClockIn = self::getLastClockIn($data['technician_id']);
            
            if (!$lastClockIn || $lastClockIn->isApproved()) {
                throw new \Exception('Cannot clock out without an active clock in session');
            }
        }

        // Validate that clock in doesn't already exist for today
        if ($data['log_type'] === self::LOG_TYPE_CLOCK_IN) {
            $existingClockIn = self::forTechnician($data['technician_id'])
                ->clockIn()
                ->forDate(now()->toDateString())
                ->where('status', '!=', self::STATUS_REJECTED)
                ->first();

            if ($existingClockIn) {
                throw new \Exception('Already clocked in for today');
            }
        }

        return self::create($data);
    }
}