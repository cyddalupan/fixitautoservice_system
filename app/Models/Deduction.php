<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deduction extends Model
{
    protected $fillable = [
        'employee_id',
        'deduction_type',
        'amount',
        'date',
        'payroll_period_id',
        'notes',
        'created_by',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Get the employee associated with the deduction.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the payroll period associated with the deduction.
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    /**
     * Get the user who created the deduction.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include pending deductions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved deductions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include applied deductions.
     */
    public function scopeApplied($query)
    {
        return $query->where('status', 'applied');
    }

    /**
     * Get the deduction type options.
     */
    public static function getDeductionTypes(): array
    {
        return [
            'SSS' => 'SSS Contribution',
            'Tax' => 'Withholding Tax',
            'Cash Advance' => 'Cash Advance',
            'Late' => 'Late Deduction',
            'Other' => 'Other Deduction'
        ];
    }

    /**
     * Get the status options.
     */
    public static function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'applied' => 'Applied to Payroll'
        ];
    }

    /**
     * Get the status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'applied' => 'success',
            default => 'secondary'
        };
    }
}
