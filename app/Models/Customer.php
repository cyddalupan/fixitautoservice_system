<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'date_of_birth',
        'customer_type',
        'company_name',
        'tax_id',
        'credit_limit',
        'balance',
        'payment_terms',
        'is_active',
        'customer_since',
        'loyalty_points',
        'preferred_contact',
        'notes',
        'preferences',
        'segment',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'customer_since' => 'date',
        'date_of_birth' => 'date',
        'preferences' => 'array',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function serviceRecords()
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function notes()
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getTotalVehiclesAttribute()
    {
        return $this->vehicles()->count();
    }

    public function getTotalServicesAttribute()
    {
        return $this->serviceRecords()->count();
    }

    public function getTotalSpentAttribute()
    {
        return $this->serviceRecords()->sum('final_amount');
    }

    public function getAverageServiceCostAttribute()
    {
        $count = $this->serviceRecords()->count();
        return $count > 0 ? $this->getTotalSpentAttribute() / $count : 0;
    }

    public function getLastServiceDateAttribute()
    {
        $lastService = $this->serviceRecords()->latest('service_date')->first();
        return $lastService ? $lastService->service_date : null;
    }

    public function getUpcomingServicesAttribute()
    {
        return $this->vehicles()
            ->whereNotNull('next_service_date')
            ->where('next_service_date', '>=', now())
            ->orderBy('next_service_date')
            ->get();
    }
}