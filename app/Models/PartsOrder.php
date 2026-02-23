<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartsOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'vendor_id',
        'work_order_id',
        'customer_id',
        'vehicle_id',
        'status',
        'subtotal',
        'shipping',
        'tax',
        'total',
        'core_charge',
        'core_refund',
        'shipping_method',
        'tracking_number',
        'carrier',
        'order_date',
        'estimated_delivery_date',
        'actual_delivery_date',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'core_charge' => 'decimal:2',
        'core_refund' => 'decimal:2',
        'order_date' => 'date',
        'estimated_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->order_number)) {
                $model->order_number = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
            if (empty($model->order_date)) {
                $model->order_date = now();
            }
        });
    }

    public function vendor()
    {
        return $this->belongsTo(InventorySupplier::class, 'vendor_id');
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
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
        return $this->hasMany(PartsOrderItem::class);
    }

    public function returns()
    {
        return $this->hasMany(PartsReturn::class);
    }

    public function coreReturns()
    {
        return $this->hasMany(CoreReturn::class);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'pending' => 'warning',
            'ordered' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'returned' => 'danger',
            default => 'secondary',
        };
    }

    public function getIsEditableAttribute()
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    public function getIsCancellableAttribute()
    {
        return in_array($this->status, ['draft', 'pending', 'ordered']);
    }
}