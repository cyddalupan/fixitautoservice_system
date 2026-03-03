<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeAttendance extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'time_attendance';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'work_date',
        'clock_in',
        'clock_out',
        'regular_hours',
        'overtime_hours',
        'double_time_hours',
        'shift_type',
        'location',
        'device_id',
        'ip_address',
        'status',
        'notes',
        'approved',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'double_time_hours' => 'decimal:2',
        'approved' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the time attendance record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the user who approved this record.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calculate total hours worked.
     */
    public function calculateTotalHours(): float
    {
        return $this->regular_hours + $this->overtime_hours + $this->double_time_hours;
    }

    /**
     * Calculate hours from clock in/out times.
     */
    public function calculateHoursFromClockTimes(): void
    {
        if ($this->clock_in && $this->clock_out) {
            $start = \Carbon\Carbon::parse($this->work_date . ' ' . $this->clock_in);
            $end = \Carbon\Carbon::parse($this->work_date . ' ' . $this->clock_out);
            
            $totalMinutes = $start->diffInMinutes($end);
            $totalHours = $totalMinutes / 60;
            
            // Basic calculation - in real system, you'd have rules for overtime
            $this->regular_hours = min($totalHours, 8);
            $this->overtime_hours = max(0, $totalHours - 8);
            $this->double_time_hours = 0; // Would need specific rules
        }
    }

    /**
     * Check if record is for today.
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->work_date->isToday();
    }

    /**
     * Check if employee is currently clocked in.
     */
    public function getIsClockedInAttribute(): bool
    {
        return !empty($this->clock_in) && empty($this->clock_out);
    }

    /**
     * Get formatted time attendance information.
     */
    public function getFormattedInfoAttribute(): array
    {
        return [
            'employee_name' => $this->employee->name ?? 'N/A',
            'work_date' => $this->work_date->format('M d, Y'),
            'day_of_week' => $this->work_date->format('l'),
            'clock_in' => $this->clock_in ? \Carbon\Carbon::parse($this->clock_in)->format('h:i A') : 'N/A',
            'clock_out' => $this->clock_out ? \Carbon\Carbon::parse($this->clock_out)->format('h:i A') : 'N/A',
            'regular_hours' => number_format($this->regular_hours, 2),
            'overtime_hours' => number_format($this->overtime_hours, 2),
            'total_hours' => number_format($this->calculateTotalHours(), 2),
            'status' => ucfirst($this->status),
            'approved' => $this->approved ? 'Yes' : 'No',
            'location' => $this->location ?? 'N/A',
        ];
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'early_departure' => 'warning',
            'on_leave' => 'info',
            'holiday' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Scope a query to only include time attendance by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include time attendance by employee.
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope a query to only include time attendance by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include approved time attendance.
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    /**
     * Scope a query to only include pending approval time attendance.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approved', false);
    }

    /**
     * Scope a query to only include today's time attendance.
     */
    public function scopeToday($query)
    {
        return $query->where('work_date', today());
    }
}