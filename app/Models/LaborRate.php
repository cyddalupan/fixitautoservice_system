<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaborRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rate_name',
        'rate_code',
        'description',
        'hourly_rate',
        'minimum_charge',
        'is_default',
        'is_active',
        'sort_order',
        'applicable_categories',
        'applicable_technicians',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'minimum_charge' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'applicable_categories' => 'array',
        'applicable_technicians' => 'array',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
    ];

    /**
     * Get the active labor rates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default labor rate
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get labor rates effective for a given date
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
     * Calculate labor cost for given hours
     */
    public function calculateCost($hours)
    {
        $cost = $hours * $this->hourly_rate;
        return max($cost, $this->minimum_charge);
    }
}
