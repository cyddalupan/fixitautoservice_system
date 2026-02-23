<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartsReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'parts_return_id',
        'parts_order_item_id',
        'quantity',
        'unit_price',
        'total_price',
        'refund_amount',
        'restocking_fee',
        'core_return',
        'core_refund',
        'condition',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'restocking_fee' => 'decimal:2',
        'core_return' => 'boolean',
        'core_refund' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function partsReturn()
    {
        return $this->belongsTo(PartsReturn::class);
    }

    public function partsOrderItem()
    {
        return $this->belongsTo(PartsOrderItem::class);
    }

    public function getNetRefundAttribute()
    {
        return $this->refund_amount - $this->restocking_fee;
    }
}