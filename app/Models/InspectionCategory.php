<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InspectionCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_name',
        'category_description',
        'category_type',
        'vehicle_type',
        'sequence',
        'is_active',
        'is_required',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Relationships
     */
    public function items()
    {
        return $this->hasMany(InspectionItem::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('category_type', $type);
    }

    public function scopeByVehicleType($query, $vehicleType)
    {
        if ($vehicleType === 'all') {
            return $query;
        }
        return $query->where('vehicle_type', $vehicleType)->orWhere('vehicle_type', 'all');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence')->orderBy('category_name');
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->category_type) {
            'standard' => 'Standard',
            'safety' => 'Safety',
            'custom' => 'Custom',
            'manufacturer' => 'Manufacturer',
            default => ucfirst($this->category_type),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->category_type) {
            'standard' => 'primary',
            'safety' => 'warning',
            'custom' => 'info',
            'manufacturer' => 'success',
            default => 'secondary',
        };
    }

    public function getVehicleTypeLabelAttribute(): string
    {
        return match($this->vehicle_type) {
            'car' => 'Car',
            'truck' => 'Truck',
            'suv' => 'SUV',
            'motorcycle' => 'Motorcycle',
            'all' => 'All Vehicles',
            default => ucfirst($this->vehicle_type),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_active) {
            return '<span class="badge bg-secondary">Inactive</span>';
        }
        
        if ($this->is_required) {
            return '<span class="badge bg-danger">Required</span>';
        }
        
        return '<span class="badge bg-success">Active</span>';
    }

    /**
     * Methods
     */
    public static function getDefaultCategories(): array
    {
        return [
            [
                'category_name' => 'Fluids & Lubricants',
                'category_description' => 'Check all fluid levels and conditions',
                'category_type' => 'standard',
                'vehicle_type' => 'all',
                'sequence' => 1,
                'is_active' => true,
                'is_required' => true,
            ],
            [
                'category_name' => 'Braking System',
                'category_description' => 'Inspect brakes, pads, rotors, and fluid',
                'category_type' => 'safety',
                'vehicle_type' => 'all',
                'sequence' => 2,
                'is_active' => true,
                'is_required' => true,
            ],
            [
                'category_name' => 'Tires & Wheels',
                'category_description' => 'Check tire condition, pressure, and alignment',
                'category_type' => 'safety',
                'vehicle_type' => 'all',
                'sequence' => 3,
                'is_active' => true,
                'is_required' => true,
            ],
            [
                'category_name' => 'Lighting & Electrical',
                'category_description' => 'Test all lights and electrical components',
                'category_type' => 'safety',
                'vehicle_type' => 'all',
                'sequence' => 4,
                'is_active' => true,
                'is_required' => true,
            ],
            [
                'category_name' => 'Engine & Performance',
                'category_description' => 'Check engine components and performance',
                'category_type' => 'standard',
                'vehicle_type' => 'all',
                'sequence' => 5,
                'is_active' => true,
                'is_required' => false,
            ],
            [
                'category_name' => 'Suspension & Steering',
                'category_description' => 'Inspect suspension and steering components',
                'category_type' => 'safety',
                'vehicle_type' => 'all',
                'sequence' => 6,
                'is_active' => true,
                'is_required' => false,
            ],
            [
                'category_name' => 'Exhaust System',
                'category_description' => 'Check exhaust components and emissions',
                'category_type' => 'standard',
                'vehicle_type' => 'all',
                'sequence' => 7,
                'is_active' => true,
                'is_required' => false,
            ],
            [
                'category_name' => 'HVAC System',
                'category_description' => 'Test heating, ventilation, and air conditioning',
                'category_type' => 'standard',
                'vehicle_type' => 'all',
                'sequence' => 8,
                'is_active' => true,
                'is_required' => false,
            ],
            [
                'category_name' => 'Interior & Comfort',
                'category_description' => 'Check interior components and comfort features',
                'category_type' => 'standard',
                'vehicle_type' => 'all',
                'sequence' => 9,
                'is_active' => true,
                'is_required' => false,
            ],
            [
                'category_name' => 'Exterior & Body',
                'category_description' => 'Inspect exterior condition and body panels',
                'category_type' => 'standard',
                'vehicle_type' => 'all',
                'sequence' => 10,
                'is_active' => true,
                'is_required' => false,
            ],
        ];
    }

    public static function seedDefaultCategories(): void
    {
        $categories = self::getDefaultCategories();
        
        foreach ($categories as $categoryData) {
            self::firstOrCreate(
                ['category_name' => $categoryData['category_name']],
                $categoryData
            );
        }
    }

    public function getCategorySummary(): array
    {
        return [
            'id' => $this->id,
            'category_name' => $this->category_name,
            'category_description' => $this->category_description,
            'category_type' => $this->category_type,
            'type_label' => $this->type_label,
            'type_color' => $this->type_color,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_type_label' => $this->vehicle_type_label,
            'sequence' => $this->sequence,
            'is_active' => $this->is_active,
            'is_required' => $this->is_required,
            'status_badge' => $this->status_badge,
            'items_count' => $this->items()->count(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}