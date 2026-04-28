<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
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
        'admin_notes'
    ];

    protected $casts = [
        'vehicle_year' => 'integer',
        'service_type' => 'array',
        'service_checklist' => 'array',
        'photos' => 'array',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'preferred_date' => 'date',
        'consent_contact' => 'boolean'
    ];
}
