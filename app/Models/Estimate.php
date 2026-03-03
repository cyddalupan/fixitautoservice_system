<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Estimate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'estimate_number',
        'issue_date',
        'expiry_date',
        'mileage',
        'labor_hours',
        'labor_rate',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'notes',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'labor_hours' => 'decimal:2',
        'labor_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the estimate.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the vehicle that owns the estimate.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the items for the estimate.
     */
    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class);
    }

    /**
     * Get the work order associated with the estimate.
     */
    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    /**
     * Scope a query to only include estimates with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include estimates that are not expired.
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expiry_date', '>=', now());
    }

    /**
     * Scope a query to only include estimates that are expired.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Check if the estimate is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date < now();
    }

    /**
     * Check if the estimate can be converted to a work order.
     */
    public function canConvertToWorkOrder(): bool
    {
        return $this->status === 'approved' && !$this->isExpired() && !$this->workOrder;
    }

    /**
     * Get the formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    /**
     * Get the formatted subtotal amount.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '₱' . number_format($this->subtotal, 2);
    }

    /**
     * Get the formatted tax amount.
     */
    public function getFormattedTaxAttribute(): string
    {
        return '₱' . number_format($this->tax_amount, 2);
    }

    /**
     * Get the days until expiry.
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            'draft' => 'bg-secondary',
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'expired' => 'bg-dark',
        ];

        return $classes[$this->status] ?? 'bg-secondary';
    }

    /**
     * Get the appointment associated with the estimate.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the invoice associated with the estimate.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'estimate_id');
    }

    /**
     * Get all payments for the estimate through the invoice.
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'estimate_id', 'invoice_id');
    }
}