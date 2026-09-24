<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A payment (down payment / full payment) recorded against a Repair Order
 * (VehicleInspection), optionally with an uploaded proof, plus a lightweight
 * verification step (pending -> verified / rejected).
 */
class RepairOrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_inspection_id',
        'customer_id',
        'amount',
        'payment_type',
        'payment_method',
        'reference_number',
        'proof_path',
        'status',
        'notes',
        'uploaded_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public const TYPES = [
        'down_payment' => 'Down Payment',
        'full_payment' => 'Full Payment',
    ];

    public const STATUSES = [
        'pending' => 'Pending Verification',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'vehicle_inspection_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->payment_type] ?? ucfirst(str_replace('_', ' ', (string) $this->payment_type));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_path ? asset('storage/' . $this->proof_path) : null;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'verified' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }
}
