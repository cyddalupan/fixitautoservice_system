<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Estimate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'appointment_id',
        'inspection_id',
        'estimate_number',
        'issue_date',
        'expiry_date',
        'mileage',
        'labor_hours',
        'labor_rate',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'tax_total',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total_amount',
        'deposit_required',
        'balance_remaining',
        'parts_total',
        'labor_total',
        'notes',
        'internal_notes',
        'customer_notes',
        'terms',
        'status',
        'service_advisor_id',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'sent_at',
        'viewed_at',
        'user_id',
        'approved_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'labor_hours' => 'decimal:2',
        'labor_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_required' => 'decimal:2',
        'balance_remaining' => 'decimal:2',
        'parts_total' => 'decimal:2',
        'labor_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class)->orderBy('sort_order');
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

        public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

public function serviceAdvisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_advisor_id');
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'inspection_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'estimate_id');
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'estimate_id', 'invoice_id');
    }

    // ── Status Helpers ──

    public const STATUSES = [
        'draft'               => 'Draft',
        'sent'                => 'Sent',
        'viewed'              => 'Viewed',
        'waiting_approval'    => 'Waiting Approval',
        'approved'            => 'Approved',
        'rejected'            => 'Rejected',
        'expired'             => 'Expired',
        'converted_to_work_order' => 'Converted to Work Order',
        'converted_to_repair_order' => 'Converted to Repair Order',
    ];

    public const STATUS_BADGES = [
        'draft'               => 'badge-draft',
        'sent'                => 'badge-sent',
        'viewed'              => 'badge-viewed',
        'waiting_approval'    => 'badge-waiting',
        'approved'            => 'badge-approved',
        'rejected'            => 'badge-rejected',
        'expired'             => 'badge-expired',
        'converted_to_work_order' => 'badge-converted',
        'converted_to_repair_order' => 'badge-converted',
    ];

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function isExpired(): bool
    {
        return $this->expiry_date < now();
    }

    public function canConvertToWorkOrder(): bool
    {
        return $this->status === 'approved' && !$this->isExpired() && !$this->workOrder;
    }

    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '₱' . number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAttribute(): string
    {
        return '₱' . number_format($this->tax_amount, 2);
    }

    public function getFormattedDepositAttribute(): string
    {
        return '₱' . number_format($this->deposit_required, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '₱' . number_format($this->balance_remaining, 2);
    }

    public function getFormattedPartsTotalAttribute(): string
    {
        return '₱' . number_format($this->parts_total, 2);
    }

    public function getFormattedLaborTotalAttribute(): string
    {
        return '₱' . number_format($this->labor_total, 2);
    }

    public function getFormattedDiscountAttribute(): string
    {
        return '₱' . number_format($this->discount_amount, 2);
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        $map = [
            'draft'               => 'bg-secondary',
            'sent'                => 'bg-info',
            'viewed'              => 'bg-info',
            'waiting_approval'    => 'bg-warning text-dark',
            'approved'            => 'bg-success',
            'rejected'            => 'bg-danger',
            'expired'             => 'bg-dark',
            'converted_to_work_order'  => 'bg-primary',
            'converted_to_repair_order' => 'bg-primary',
        ];
        return $map[$this->status] ?? 'bg-secondary';
    }
}
