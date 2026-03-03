<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPeriod extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payroll_periods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'period_name',
        'start_date',
        'end_date',
        'pay_date',
        'status',
        'total_gross',
        'total_deductions',
        'total_net',
        'employee_count',
        'processed_by',
        'processed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pay_date' => 'date',
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the payroll records for this period.
     */
    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    /**
     * Get the user who processed this payroll.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the payroll history logs for this period.
     */
    public function payrollHistoryLogs(): HasMany
    {
        return $this->hasMany(PayrollHistoryLog::class);
    }

    /**
     * Calculate payroll period statistics.
     */
    public function calculateStatistics(): array
    {
        $records = $this->payrollRecords;
        
        $stats = [
            'total_gross' => $records->sum('total_gross'),
            'total_deductions' => $records->sum('total_deductions'),
            'total_net' => $records->sum('net_pay'),
            'employee_count' => $records->count(),
            'average_gross' => $records->avg('total_gross'),
            'average_net' => $records->avg('net_pay'),
        ];

        // Update the period with calculated totals
        $this->update([
            'total_gross' => $stats['total_gross'],
            'total_deductions' => $stats['total_deductions'],
            'total_net' => $stats['total_net'],
            'employee_count' => $stats['employee_count'],
        ]);

        return $stats;
    }

    /**
     * Check if payroll period can be processed.
     */
    public function canBeProcessed(): bool
    {
        return $this->status === 'draft' && $this->end_date <= now();
    }

    /**
     * Check if payroll period can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'processing' && $this->payrollRecords()->where('status', 'calculated')->count() > 0;
    }

    /**
     * Check if payroll period can be paid.
     */
    public function canBePaid(): bool
    {
        return $this->status === 'approved' && $this->pay_date <= now();
    }

    /**
     * Get formatted period information.
     */
    public function getFormattedInfoAttribute(): array
    {
        return [
            'period_name' => $this->period_name,
            'date_range' => $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y'),
            'pay_date' => $this->pay_date->format('M d, Y'),
            'status' => ucfirst($this->status),
            'total_gross' => '$' . number_format($this->total_gross, 2),
            'total_deductions' => '$' . number_format($this->total_deductions, 2),
            'total_net' => '$' . number_format($this->total_net, 2),
            'employee_count' => $this->employee_count,
            'processed_by' => $this->processedBy->name ?? 'N/A',
            'processed_at' => $this->processed_at?->format('M d, Y H:i') ?? 'N/A',
        ];
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'processing' => 'warning',
            'approved' => 'info',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Scope a query to only include payroll periods by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include payroll periods by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include upcoming payroll periods.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('pay_date', '>', now())
                    ->where('status', '!=', 'paid')
                    ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope a query to only include overdue payroll periods.
     */
    public function scopeOverdue($query)
    {
        return $query->where('pay_date', '<', now())
                    ->where('status', '!=', 'paid')
                    ->where('status', '!=', 'cancelled');
    }
}