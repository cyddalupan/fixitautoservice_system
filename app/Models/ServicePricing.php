<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePricing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_type_id',
        'vehicle_type',
        'vehicle_model_id',
        'brand_name',
        'model_name',
        'variant_label',
        'price',
        'notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForServiceType($query, $serviceTypeId)
    {
        return $query->where('service_type_id', $serviceTypeId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
