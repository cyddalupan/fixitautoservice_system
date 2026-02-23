<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxRate extends Model
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
        'rate',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active tax rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the formatted rate with percentage symbol.
     */
    public function getFormattedRateAttribute(): string
    {
        return $this->rate . '%';
    }

    /**
     * Calculate tax amount for a given subtotal.
     */
    public function calculateTax(float $subtotal): float
    {
        return ($subtotal * $this->rate) / 100;
    }
}
