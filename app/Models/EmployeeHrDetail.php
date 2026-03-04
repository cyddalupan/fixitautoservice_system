<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeHrDetail extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'employee_hr_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'employee_number',
        'department',
        'position',
        'job_title',
        'employment_status',
        'hire_date',
        'termination_date',
        'termination_reason',
        'base_salary',
        'pay_frequency',
        'pay_type',
        'bank_name',
        'bank_account_number',
        'bank_routing_number',
        'social_security_number',
        'date_of_birth',
        'marital_status',
        'dependents',
        'emergency_contacts',
        'benefits_enrollment',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'date_of_birth' => 'date',
        'base_salary' => 'decimal:2',
        'emergency_contacts' => 'array',
        'benefits_enrollment' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns the HR details.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee's payroll records.
     */
    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class, 'employee_id', 'user_id');
    }

    /**
     * Get the employee's time attendance records.
     */
    public function timeAttendance()
    {
        return $this->hasMany(TimeAttendance::class, 'employee_id', 'user_id');
    }

    /**
     * Get the employee's leave requests.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'user_id');
    }

    /**
     * Get the employee's leave balances.
     */
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'employee_id', 'user_id');
    }

    /**
     * Get the employee's deductions.
     */
    public function employeeDeductions()
    {
        return $this->hasMany(EmployeeDeduction::class, 'employee_id', 'user_id');
    }

    /**
     * Check if employee is active.
     */
    public function getIsActiveAttribute(): bool
    {
        return empty($this->termination_date) || $this->termination_date > now();
    }

    /**
     * Calculate years of service.
     */
    public function getYearsOfServiceAttribute(): float
    {
        if (!$this->hire_date) {
            return 0;
        }

        $start = $this->hire_date;
        $end = $this->termination_date ?? now();
        
        return $start->diffInYears($end);
    }

    /**
     * Get formatted employee information.
     */
    public function getFormattedInfoAttribute(): array
    {
        return [
            'employee_number' => $this->employee_number,
            'full_name' => $this->user->name ?? 'N/A',
            'department' => $this->department ?? 'N/A',
            'position' => $this->position ?? 'N/A',
            'employment_status' => ucfirst(str_replace('_', ' ', $this->employment_status)),
            'hire_date' => $this->hire_date?->format('M d, Y') ?? 'N/A',
            'years_of_service' => number_format($this->years_of_service, 1),
            'base_salary' => '$' . number_format($this->base_salary, 2),
            'pay_frequency' => ucfirst($this->pay_frequency),
        ];
    }

    /**
     * Scope a query to only include active employees.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('termination_date')
                    ->orWhere('termination_date', '>', now());
    }

    /**
     * Scope a query to only include terminated employees.
     */
    public function scopeTerminated($query)
    {
        return $query->whereNotNull('termination_date')
                    ->where('termination_date', '<=', now());
    }

    /**
     * Scope a query to only include employees by department.
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope a query to only include employees by employment status.
     */
    public function scopeByEmploymentStatus($query, $status)
    {
        return $query->where('employment_status', $status);
    }

    /**
     * Get the badge color for employment status.
     */
    public function getEmploymentStatusBadgeColorAttribute()
    {
        return match($this->employment_status) {
            'active' => 'success',
            'on_leave' => 'warning',
            'suspended' => 'danger',
            'terminated' => 'danger',
            'full_time' => 'success',
            'part_time' => 'info',
            'contract' => 'warning',
            'temporary' => 'secondary',
            'intern' => 'light',
            default => 'light',
        };
    }

    /**
     * Get the display text for employment status.
     */
    public function getEmploymentStatusDisplayAttribute()
    {
        return match($this->employment_status) {
            'active' => 'Active',
            'on_leave' => 'On Leave',
            'suspended' => 'Suspended',
            'terminated' => 'Terminated',
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'temporary' => 'Temporary',
            'intern' => 'Intern',
            default => ucfirst(str_replace('_', ' ', $this->employment_status)),
        };
    }
}