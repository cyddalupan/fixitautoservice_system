<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class VINDecoderCache extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vin_decoder_cache';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vin',
        'decoded_data',
        'make',
        'model',
        'year',
        'trim',
        'engine',
        'transmission',
        'drive_type',
        'body_style',
        'fuel_type',
        'manufacturer',
        'plant_code',
        'cache_hits',
        'last_accessed_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'decoded_data' => 'array',
        'last_accessed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Scope a query to only include non-expired cache entries.
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope a query to only include cache entries for a specific make/model/year.
     */
    public function scopeByVehicleDetails(Builder $query, string $make, string $model, int $year): Builder
    {
        return $query->where('make', $make)
                    ->where('model', $model)
                    ->where('year', $year);
    }

    /**
     * Scope a query to only include cache entries that need to be refreshed.
     */
    public function scopeNeedsRefresh(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->whereNotNull('expires_at')
                  ->where('expires_at', '<=', now());
        });
    }

    /**
     * Increment the cache hit counter and update last accessed timestamp.
     */
    public function incrementHit(): void
    {
        $this->increment('cache_hits');
        $this->last_accessed_at = now();
        $this->save();
    }

    /**
     * Check if the cache entry is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Get the cache age in days.
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Get the time until expiration in days.
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Get basic vehicle information from decoded data.
     */
    public function getBasicInfoAttribute(): array
    {
        return [
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'trim' => $this->trim,
            'engine' => $this->engine,
            'transmission' => $this->transmission,
            'body_style' => $this->body_style,
        ];
    }

    /**
     * Get detailed vehicle specifications from decoded data.
     */
    public function getSpecificationsAttribute(): array
    {
        $data = $this->decoded_data;
        
        return [
            'engine' => [
                'type' => $data['EngineType'] ?? $this->engine,
                'size' => $data['DisplacementL'] ?? null,
                'cylinders' => $data['Cylinders'] ?? null,
                'horsepower' => $data['Horsepower'] ?? null,
                'torque' => $data['Torque'] ?? null,
                'fuel_system' => $data['FuelSystem'] ?? null,
            ],
            'transmission' => [
                'type' => $data['TransmissionType'] ?? $this->transmission,
                'speeds' => $data['TransmissionSpeeds'] ?? null,
            ],
            'dimensions' => [
                'wheelbase' => $data['Wheelbase'] ?? null,
                'length' => $data['Length'] ?? null,
                'width' => $data['Width'] ?? null,
                'height' => $data['Height'] ?? null,
                'curb_weight' => $data['CurbWeight'] ?? null,
            ],
            'performance' => [
                'mpg_city' => $data['MPGCity'] ?? null,
                'mpg_highway' => $data['MPGHighway'] ?? null,
                'top_speed' => $data['TopSpeed'] ?? null,
                'acceleration' => $data['Acceleration'] ?? null,
            ],
            'safety' => [
                'nhtsa_rating' => $data['NHTSARating'] ?? null,
                'iihs_rating' => $data['IIHSRating'] ?? null,
                'airbags' => $data['Airbags'] ?? null,
                'stability_control' => $data['StabilityControl'] ?? null,
            ],
        ];
    }

    /**
     * Get vehicle features from decoded data.
     */
    public function getFeaturesAttribute(): array
    {
        $data = $this->decoded_data;
        
        $features = [];
        
        // Interior features
        if (isset($data['InteriorFeatures'])) {
            $features['interior'] = is_array($data['InteriorFeatures']) 
                ? $data['InteriorFeatures'] 
                : explode(',', $data['InteriorFeatures']);
        }
        
        // Exterior features
        if (isset($data['ExteriorFeatures'])) {
            $features['exterior'] = is_array($data['ExteriorFeatures']) 
                ? $data['ExteriorFeatures'] 
                : explode(',', $data['ExteriorFeatures']);
        }
        
        // Safety features
        if (isset($data['SafetyFeatures'])) {
            $features['safety'] = is_array($data['SafetyFeatures']) 
                ? $data['SafetyFeatures'] 
                : explode(',', $data['SafetyFeatures']);
        }
        
        // Technology features
        if (isset($data['TechnologyFeatures'])) {
            $features['technology'] = is_array($data['TechnologyFeatures']) 
                ? $data['TechnologyFeatures'] 
                : explode(',', $data['TechnologyFeatures']);
        }
        
        return $features;
    }

    /**
     * Get recall information from decoded data if available.
     */
    public function getRecallInfoAttribute(): array
    {
        $data = $this->decoded_data;
        
        return [
            'has_recalls' => $data['HasRecalls'] ?? false,
            'recall_count' => $data['RecallCount'] ?? 0,
            'last_recall_check' => $data['LastRecallCheck'] ?? null,
            'recalls' => $data['Recalls'] ?? [],
        ];
    }

    /**
     * Get maintenance schedule from decoded data if available.
     */
    public function getMaintenanceScheduleAttribute(): array
    {
        $data = $this->decoded_data;
        
        return [
            'oil_change_interval' => $data['OilChangeInterval'] ?? 5000,
            'tire_rotation_interval' => $data['TireRotationInterval'] ?? 7500,
            'brake_service_interval' => $data['BrakeServiceInterval'] ?? 30000,
            'transmission_service_interval' => $data['TransmissionServiceInterval'] ?? 60000,
            'coolant_flush_interval' => $data['CoolantFlushInterval'] ?? 100000,
            'timing_belt_interval' => $data['TimingBeltInterval'] ?? 90000,
        ];
    }

    /**
     * Get the cache hit rate (hits per day).
     */
    public function getHitRateAttribute(): float
    {
        if ($this->cache_hits === 0 || $this->age_in_days === 0) {
            return 0.0;
        }

        return round($this->cache_hits / $this->age_in_days, 2);
    }

    /**
     * Check if this cache entry should be refreshed based on age and hit rate.
     */
    public function shouldRefresh(): bool
    {
        // Always refresh if expired
        if ($this->isExpired()) {
            return true;
        }

        // Refresh if cache is old and rarely used
        if ($this->age_in_days > 30 && $this->hit_rate < 0.1) {
            return true;
        }

        // Refresh if cache is very old
        if ($this->age_in_days > 90) {
            return true;
        }

        return false;
    }

    /**
     * Create a cache entry from decoded VIN data.
     */
    public static function createFromDecodedData(string $vin, array $decodedData, int $expirationDays = 30): self
    {
        $cache = new self([
            'vin' => $vin,
            'decoded_data' => $decodedData,
            'make' => $decodedData['Make'] ?? null,
            'model' => $decodedData['Model'] ?? null,
            'year' => $decodedData['Year'] ?? null,
            'trim' => $decodedData['Trim'] ?? null,
            'engine' => $decodedData['Engine'] ?? $decodedData['EngineType'] ?? null,
            'transmission' => $decodedData['Transmission'] ?? $decodedData['TransmissionType'] ?? null,
            'drive_type' => $decodedData['DriveType'] ?? null,
            'body_style' => $decodedData['BodyStyle'] ?? null,
            'fuel_type' => $decodedData['FuelType'] ?? null,
            'manufacturer' => $decodedData['Manufacturer'] ?? null,
            'plant_code' => $decodedData['PlantCode'] ?? null,
            'cache_hits' => 0,
            'last_accessed_at' => now(),
            'expires_at' => now()->addDays($expirationDays),
        ]);

        $cache->save();

        return $cache;
    }
}