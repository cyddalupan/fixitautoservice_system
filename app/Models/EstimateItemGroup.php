<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A group of Repair Quotation items that share ONE labor price.
 * (Mirrors InspectionFindingGroup, but for estimates.)
 */
class EstimateItemGroup extends Model
{
    protected $fillable = ['estimate_id', 'name', 'labor_cost', 'sort_order'];

    protected $casts = [
        'labor_cost' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class, 'group_id');
    }

    /** Σ qty × unit_price of the parts in this group (pre-discount). */
    public function getPartsTotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price);
    }

    /** Parts + the shared labor price. */
    public function getTotalAttribute(): float
    {
        return $this->parts_total + (float) $this->labor_cost;
    }
}
