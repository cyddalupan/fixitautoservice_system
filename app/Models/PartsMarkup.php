<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PartsMarkup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'markup_name',
        'markup_type',
        'markup_value',
        'category_id',
        'supplier_id',
        'minimum_cost',
        'maximum_cost',
        'minimum_retail',
        'maximum_retail',
        'apply_to_all_categories',
        'apply_to_all_suppliers',
        'is_active',
        'priority',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'markup_value' => 'decimal:2',
        'minimum_cost' => 'decimal:2',
        'maximum_cost' => 'decimal:2',
        'minimum_retail' => 'decimal:2',
        'maximum_retail' => 'decimal:2',
        'apply_to_all_categories' => 'boolean',
        'apply_to_all_suppliers' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
    ];

    /**
     * Relationship with inventory category
     */
    public function category()
    {
        return $this->belongsTo(InventoryCategory::class);
    }

    /**
     * Relationship with supplier
     */
    public function supplier()
    {
        return $this->belongsTo(InventorySupplier::class);
    }

    /**
     * Get active markup rules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get markup rules effective for a given date
     */
    public function scopeEffectiveOn($query, $date = null)
    {
        $date = $date ?? now();
        
        return $query->where(function($q) use ($date) {
            $q->whereNull('effective_from')
              ->orWhere('effective_from', '<=', $date);
        })->where(function($q) use ($date) {
            $q->whereNull('effective_to')
              ->orWhere('effective_to', '>=', $date);
        });
    }

    /**
     * Calculate retail price based on cost
     */
    public function calculateRetailPrice($cost)
    {
        if ($this->minimum_cost && $cost < $this->minimum_cost) {
            return null; // Doesn't apply
        }
        
        if ($this->maximum_cost && $cost > $this->maximum_cost) {
            return null; // Doesn't apply
        }

        $retail = 0;
        
        switch ($this->markup_type) {
            case 'percentage':
                $retail = $cost * (1 + ($this->markup_value / 100));
                break;
            case 'fixed':
                $retail = $cost + $this->markup_value;
                break;
            case 'tiered':
                // For simplicity, using fixed markup for tiered
                $retail = $cost + $this->markup_value;
                break;
        }

        // Apply minimum/maximum retail constraints
        if ($this->minimum_retail && $retail < $this->minimum_retail) {
            $retail = $this->minimum_retail;
        }
        
        if ($this->maximum_retail && $retail > $this->maximum_retail) {
            $retail = $this->maximum_retail;
        }

        return round($retail, 2);
    }
}
