<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionFindingGroup extends Model
{
    protected $fillable = [
        'inspection_id',
        'name',
        'auto_name',
        'labor_cost',
        'sort_order',
    ];

    protected $casts = [
        'auto_name' => 'boolean',
        'labor_cost' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'inspection_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(InspectionFinding::class, 'group_id')->orderBy('sort_order');
    }

    /**
     * Sum of parts (quantity * unit_price) for this group.
     */
    public function getPartsTotalAttribute(): float
    {
        return (float) $this->findings->sum(function ($f) {
            return (float) $f->quantity * (float) ($f->unit_price ?? 0);
        });
    }

    /**
     * Parts total + labor cost.
     */
    public function getTotalAttribute(): float
    {
        return $this->parts_total + (float) $this->labor_cost;
    }
}
