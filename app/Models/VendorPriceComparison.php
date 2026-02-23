<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPriceComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'parts_lookup_id',
        'vendor_id',
        'vendor_part_number',
        'price',
        'shipping_cost',
        'tax',
        'estimated_delivery_days',
        'in_stock',
        'quantity_available',
        'condition',
        'warranty',
        'vendor_data',
        'is_selected',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'in_stock' => 'boolean',
        'is_selected' => 'boolean',
        'vendor_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function partsLookup()
    {
        return $this->belongsTo(PartsLookup::class);
    }

    public function vendor()
    {
        return $this->belongsTo(InventorySupplier::class, 'vendor_id');
    }

    public function partsOrderItems()
    {
        return $this->hasMany(PartsOrderItem::class);
    }

    public function getTotalPriceAttribute()
    {
        return $this->price + $this->shipping_cost + $this->tax;
    }
}