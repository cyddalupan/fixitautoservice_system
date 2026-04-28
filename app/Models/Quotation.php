<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'barangay',
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'color',
        'engine_type',
        'transmission',
        'chassis_number',
        'mileage',
        'license_plate',
        'vin_number',
        'preferred_date',
        'preferred_time',
        'service_type',
        'service_checklist',
        'parts_preference',
        'budget_min',
        'budget_max',
        'service_description',
        'photos',
        'consent_contact',
        'status',
        'admin_notes',
        'customer_id',
        'vehicle_id',
    ];

    protected $casts = [
        'vehicle_year' => 'integer',
        'mileage' => 'integer',
        'service_type' => 'array',
        'service_checklist' => 'array',
        'photos' => 'array',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'preferred_date' => 'date',
        'consent_contact' => 'boolean',
    ];

    /**
     * Get the customer linked to this quotation.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the vehicle linked to this quotation.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope for new leads (fresh submissions).
     */
    public function scopeNewLeads($query)
    {
        return $query->where('status', 'new_lead');
    }

    /**
     * Status labels for the CRM pipeline.
     */
    public static function statusLabels(): array
    {
        return [
            'new_lead'              => 'New Lead',
            'contacted'             => 'Contacted',
            'converted_to_customer' => 'Converted to Customer',
            'appointment_booked'    => 'Appointment Booked',
            'won'                   => 'Won',
            'lost'                  => 'Lost',
            'archived'              => 'Archived',
        ];
    }

    /**
     * Status colors for badges.
     */
    public static function statusColors(): array
    {
        return [
            'new_lead'              => 'warning',
            'contacted'             => 'info',
            'converted_to_customer' => 'success',
            'appointment_booked'    => 'primary',
            'won'                   => 'success',
            'lost'                  => 'danger',
            'archived'              => 'secondary',
        ];
    }
}
