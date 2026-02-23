<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
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
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active payment methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include payment methods of a specific type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the icon class for the payment method.
     */
    public function getIconClassAttribute(): string
    {
        return match($this->type) {
            'cash' => 'fas fa-money-bill-wave',
            'card' => 'fas fa-credit-card',
            'bank' => 'fas fa-university',
            'digital' => 'fas fa-mobile-alt',
            'check' => 'fas fa-money-check',
            default => 'fas fa-money-bill',
        };
    }

    /**
     * Get the color class for the payment method.
     */
    public function getColorClassAttribute(): string
    {
        return match($this->type) {
            'cash' => 'success',
            'card' => 'primary',
            'bank' => 'info',
            'digital' => 'warning',
            'check' => 'secondary',
            default => 'light',
        };
    }
}
