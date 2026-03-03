<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payroll_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'regular_hours',
        'overtime_hours',
        'double_time_hours',
        'regular_rate',
        'overtime_rate',
        'double_time_rate',
        'regular_pay',
        'overtime_pay',
        'double_time_pay',
        'bonus',
        'commission',
        'other_earnings',
        'total_gross',
        'federal_tax',
        'state_tax',
        'social_security',
        'medicare',
        'health_insurance',
        'retirement_contribution',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'status',
        'pay_date',
        'payment_method',
        'check_number',
        'notes',
        'earnings_breakdown',
        'deductions_breakdown',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'double_time_hours' => 'decimal:2',
        'regular_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'double_time_rate' => 'decimal:2',
        'regular_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'double_time_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'commission' => 'decimal:2',
        'other_earnings' => 'decimal:2',
        'total_gross' => 'decimal:2',
        'federal_tax' => 'decimal:2',
        'state_tax' => 'decimal:2',
        'social_security' => 'decimal:2',
        'medicare' => 'decimal:2',
        'health_insurance' => 'decimal:2',
        'retirement_contribution' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'pay_date' => 'date',
        'earnings_breakdown' => 'array',
        'deductions_breakdown' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the payroll period that owns the record.
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * Get the employee that owns the payroll record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the employee's HR details.
     */
    public function employeeHrDetails()
    {
        return $this->hasOne(EmployeeHrDetail::class, 'user_id', 'employee_id');
    }

    /**
     * Calculate payroll record totals.
     */
    public function calculateTotals(): self
    {
        // Calculate regular pay
        $this->regular_pay = $this->regular_hours * $this->regular_rate;
        
        // Calculate overtime pay (typically 1.5x regular rate)
        $this->overtime_pay = $this->overtime_hours * $this->overtime_rate;
        
        // Calculate double time pay (typically 2x regular rate)
        $this->double_time_pay = $this->double_time_hours * $this->double_time_rate;
        
        // Calculate total gross
        $this->total_gross = $this->regular_pay + $this->overtime_pay + $this->double_time_pay 
                           + $this->bonus + $this->commission + $this->other_earnings;
        
        // Calculate total deductions
        $this->total_deductions = $this->federal_tax + $this->state_tax + $this->social_security 
                                + $this->medicare + $this->health_insurance 
                                + $this->retirement_contribution + $this->other_deductions;
        
        // Calculate net pay
        $this->net_pay = $this->total_gross - $this->total_deductions;
        
        return $this;
    }

    /**
     * Generate earnings breakdown.
     */
    public function generateEarningsBreakdown(): array
    {
        return [
            'regular' => [
                'hours' => $this->regular_hours,
                'rate' => $this->regular_rate,
                'amount' => $this->regular_pay,
            ],
            'overtime' => [
                'hours' => $this->overtime_hours,
                'rate' => $this->overtime_rate,
                'amount' => $this->overtime_pay,
            ],
            'double_time' => [
                'hours' => $this->double_time_hours,
                'rate' => $this->double_time_rate,
                'amount' => $this->double_time_pay,
            ],
            'bonus' => $this->bonus,
            'commission' => $this->commission,
            'other_earnings' => $this->other_earnings,
            'total_gross' => $this->total_gross,
        ];
    }

    /**
     * Generate deductions breakdown.
     */
    public function generateDeductionsBreakdown(): array
    {
        return [
            'federal_tax' => $this->federal_tax,
            'state_tax' => $this->state_tax,
            'social_security' => $this->social_security,
            'medicare' => $this->medicare,
            'health_insurance' => $this->health_insurance,
            'retirement_contribution' => $this->retirement_contribution,
            'other_deductions' => $this->other_deductions,
            'total_deductions' => $this->total_deductions,
        ];
    }

    /**
     * Get formatted payroll information.
     */
    public function getFormattedInfoAttribute(): array
    {
        return [
            'employee_name' => $this->employee->name ?? 'N/A',
            'employee_number' => $this->employeeHrDetails->employee_number ?? 'N/A',
            'period' => $this->payrollPeriod->period_name ?? 'N/A',
            'pay_date' => $this->pay_date->format('M d, Y'),
            'regular_hours' => number_format($this->regular_hours, 2),
            'overtime_hours' => number_format($this->overtime_hours, 2),
            'total_gross' => '$' . number_format($this->total_gross, 2),
            'total_deductions' => '$' . number_format($this->total_deductions, 2),
            'net_pay' => '$' . number_format($this->net_pay, 2),
            'status' => ucfirst($this->status),
            'payment_method' => $this->payment_method ?? 'Direct Deposit',
        ];
    }

    /**
     * Get payslip data for employee.
     */
    public function getPayslipDataAttribute(): array
    {
        return [
            'company_name' => 'Fix-It Auto Services',
            'company_address' => '123 Auto Service St, City, State 12345',
            'company_phone' => '(555) 123-4567',
            'payslip_number' => 'PS-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'employee' => [
                'name' => $this->employee->name ?? 'N/A',
                'employee_id' => $this->employeeHrDetails->employee_number ?? 'N/A',
                'department' => $this->employeeHrDetails->department ?? 'N/A',
                'position' => $this->employeeHrDetails->position ?? 'N/A',
            ],
            'pay_period' => [
                'name' => $this->payrollPeriod->period_name ?? 'N/A',
                'start_date' => $this->payrollPeriod->start_date?->format('M d, Y') ?? 'N/A',
                'end_date' => $this->payrollPeriod->end_date?->format('M d, Y') ?? 'N/A',
                'pay_date' => $this->pay_date->format('M d, Y'),
            ],
            'earnings' => $this->generateEarningsBreakdown(),
            'deductions' => $this->generateDeductionsBreakdown(),
            'totals' => [
                'gross_pay' => $this->total_gross,
                'total_deductions' => $this->total_deductions,
                'net_pay' => $this->net_pay,
            ],
            'notes' => $this->notes,
        ];
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'calculated' => 'info',
            'approved' => 'warning',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Scope a query to only include payroll records by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include payroll records by employee.
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope a query to only include payroll records by date range.
     */
    public function scopeByPayDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('pay_date', [$startDate, $endDate]);
    }
}