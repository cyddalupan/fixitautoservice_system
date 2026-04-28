<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_items';

    protected $fillable = [
        'name',
        'description',
        'retail_price',
        'is_active',
        'category_id',
        'estimated_duration_minutes',
        'notes',
    ];

    protected $casts = [
        'retail_price' => 'decimal:2',
        'is_active' => 'boolean',
        'estimated_duration_minutes' => 'integer',
    ];

    /**
     * Scope to get only active service items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by name
     */
    public function scopeOrderByName($query)
    {
        return $query->orderBy('name');
    }
}
