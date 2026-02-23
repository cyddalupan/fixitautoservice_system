<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'customer_id',
        'vehicle_id',
        'service_advisor_id',
        'technician_id',
        'work_order_number',
        'work_order_date',
        'work_order_status',
        'priority',
        'work_order_type',
        'odometer_in',
        'odometer_out',
        'fuel_level',
        'vehicle_condition',
        'customer_concerns',
        'customer_complaints',
        'initial_diagnosis',
        'technician_diagnosis',
        'recommended_services',
        'additional_notes',
        'estimated_labor_hours',
        'estimated_labor_cost',
        'estimated_parts_cost',
        'estimated_tax',
        'estimated_total',
        'estimate_approved',
        'estimate_approved_at',
        'estimate_notes',
        'actual_labor_hours',
        'actual_labor_cost',
        'actual_parts_cost',
        'actual_tax',
        'actual_total',
        'discount_amount',
        'final_amount',
        'payment_status',
        'amount_paid',
        'balance_due',
        'payment_due_date',
        'is_warranty_work',
        'warranty_type',
        'warranty_number',
        'warranty_expiry',
        'warranty_coverage',
        'is_insurance_work',
        'insurance_company',
        'insurance_claim_number',
        'insurance_adjuster',
        'insurance_deductible',
        'check_in_time',
        'diagnosis_start_time',
        'diagnosis_complete_time',
        'work_start_time',
        'work_complete_time',
        'quality_check_time',
        'customer_notified_time',
        'customer_pickup_time',
        'invoice_sent_time',
        'bay_number',
        'bay_status',
        'parts_required',
        'parts_used',
        'parts_ordered',
        'parts_ordered_at',
        'parts_received_at',
        'labor_tasks',
        'technician_assignments',
        'quality_check_passed',
        'quality_check_by',
        'quality_check_at',
        'quality_check_notes',
        'customer_notified',
        'notification_method',
        'customer_communication_log',
        'work_performed',
        'technician_notes',
        'service_advisor_notes',
        'customer_feedback',
        'customer_rating',
        'requires_customer_approval',
        'customer_approval_received',
        'customer_approval_at',
        'requires_manager_approval',
        'manager_approval_received',
        'manager_approval_at',
        'is_rush_order',
        'is_complex_job',
        'has_safety_concerns',
        'attachments',
        'tags',
        'internal_notes',
    ];

    protected $casts = [
        'work_order_date' => 'date',
        'payment_due_date' => 'date',
        'warranty_expiry' => 'date',
        'check_in_time' => 'datetime',
        'diagnosis_start_time' => 'datetime',
        'diagnosis_complete_time' => 'datetime',
        'work_start_time' => 'datetime',
        'work_complete_time' => 'datetime',
        'quality_check_time' => 'datetime',
        'customer_notified_time' => 'datetime',
        'customer_pickup_time' => 'datetime',
        'invoice_sent_time' => 'datetime',
        'parts_ordered_at' => 'datetime',
        'parts_received_at' => 'datetime',
        'quality_check_at' => 'datetime',
        'customer_approval_at' => 'datetime',
        'manager_approval_at' => 'datetime',
        'estimate_approved_at' => 'datetime',
        'estimated_labor_hours' => 'decimal:2',
        'estimated_labor_cost' => 'decimal:2',
        'estimated_parts_cost' => 'decimal:2',
        'estimated_tax' => 'decimal:2',
        'estimated_total' => 'decimal:2',
        'actual_labor_hours' => 'decimal:2',
        'actual_labor_cost' => 'decimal:2',
        'actual_parts_cost' => 'decimal:2',
        'actual_tax' => 'decimal:2',
        'actual_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'warranty_coverage' => 'decimal:2',
        'insurance_deductible' => 'decimal:2',
        'parts_required' => 'array',
        'parts_used' => 'array',
        'labor_tasks' => 'array',
        'technician_assignments' => 'array',
        'attachments' => 'array',
        'tags' => 'array',
        'estimate_approved' => 'boolean',
        'parts_ordered' => 'boolean',
        'quality_check_passed' => 'boolean',
        'customer_notified' => 'boolean',
        'is_warranty_work' => 'boolean',
        'is_insurance_work' => 'boolean',
        'requires_customer_approval' => 'boolean',
        'customer_approval_received' => 'boolean',
        'requires_manager_approval' => 'boolean',
        'manager_approval_received' => 'boolean',
        'is_rush_order' => 'boolean',
        'is_complex_job' => 'boolean',
        'has_safety_concerns' => 'boolean',
    ];

    /**
     * Generate a unique work order number.
     */
    public static function generateWorkOrderNumber(): string
    {
        $prefix = 'WO-' . date('Y') . '-';
        $lastOrder = self::where('work_order_number', 'like', $prefix . '%')
            ->orderBy('work_order_number', 'desc')
            ->first();
        
        if ($lastOrder) {
            $lastNumber = (int) str_replace($prefix, '', $lastOrder->work_order_number);
            $nextNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '00001';
        }
        
        return $prefix . $nextNumber;
    }

    /**
     * Relationships
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function serviceAdvisor()
    {
        return $this->belongsTo(User::class, 'service_advisor_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function qualityChecker()
    {
        return $this->belongsTo(User::class, 'quality_check_by');
    }

    public function items()
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function tasks()
    {
        return $this->hasMany(WorkOrderTask::class);
    }

    /**
     * Scopes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('work_order_date', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('work_order_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('work_order_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    public function scopePending($query)
    {
        return $query->whereIn('work_order_status', ['draft', 'pending_approval', 'approved']);
    }

    public function scopeInProgress($query)
    {
        return $query->where('work_order_status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('work_order_status', 'completed');
    }

    public function scopeInvoiced($query)
    {
        return $query->where('work_order_status', 'invoiced');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue')
            ->orWhere(function($q) {
                $q->where('payment_due_date', '<', Carbon::today())
                  ->where('balance_due', '>', 0);
            });
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'emergency']);
    }

    public function scopeWarranty($query)
    {
        return $query->where('is_warranty_work', true);
    }

    public function scopeInsurance($query)
    {
        return $query->where('is_insurance_work', true);
    }

    public function scopeRequiresApproval($query)
    {
        return $query->where('requires_customer_approval', true)
            ->where('customer_approval_received', false);
    }

    /**
     * Accessors
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->work_order_status) {
            'draft' => 'secondary',
            'pending_approval' => 'warning',
            'approved' => 'info',
            'in_progress' => 'primary',
            'on_hold' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            'invoiced' => 'success',
            default => 'secondary',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'success',
            'normal' => 'info',
            'high' => 'warning',
            'emergency' => 'danger',
            default => 'info',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success',
            'overdue' => 'danger',
            'written_off' => 'secondary',
            default => 'warning',
        };
    }

    public function getFormattedEstimatedTotalAttribute(): string
    {
        return '$' . number_format($this->estimated_total, 2);
    }

    public function getFormattedFinalAmountAttribute(): string
    {
        return '$' . number_format($this->final_amount, 2);
    }

    public function getFormattedBalanceDueAttribute(): string
    {
        return '$' . number_format($this->balance_due, 2);
    }

    public function getFormattedAmountPaidAttribute(): string
    {
        return '$' . number_format($this->amount_paid, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->payment_due_date && 
               $this->payment_due_date->lt(Carbon::today()) && 
               $this->balance_due > 0;
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if ($this->is_overdue) {
            return $this->payment_due_date->diffInDays(Carbon::today());
        }
        return null;
    }

    public function getCompletionPercentageAttribute(): int
    {
        if ($this->work_order_status === 'completed') {
            return 100;
        }
        
        $completedTasks = $this->tasks()->where('task_status', 'completed')->count();
        $totalTasks = $this->tasks()->count();
        
        if ($totalTasks === 0) {
            return 0;
        }
        
        return (int) (($completedTasks / $totalTasks) * 100);
    }

    public function getEstimatedProfitAttribute(): float
    {
        return $this->estimated_total - ($this->estimated_labor_cost + $this->estimated_parts_cost);
    }

    public function getActualProfitAttribute(): float
    {
        return $this->final_amount - ($this->actual_labor_cost + $this->actual_parts_cost);
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->final_amount === 0) {
            return 0;
        }
        return ($this->actual_profit / $this->final_amount) * 100;
    }

    /**
     * Methods
     */
    public function startWork(): bool
    {
        if ($this->work_order_status === 'approved') {
            $this->update([
                'work_order_status' => 'in_progress',
                'work_start_time' => now(),
                'bay_status' => 'occupied',
            ]);
            return true;
        }
        return false;
    }

    public function completeWork(): bool
    {
        if ($this->work_order_status === 'in_progress') {
            $this->update([
                'work_order_status' => 'completed',
                'work_complete_time' => now(),
                'bay_status' => 'available',
            ]);
            return true;
        }
        return false;
    }

    public function approveEstimate(): bool
    {
        if ($this->work_order_status === 'pending_approval') {
            $this->update([
                'work_order_status' => 'approved',
                'estimate_approved' => true,
                'estimate_approved_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    public function addPayment(float $amount): bool
    {
        $newAmountPaid = $this->amount_paid + $amount;
        $newBalanceDue = $this->final_amount - $newAmountPaid;
        
        $paymentStatus = match(true) {
            $newBalanceDue <= 0 => 'paid',
            $newAmountPaid > 0 => 'partial',
            default => 'pending',
        };
        
        $this->update([
            'amount_paid' => $newAmountPaid,
            'balance_due' => $newBalanceDue,
            'payment_status' => $paymentStatus,
        ]);
        
        return true;
    }

    public function markAsInvoiced(): bool
    {
        $this->update([
            'work_order_status' => 'invoiced',
            'invoice_sent_time' => now(),
        ]);
        return true;
    }

    public function calculateTotals(): void
    {
        // Calculate estimated totals
        $estimatedSubtotal = $this->estimated_labor_cost + $this->estimated_parts_cost;
        $estimatedTotal = $estimatedSubtotal + $this->estimated_tax;
        
        // Calculate actual totals
        $actualSubtotal = $this->actual_labor_cost + $this->actual_parts_cost;
        $actualTotal = $actualSubtotal + $this->actual_tax - $this->discount_amount;
        
        $this->update([
            'estimated_total' => $estimatedTotal,
            'actual_total' => $actualTotal,
            'final_amount' => $actualTotal,
            'balance_due' => $actualTotal - $this->amount_paid,
        ]);
    }

    public function getTimeline(): array
    {
        $timeline = [];
        
        if ($this->check_in_time) {
            $timeline[] = [
                'event' => 'Vehicle Checked In',
                'time' => $this->check_in_time,
                'icon' => 'check-circle',
                'color' => 'success',
            ];
        }
        
        if ($this->diagnosis_start_time) {
            $timeline[] = [
                'event' => 'Diagnosis Started',
                'time' => $this->diagnosis_start_time,
                'icon' => 'search',
                'color' => 'info',
            ];
        }
        
        if ($this->diagnosis_complete_time) {
            $timeline[] = [
                'event' => 'Diagnosis Complete',
                'time' => $this->diagnosis_complete_time,
                'icon' => 'check-double',
                'color' => 'success',
            ];
        }
        
        if ($this->work_start_time) {
            $timeline[] = [
                'event' => 'Work Started',
                'time' => $this->work_start_time,
                'icon' => 'tools',
                'color' => 'primary',
            ];
        }
        
        if ($this->work_complete_time) {
            $timeline[] = [
                'event' => 'Work Complete',
                'time' => $this->work_complete_time,
                'icon' => 'check',
                'color' => 'success',
            ];
        }
        
        if ($this->quality_check_time) {
            $timeline[] = [
                'event' => 'Quality Check',
                'time' => $this->quality_check_time,
                'icon' => 'shield-check',
                'color' => $this->quality_check_passed ? 'success' : 'danger',
            ];
        }
        
        if ($this->customer_notified_time) {
            $timeline[] = [
                'event' => 'Customer Notified',
                'time' => $this->customer_notified_time,
                'icon' => 'bell',
                'color' => 'info',
            ];
        }
        
        if ($this->customer_pickup_time) {
            $timeline[] = [
                'event' => 'Vehicle Picked Up',
                'time' => $this->customer_pickup_time,
                'icon' => 'truck',
                'color' => 'success',
            ];
        }
        
        if ($this->invoice_sent_time) {
            $timeline[] = [
                'event' => 'Invoice Sent',
                'time' => $this->invoice_sent_time,
                'icon' => 'file-invoice-dollar',
                'color' => 'success',
            ];
        }
        
        // Sort by time
        usort($timeline, function($a, $b) {
            return $a['time'] <=> $b['time'];
        });
        
        return $timeline;
    }

    /**
     * Get work order summary for dashboard.
     */
    public function getSummary(): array
    {
        return [
            'work_order_number' => $this->work_order_number,
            'customer_name' => $this->customer->full_name,
            'vehicle' => $this->vehicle->year . ' ' . $this->vehicle->make . ' ' . $this->vehicle->model,
            'license_plate' => $this->vehicle->license_plate,
            'status' => $this->work_order_status,
            'status_color' => $this->status_color,
            'priority' => $this->priority,
            'priority_color' => $this->priority_color,
            'estimated_total' => $this->formatted_estimated_total,
            'final_amount' => $this->formatted_final_amount,
            'balance_due' => $this->formatted_balance_due,
            'completion_percentage' => $this->completion_percentage,
            'technician' => $this->technician ? $this->technician->name : 'Not assigned',
            'service_advisor' => $this->serviceAdvisor->name,
            'days_open' => $this->created_at->diffInDays(now()),
            'is_overdue' => $this->is_overdue,
            'days_overdue' => $this->days_overdue,
        ];
    }
}