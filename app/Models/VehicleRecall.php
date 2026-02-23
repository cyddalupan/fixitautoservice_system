<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleRecall extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'recall_id',
        'campaign_number',
        'component',
        'summary',
        'consequence',
        'remedy',
        'recall_date',
        'status',
        'notification_date',
        'repair_date',
        'repair_notes',
        'estimated_cost',
        'actual_cost',
        'customer_notified',
        'customer_notification_date',
        'customer_responded',
        'customer_response_date',
        'customer_response_notes',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'recall_date' => 'date',
        'notification_date' => 'date',
        'repair_date' => 'date',
        'customer_notification_date' => 'date',
        'customer_response_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'customer_notified' => 'boolean',
        'customer_responded' => 'boolean',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the vehicle that owns the recall.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope a query to only include active recalls.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include open recalls.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include recalls that need customer notification.
     */
    public function scopeNeedsNotification($query)
    {
        return $query->where('customer_notified', false)
                    ->where('is_active', true)
                    ->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope a query to only include recalls by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if recall is overdue for notification.
     */
    public function isNotificationOverdue(): bool
    {
        if ($this->customer_notified) {
            return false;
        }

        if (!$this->notification_date) {
            return false;
        }

        return $this->notification_date->isPast() && $this->notification_date->diffInDays(now()) > 7;
    }

    /**
     * Get the recall status with color for display.
     */
    public function getStatusWithColorAttribute(): array
    {
        $statusColors = [
            'open' => ['color' => 'danger', 'label' => 'Open'],
            'in_progress' => ['color' => 'warning', 'label' => 'In Progress'],
            'completed' => ['color' => 'success', 'label' => 'Completed'],
            'closed' => ['color' => 'secondary', 'label' => 'Closed'],
        ];

        return $statusColors[$this->status] ?? ['color' => 'secondary', 'label' => ucfirst($this->status)];
    }

    /**
     * Calculate the days since recall was issued.
     */
    public function getDaysSinceRecallAttribute(): ?int
    {
        if (!$this->recall_date) {
            return null;
        }

        return $this->recall_date->diffInDays(now());
    }

    /**
     * Check if recall repair is overdue.
     */
    public function isRepairOverdue(): bool
    {
        if ($this->status === 'completed' || $this->status === 'closed') {
            return false;
        }

        if (!$this->recall_date) {
            return false;
        }

        // Consider recall overdue if it's been more than 90 days
        return $this->recall_date->diffInDays(now()) > 90;
    }

    /**
     * Get the estimated repair cost formatted.
     */
    public function getEstimatedCostFormattedAttribute(): string
    {
        if (!$this->estimated_cost) {
            return 'N/A';
        }

        return '$' . number_format($this->estimated_cost, 2);
    }

    /**
     * Get the actual repair cost formatted.
     */
    public function getActualCostFormattedAttribute(): string
    {
        if (!$this->actual_cost) {
            return 'N/A';
        }

        return '$' . number_format($this->actual_cost, 2);
    }
}