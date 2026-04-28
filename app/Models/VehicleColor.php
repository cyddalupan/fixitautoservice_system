<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleColor extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'hex_code',
        'popularity_score',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'popularity_score' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('popularity_score', 'desc');
    }
}
