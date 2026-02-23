<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartsLookup extends Model
{
    use HasFactory;

    protected $fillable = [
        'vin',
        'make',
        'model',
        'year',
        'engine',
        'transmission',
        'part_category',
        'part_name',
        'part_number',
        'oem_number',
        'description',
        'search_results',
        'customer_id',
        'vehicle_id',
        'work_order_id',
        'created_by',
    ];

    protected $casts = [
        'search_results' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vendorPriceComparisons()
    {
        return $this->hasMany(VendorPriceComparison::class);
    }
}