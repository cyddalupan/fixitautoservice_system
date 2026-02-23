<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'vin',
        'license_plate',
        'make',
        'model',
        'year',
        'color',
        'vehicle_type',
        'engine_type',
        'transmission',
        'fuel_type',
        'odometer',
        'last_service_date',
        'next_service_date',
        'service_interval_miles',
        'service_interval_months',
        'service_history_summary',
        'average_service_cost',
        'total_service_count',
        'has_warranty',
        'warranty_expiry',
        'has_recall',
        'recall_details',
        'is_active',
        'notes',
        // New VIN decoding fields
        'trim',
        'body_style',
        'drive_type',
        'manufacturer',
        'plant_code',
        'series',
        'vehicle_class',
        'doors',
        'passenger_capacity',
        'gross_vehicle_weight',
        'country_of_origin',
        'vin_decoded_at',
        'vin_source',
        'vin_valid',
        'vin_validation_notes',
        'open_recall_count',
        'last_recall_check',
        'recall_check_required',
        'detailed_service_history',
        'first_service_date',
        'total_service_cost',
    ];

    protected $casts = [
        'year' => 'integer',
        'odometer' => 'integer',
        'last_service_date' => 'date',
        'next_service_date' => 'date',
        'service_interval_miles' => 'integer',
        'service_interval_months' => 'integer',
        'service_history_summary' => 'array',
        'average_service_cost' => 'decimal:2',
        'total_service_count' => 'integer',
        'has_warranty' => 'boolean',
        'warranty_expiry' => 'date',
        'has_recall' => 'boolean',
        'is_active' => 'boolean',
        // New VIN decoding casts
        'doors' => 'integer',
        'passenger_capacity' => 'integer',
        'gross_vehicle_weight' => 'decimal:2',
        'vin_decoded_at' => 'datetime',
        'vin_valid' => 'boolean',
        'open_recall_count' => 'integer',
        'last_recall_check' => 'date',
        'recall_check_required' => 'boolean',
        'detailed_service_history' => 'array',
        'first_service_date' => 'date',
        'total_service_cost' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceRecords()
    {
        return $this->hasMany(ServiceRecord::class);
    }

    /**
     * Get the vehicle recalls.
     */
    public function recalls()
    {
        return $this->hasMany(VehicleRecall::class);
    }

    /**
     * Get the open recalls.
     */
    public function openRecalls()
    {
        return $this->recalls()->where('status', 'open');
    }

    /**
     * Get the VIN decoder cache entry for this vehicle.
     */
    public function vinCache()
    {
        return $this->hasOne(VINDecoderCache::class, 'vin', 'vin');
    }

    public function getFullDescriptionAttribute()
    {
        return "{$this->year} {$this->make} {$this->model}";
    }

    public function getServiceHistoryAttribute()
    {
        return $this->serviceRecords()
            ->orderBy('service_date', 'desc')
            ->get();
    }

    public function getTotalServiceCostAttribute()
    {
        return $this->serviceRecords()->sum('final_amount');
    }

    public function getLastServiceAttribute()
    {
        return $this->serviceRecords()
            ->orderBy('service_date', 'desc')
            ->first();
    }

    public function getNextServiceDueAttribute()
    {
        if (!$this->next_service_date) {
            return null;
        }

        $daysUntilService = now()->diffInDays($this->next_service_date, false);
        
        if ($daysUntilService < 0) {
            return 'Overdue';
        } elseif ($daysUntilService == 0) {
            return 'Today';
        } elseif ($daysUntilService <= 7) {
            return 'This week';
        } elseif ($daysUntilService <= 30) {
            return 'This month';
        } else {
            return 'Upcoming';
        }
    }

    public function getServiceStatusColorAttribute()
    {
        $status = $this->getNextServiceDueAttribute();
        
        return match($status) {
            'Overdue' => 'danger',
            'Today' => 'warning',
            'This week' => 'info',
            'This month' => 'primary',
            default => 'success',
        };
    }

    public function calculateNextServiceDate()
    {
        if (!$this->last_service_date) {
            return null;
        }

        $nextDate = $this->last_service_date->copy();
        
        // Add service interval months
        if ($this->service_interval_months > 0) {
            $nextDate->addMonths($this->service_interval_months);
        }
        
        return $nextDate;
    }

    public function calculateNextServiceOdometer()
    {
        if ($this->service_interval_miles > 0) {
            return $this->odometer + $this->service_interval_miles;
        }
        
        return null;
    }

    /**
     * Check if VIN has been decoded.
     */
    public function isVINDecoded(): bool
    {
        return !empty($this->vin_decoded_at);
    }

    /**
     * Get VIN decoding status.
     */
    public function getVINDecodingStatusAttribute(): string
    {
        if (!$this->vin) {
            return 'no_vin';
        }

        if (!$this->isVINDecoded()) {
            return 'not_decoded';
        }

        if (!$this->vin_valid) {
            return 'invalid';
        }

        return 'decoded';
    }

    /**
     * Get VIN decoding status with color.
     */
    public function getVINDecodingStatusWithColorAttribute(): array
    {
        $status = $this->vin_decoding_status;

        $statusMap = [
            'no_vin' => ['color' => 'secondary', 'label' => 'No VIN'],
            'not_decoded' => ['color' => 'warning', 'label' => 'Not Decoded'],
            'invalid' => ['color' => 'danger', 'label' => 'Invalid VIN'],
            'decoded' => ['color' => 'success', 'label' => 'Decoded'],
        ];

        return $statusMap[$status] ?? ['color' => 'secondary', 'label' => 'Unknown'];
    }

    /**
     * Check if recall check is needed.
     */
    public function needsRecallCheck(): bool
    {
        if (!$this->last_recall_check) {
            return true;
        }

        // Check every 30 days
        return $this->last_recall_check->diffInDays(now()) > 30;
    }

    /**
     * Get recall status.
     */
    public function getRecallStatusAttribute(): string
    {
        if ($this->open_recall_count > 0) {
            return 'has_recalls';
        }

        if ($this->needsRecallCheck()) {
            return 'needs_check';
        }

        return 'clear';
    }

    /**
     * Get recall status with color.
     */
    public function getRecallStatusWithColorAttribute(): array
    {
        $status = $this->recall_status;

        $statusMap = [
            'has_recalls' => ['color' => 'danger', 'label' => 'Has Recalls'],
            'needs_check' => ['color' => 'warning', 'label' => 'Needs Check'],
            'clear' => ['color' => 'success', 'label' => 'Clear'],
        ];

        return $statusMap[$status] ?? ['color' => 'secondary', 'label' => 'Unknown'];
    }

    /**
     * Get detailed vehicle specifications if VIN is decoded.
     */
    public function getSpecificationsAttribute(): ?array
    {
        if (!$this->vinCache) {
            return null;
        }

        return $this->vinCache->specifications;
    }

    /**
     * Get vehicle features if VIN is decoded.
     */
    public function getFeaturesAttribute(): ?array
    {
        if (!$this->vinCache) {
            return null;
        }

        return $this->vinCache->features;
    }

    /**
     * Get maintenance schedule if VIN is decoded.
     */
    public function getMaintenanceScheduleAttribute(): ?array
    {
        if (!$this->vinCache) {
            return null;
        }

        return $this->vinCache->maintenance_schedule;
    }

    /**
     * Get formatted vehicle description with VIN details.
     */
    public function getDetailedDescriptionAttribute(): string
    {
        $description = "{$this->year} {$this->make} {$this->model}";

        if ($this->trim) {
            $description .= " {$this->trim}";
        }

        if ($this->body_style) {
            $description .= " ({$this->body_style})";
        }

        if ($this->vin) {
            $description .= " - VIN: {$this->vin}";
        }

        return $description;
    }

    /**
     * Update recall count based on open recalls.
     */
    public function updateRecallCount(): void
    {
        $openCount = $this->openRecalls()->count();
        
        $this->update([
            'open_recall_count' => $openCount,
            'has_recall' => $openCount > 0,
            'last_recall_check' => now(),
            'recall_check_required' => false,
        ]);
    }

    /**
     * Mark VIN as decoded with source information.
     */
    public function markVINAsDecoded(string $source = 'manual', bool $valid = true, ?string $notes = null): void
    {
        $this->update([
            'vin_decoded_at' => now(),
            'vin_source' => $source,
            'vin_valid' => $valid,
            'vin_validation_notes' => $notes,
        ]);
    }
}