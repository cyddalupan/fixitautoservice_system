<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartsRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'parts_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_number',
        'technician_id',
        'work_order_id',
        'vehicle_id',
        'status',
        'priority',
        'total_cost',
        'approved_by',
        'approved_at',
        'ordered_by',
        'ordered_at',
        'received_by',
        'received_at',
        'installed_by',
        'installed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'ordered_at' => 'datetime',
            'received_at' => 'datetime',
            'installed_at' => 'datetime',
            'total_cost' => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->request_number) {
                $model->request_number = 'PR-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the technician who made the request.
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the work order associated with this request.
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * Get the vehicle associated with this request.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Get the approver of this request.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the person who ordered the parts.
     */
    public function orderer()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    /**
     * Get the person who received the parts.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the person who installed the parts.
     */
    public function installer()
    {
        return $this->belongsTo(User::class, 'installed_by');
    }

    /**
     * Get all items for this parts request.
     */
    public function items()
    {
        return $this->hasMany(PartsRequestItem::class, 'parts_request_id');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include ordered requests.
     */
    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    /**
     * Scope a query to only include received requests.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope a query to only include installed requests.
     */
    public function scopeInstalled($query)
    {
        return $query->where('status', 'installed');
    }

    /**
     * Scope a query to only include high priority requests.
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    /**
     * Scope a query to only include normal priority requests.
     */
    public function scopeNormalPriority($query)
    {
        return $query->where('priority', 'normal');
    }

    /**
     * Scope a query to only include low priority requests.
     */
    public function scopeLowPriority($query)
    {
        return $query->where('priority', 'low');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is ordered.
     */
    public function isOrdered(): bool
    {
        return $this->status === 'ordered';
    }

    /**
     * Check if the request is received.
     */
    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    /**
     * Check if the request is installed.
     */
    public function isInstalled(): bool
    {
        return $this->status === 'installed';
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'primary',
            'ordered' => 'info',
            'received' => 'success',
            'installed' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get the priority badge color.
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            'high' => 'danger',
            'normal' => 'primary',
            'low' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get the formatted status.
     */
    public function getFormattedStatusAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Get the formatted priority.
     */
    public function getFormattedPriorityAttribute(): string
    {
        return ucfirst($this->priority);
    }

    /**
     * Approve this parts request.
     */
    public function approve(int $approvedBy): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark as ordered.
     */
    public function markAsOrdered(int $orderedBy): bool
    {
        return $this->update([
            'status' => 'ordered',
            'ordered_by' => $orderedBy,
            'ordered_at' => now(),
        ]);
    }

    /**
     * Mark as received.
     */
    public function markAsReceived(int $receivedBy): bool
    {
        return $this->update([
            'status' => 'received',
            'received_by' => $receivedBy,
            'received_at' => now(),
        ]);
    }

    /**
     * Mark as installed.
     */
    public function markAsInstalled(int $installedBy): bool
    {
        return $this->update([
            'status' => 'installed',
            'installed_by' => $installedBy,
            'installed_at' => now(),
        ]);
    }

    /**
     * Calculate the total cost from items.
     */
    public function calculateTotalCost(): float
    {
        return $this->items()->sum('total_price');
    }

    /**
     * Update total cost from items.
     */
    public function updateTotalCost(): bool
    {
        return $this->update([
            'total_cost' => $this->calculateTotalCost(),
        ]);
    }
}