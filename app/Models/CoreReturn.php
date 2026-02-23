<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoreReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'core_return_number',
        'parts_order_id',
        'vendor_id',
        'core_type',
        'core_part_number',
        'condition',
        'description',
        'status',
        'core_charge',
        'expected_refund',
        'actual_refund',
        'return_due_date',
        'return_shipped_date',
        'return_received_date',
        'refund_date',
        'shipping_method',
        'tracking_number',
        'carrier',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'core_charge' => 'decimal:2',
        'expected_refund' => 'decimal:2',
        'actual_refund' => 'decimal:2',
        'return_due_date' => 'date',
        'return_shipped_date' => 'date',
        'return_received_date' => 'date',
        'refund_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->core_return_number)) {
                $model->core_return_number = 'CORE-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
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

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'shipped' => 'primary',
            'received' => 'info',
            'refunded' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getIsOverdueAttribute()
    {
        return $this->return_due_date < now() && $this->status === 'pending';
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }
        return now()->diffInDays($this->return_due_date);
    }

    public function getRefundDifferenceAttribute()
    {
        return $this->actual_refund - $this->expected_refund;
    }
}