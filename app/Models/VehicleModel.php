<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VehicleModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_brand_id',
        'name',
        'slug',
        'year_start',
        'year_end',
        'body_type',
        'vehicle_type',
        'popularity_score',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    /**
     * Get the brand that owns the model.
     */
    public function brand()
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
    }

    /**
     * Scope a query to only include active models.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by popularity.
     */
    public function scopePopular($query)
    {
        return $query->orderBy('popularity_score', 'desc');
    }

    /**
     * Scope a query to filter by brand.
     */
    public function scopeForBrand($query, $brandId)
    {
        return $query->where('vehicle_brand_id', $brandId);
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeForYear($query, $year)
    {
        return $query->where(function($q) use ($year) {
            $q->whereNull('year_start')
              ->orWhere('year_start', '<=', $year);
        })->where(function($q) use ($year) {
            $q->whereNull('year_end')
              ->orWhere('year_end', '>=', $year);
        });
    }

    /**
     * Increment the popularity score.
     */
    public function incrementPopularity($points = 1)
    {
        $this->increment('popularity_score', $points);
        $this->brand->incrementPopularity($points);
    }

    /**
     * Get the display name with brand.
     */
    public function getFullNameAttribute()
    {
        return $this->brand ? $this->brand->name . ' ' . $this->name : $this->name;
    }
}