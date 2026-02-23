<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discount extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active discounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include discounts with a specific code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Calculate discount amount for a given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            return ($subtotal * $this->value) / 100;
        }
        
        return $this->value; // fixed amount
    }

    /**
     * Get the formatted discount value.
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->type === 'percentage') {
            return $this->value . '%';
        }
        
        return '₱' . number_format($this->value, 2);
    }
}
