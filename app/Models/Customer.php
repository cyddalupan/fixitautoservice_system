<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'address',
        'facebook_profile', 'profile_picture', 'last_visit',
        'is_active', 'notes', 'source',
    ];

    protected $appends = [
        'full_name',
        'name',
        'total_vehicles',
        'total_services',
        'total_spent',
        'average_service_cost',
        'last_service_date',
        'upcoming_services',
    ];

    protected $casts = [
        'last_visit' => 'datetime',
        'form_submitted_at' => 'datetime',
        'is_active' => 'boolean',
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
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getNameAttribute()
    {
        return $this->full_name;
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
        return $this->serviceRecords()->sum('total_cost');
    }

    public function getAverageServiceCostAttribute()
    {
        return $this->serviceRecords()->avg('total_cost') ?? 0;
    }

    public function getLastServiceDateAttribute()
    {
        $last = $this->serviceRecords()->latest('service_date')->first();
        return $last ? $last->service_date : null;
    }

    public function getUpcomingServicesAttribute()
    {
        return $this->serviceRecords()
            ->where('service_date', '>=', now())
            ->orderBy('service_date')
            ->get();
    }

    public function getAvatarAttribute()
    {
        if ($this->profile_picture) {
            return Storage::url($this->profile_picture);
        }
        
        // Generate initials avatar
        $name = $this->full_name;
        $initials = '';
        
        if (!empty($this->first_name)) {
            $initials .= strtoupper(substr($this->first_name, 0, 1));
        }
        if (!empty($this->last_name)) {
            $initials .= strtoupper(substr($this->last_name, 0, 1));
        }
        
        if (empty($initials)) {
            $initials = '?';
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF&bold=true&size=128';
    }

    public function getHasProfilePictureAttribute()
    {
        return !is_null($this->profile_picture);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function latestQuotation()
    {
        return $this->hasOne(Quotation::class)->latestOfMany();
    }

    /**
     * Get the portal user associated with this customer.
     */
    public function portalUser()
    {
        return $this->hasOne(PortalUser::class, 'customer_id');
    }
}
