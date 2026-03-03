<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'appointments';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'service_id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_id',
        'customer_id',
        'vehicle_id',
    ];

    /**
     * Get the appointment for the service.
     */
    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class, 'service_id', 'service_id');
    }

    /**
     * Get the estimate for the service.
     */
    public function estimate(): HasOne
    {
        return $this->hasOne(Estimate::class, 'service_id', 'service_id');
    }

    /**
     * Get the work order for the service.
     */
    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class, 'service_id', 'service_id');
    }

    /**
     * Get the invoice for the service.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'service_id', 'service_id');
    }

    /**
     * Get the payments for the service.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'service_id', 'service_id');
    }

    /**
     * Get the customer that owns the service.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the vehicle for the service.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the current stage of the service workflow.
     */
    public function getCurrentStageAttribute(): string
    {
        if ($this->payments()->where('status', 'completed')->exists()) {
            return 'payment_completed';
        } elseif ($this->invoice()->exists()) {
            return 'invoiced';
        } elseif ($this->workOrder()->exists()) {
            return 'work_in_progress';
        } elseif ($this->estimate()->exists()) {
            return 'estimated';
        } elseif ($this->appointment()->exists()) {
            return 'appointment_scheduled';
        }
        
        return 'new';
    }

    /**
     * Get the workflow progress percentage.
     */
    public function getWorkflowProgressAttribute(): int
    {
        $stages = ['appointment_scheduled', 'estimated', 'work_in_progress', 'invoiced', 'payment_completed'];
        $currentStage = $this->current_stage;
        
        $currentIndex = array_search($currentStage, $stages);
        if ($currentIndex === false) {
            return 0;
        }
        
        return (int) (($currentIndex + 1) / count($stages) * 100);
    }

    /**
     * Get the total amount for the service.
     */
    public function getTotalAmountAttribute(): float
    {
        if ($this->invoice()->exists()) {
            return $this->invoice->total_amount;
        } elseif ($this->estimate()->exists()) {
            return $this->estimate->total_amount;
        } elseif ($this->workOrder()->exists()) {
            return $this->workOrder->final_amount;
        }
        
        return 0;
    }

    /**
     * Get the amount paid for the service.
     */
    public function getAmountPaidAttribute(): float
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Get the balance due for the service.
     */
    public function getBalanceDueAttribute(): float
    {
        return $this->total_amount - $this->amount_paid;
    }

    /**
     * Check if the service is fully paid.
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->balance_due <= 0;
    }

    /**
     * Generate a unique service ID.
     */
    public static function generateServiceId(): string
    {
        $prefix = 'SVC-' . date('Ymd') . '-';
        $lastService = self::where('service_id', 'like', $prefix . '%')
            ->orderBy('service_id', 'desc')
            ->first();
        
        if ($lastService) {
            $lastNumber = (int) str_replace($prefix, '', $lastService->service_id);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        
        return $prefix . $nextNumber;
    }

    /**
     * Get the service summary.
     */
    public function getSummary(): array
    {
        return [
            'service_id' => $this->service_id,
            'customer_name' => $this->customer->full_name ?? 'N/A',
            'vehicle' => $this->vehicle ? $this->vehicle->year . ' ' . $this->vehicle->make . ' ' . $this->vehicle->model : 'N/A',
            'license_plate' => $this->vehicle->license_plate ?? 'N/A',
            'current_stage' => $this->current_stage,
            'workflow_progress' => $this->workflow_progress,
            'total_amount' => '₱' . number_format($this->total_amount, 2),
            'amount_paid' => '₱' . number_format($this->amount_paid, 2),
            'balance_due' => '₱' . number_format($this->balance_due, 2),
            'is_paid' => $this->is_paid,
            'has_appointment' => $this->appointment()->exists(),
            'has_estimate' => $this->estimate()->exists(),
            'has_work_order' => $this->workOrder()->exists(),
            'has_invoice' => $this->invoice()->exists(),
            'has_payments' => $this->payments()->exists(),
        ];
    }
}