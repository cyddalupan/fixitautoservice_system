<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ServiceProgress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'serviceable_id',
        'serviceable_type',
        'customer_id',
        'vehicle_id',
        'has_appointment',
        'appointment_id',
        'appointment_created_at',
        'has_inspection',
        'inspection_id',
        'inspection_created_at',
        'has_estimate',
        'estimate_id',
        'estimate_created_at',
        'has_work_order',
        'work_order_id',
        'work_order_created_at',
        'has_invoice',
        'invoice_id',
        'invoice_created_at',
        'has_payment',
        'payment_id',
        'payment_created_at',
        'current_stage',
        'progress_percentage',
        'service_type',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_appointment' => 'boolean',
        'appointment_created_at' => 'datetime',
        'has_inspection' => 'boolean',
        'inspection_created_at' => 'datetime',
        'has_estimate' => 'boolean',
        'estimate_created_at' => 'datetime',
        'has_work_order' => 'boolean',
        'work_order_created_at' => 'datetime',
        'has_invoice' => 'boolean',
        'invoice_created_at' => 'datetime',
        'has_payment' => 'boolean',
        'payment_created_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the parent serviceable model.
     */
    public function serviceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the customer associated with the service progress.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the vehicle associated with the service progress.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the appointment associated with the service progress.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the inspection associated with the service progress.
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    /**
     * Get the estimate associated with the service progress.
     */
    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    /**
     * Get the work order associated with the service progress.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the invoice associated with the service progress.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the payment associated with the service progress.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Calculate progress percentage based on completed steps.
     * Based on user feedback: "100% ay pag nasa payment section na"
     * This means: 100% only when payment is reached/completed
     */
    public function calculateProgressPercentage(): int
    {
        // Get all possible stages for this service type
        $stages = $this->getStages();
        
        // If no stages, return 0
        if (count($stages) === 0) {
            return 0;
        }
        
        // Count completed stages
        $completed = 0;
        foreach ($stages as $stage) {
            if ($this->{"has_" . $stage}) {
                $completed++;
            }
        }
        
        // Calculate percentage
        $totalStages = count($stages);
        $percentage = (int) ($completed / $totalStages * 100);
        
        // If payment is not completed, cap at 90%
        // (Payment gives the final 10% to reach 100%)
        if (!$this->has_payment && $percentage > 90) {
            return 90;
        }
        
        return min(100, $percentage);
    }

    /**
     * Calculate context-aware progress percentage based on current stage.
     * For example: On estimate page, show progress up to estimate stage only.
     */
    public function calculateContextAwareProgress(string $currentStage): int
    {
        // Get all possible stages for this service type
        $stages = $this->getStages();
        
        // If no stages, return 0
        if (count($stages) === 0) {
            return 0;
        }
        
        // Find the position of current stage in the workflow
        $currentStageIndex = array_search($currentStage, $stages);
        if ($currentStageIndex === false) {
            // Current stage not in workflow, use overall progress
            return $this->calculateProgressPercentage();
        }
        
        // Count completed stages UP TO current stage
        $completed = 0;
        for ($i = 0; $i <= $currentStageIndex; $i++) {
            $stage = $stages[$i];
            if ($this->{"has_" . $stage}) {
                $completed++;
            }
        }
        
        // Calculate percentage: completed stages up to current stage / total stages
        $totalStages = count($stages);
        $percentage = (int) ($completed / $totalStages * 100);
        
        // If payment is not completed, cap at 90%
        // (Payment gives the final 10% to reach 100%)
        if (!$this->has_payment && $percentage > 90) {
            return 90;
        }
        
        return min(100, $percentage);
    }

    /**
     * Get the stages for this service type.
     */
    public function getStages(): array
    {
        if ($this->service_type === 'parts_purchase') {
            return ['invoice', 'payment'];
        }
        
        // For full service, determine which stages are actually used
        $stages = ['appointment'];
        
        // Only include inspection if it's actually used
        if ($this->has_inspection || $this->inspection_id) {
            $stages[] = 'inspection';
        }
        
        // Always include estimate, work_order, invoice, payment for full service
        $stages[] = 'estimate';
        $stages[] = 'work_order';
        $stages[] = 'invoice';
        $stages[] = 'payment';
        
        return $stages;
    }

    /**
     * Update current stage based on progress.
     */
    public function updateCurrentStage(): void
    {
        $stages = $this->getStages();
        
        // Find the current stage based on what's completed
        $currentStage = 'appointment'; // Default
        
        foreach ($stages as $stage) {
            if ($this->{"has_" . $stage}) {
                $currentStage = $stage;
            }
        }
        
        // If payment is completed, mark as completed
        if ($this->has_payment) {
            $currentStage = 'completed';
        }
        
        $this->current_stage = $currentStage;
        $this->save();
    }

    /**
     * Get the progress bar HTML for display.
     */

    /**
     * Get the count of completed stages.
     */
    public function getCompletedStagesCount(): int
    {
        $count = 0;
        $stages = $this->getStages();
        
        foreach ($stages as $stage) {
            if ($this->{"has_" . $stage}) {
                $count++;
            }
        }
        
        return $count;
    }
}