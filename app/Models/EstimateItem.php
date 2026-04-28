<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id',
        'inventory_id',
        'item_name',
        'category',
        'quantity',
        'unit_price',
        'discount',
        'discount_type',
        'tax',
        'tax_rate',
        'subtotal',
        'total_price',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'discount'    => 'decimal:2',
        'tax'         => 'decimal:2',
        'tax_rate'    => 'decimal:2',
        'subtotal'    => 'decimal:2',
        'total_price' => 'decimal:2',
        'sort_order'  => 'integer',
    ];

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return '₱' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return '₱' . number_format($this->total_price, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '₱' . number_format($this->subtotal, 2);
    }
}
