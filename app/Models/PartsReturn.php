<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartsReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'parts_order_id',
        'vendor_id',
        'reason',
        'description',
        'status',
        'rma_number',
        'return_request_date',
        'return_approval_date',
        'return_shipped_date',
        'return_received_date',
        'refund_date',
        'refund_amount',
        'restocking_fee',
        'shipping_method',
        'tracking_number',
        'carrier',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'return_request_date' => 'date',
        'return_approval_date' => 'date',
        'return_shipped_date' => 'date',
        'return_received_date' => 'date',
        'refund_date' => 'date',
        'refund_amount' => 'decimal:2',
        'restocking_fee' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->return_number)) {
                $model->return_number = 'RET-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
            if (empty($model->return_request_date)) {
                $model->return_request_date = now();
            }
        });
    }

    public function partsOrder()
    {
        return $this->belongsTo(PartsOrder::class);
    }

    public function vendor()
    {
        return $this->belongsTo(InventorySupplier::class, 'vendor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PartsReturnItem::class);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'requested' => 'warning',
            'approved' => 'info',
            'shipped' => 'primary',
            'received' => 'success',
            'refunded' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getTotalRefundAttribute()
    {
        return $this->refund_amount - $this->restocking_fee;
    }

    public function getIsApprovedAttribute()
    {
        return in_array($this->status, ['approved', 'shipped', 'received', 'refunded']);
    }

    public function getIsCompletedAttribute()
    {
        return in_array($this->status, ['refunded', 'rejected']);
    }
}