<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartsOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'parts_order_id',
        'vendor_price_comparison_id',
        'inventory_item_id',
        'part_name',
        'part_number',
        'oem_number',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'core_charge',
        'core_return_required',
        'core_returned',
        'core_return_date',
        'core_refund_amount',
        'status',
        'tracking_number',
        'estimated_delivery_date',
        'actual_delivery_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'core_charge' => 'decimal:2',
        'core_return_required' => 'boolean',
        'core_returned' => 'boolean',
        'core_refund_amount' => 'decimal:2',
        'core_return_date' => 'date',
        'estimated_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function partsOrder()
    {
        return $this->belongsTo(PartsOrder::class);
    }

    public function vendorPriceComparison()
    {
        return $this->belongsTo(VendorPriceComparison::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(Inventory::class, 'inventory_item_id');
    }

    public function returnItems()
    {
        return $this->hasMany(PartsReturnItem::class);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'ordered' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'returned' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    public function getIsDeliveredAttribute()
    {
        return $this->status === 'delivered';
    }

    public function getIsReturnedAttribute()
    {
        return $this->status === 'returned';
    }

    public function getHasCoreReturnAttribute()
    {
        return $this->core_return_required && !$this->core_returned;
    }
}