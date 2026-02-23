<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InspectionTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'template_name',
        'template_description',
        'template_type',
        'vehicle_type',
        'categories',
        'is_active',
        'is_default',
        'metadata',
    ];

    protected $casts = [
        'categories' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('template_type', $type);
    }

    public function scopeByVehicleType($query, $vehicleType)
    {
        if ($vehicleType === 'all') {
            return $query;
        }
        return $query->where('vehicle_type', $vehicleType)->orWhere('vehicle_type', 'all');
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->template_type) {
            'standard' => 'Standard Inspection',
            'safety' => 'Safety Inspection',
            'pre_purchase' => 'Pre-Purchase Inspection',
            'post_service' => 'Post-Service Inspection',
            'comprehensive' => 'Comprehensive Inspection',
            'quick_check' => 'Quick Check',
            default => ucfirst(str_replace('_', ' ', $this->template_type)),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->template_type) {
            'standard' => 'primary',
            'safety' => 'warning',
            'pre_purchase' => 'info',
            'post_service' => 'success',
            'comprehensive' => 'purple',
            'quick_check' => 'secondary',
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
        
        if ($this->is_default) {
            return '<span class="badge bg-primary">Default</span>';
        }
        
        return '<span class="badge bg-success">Active</span>';
    }

    public function getCategoryCountAttribute(): int
    {
        return $this->categories ? count($this->categories) : 0;
    }

    /**
     * Methods
     */
    public static function getDefaultTemplates(): array
    {
        return [
            [
                'template_name' => 'Standard Pre-Service Inspection',
                'template_description' => 'Comprehensive vehicle inspection before service',
                'template_type' => 'standard',
                'vehicle_type' => 'all',
                'categories' => [1, 2, 3, 4, 5], // Fluids, Brakes, Tires, Lighting, Engine
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'template_name' => 'Safety Inspection',
                'template_description' => 'Focus on safety-critical components only',
                'template_type' => 'safety',
                'vehicle_type' => 'all',
                'categories' => [2, 3, 4, 6], // Brakes, Tires, Lighting, Suspension
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'template_name' => 'Quick Check',
                'template_description' => 'Basic 10-point inspection',
                'template_type' => 'quick_check',
                'vehicle_type' => 'all',
                'categories' => [1, 2, 3, 4], // Fluids, Brakes, Tires, Lighting
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'template_name' => 'Comprehensive Inspection',
                'template_description' => 'Full vehicle inspection including all categories',
                'template_type' => 'comprehensive',
                'vehicle_type' => 'all',
                'categories' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], // All categories
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'template_name' => 'Post-Service Verification',
                'template_description' => 'Verify completed work and check for issues',
                'template_type' => 'post_service',
                'vehicle_type' => 'all',
                'categories' => [1, 2, 3, 4], // Fluids, Brakes, Tires, Lighting
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'template_name' => 'Pre-Purchase Inspection',
                'template_description' => 'Detailed inspection for vehicle buyers',
                'template_type' => 'pre_purchase',
                'vehicle_type' => 'all',
                'categories' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], // All categories
                'is_active' => true,
                'is_default' => false,
            ],
        ];
    }

    public static function seedDefaultTemplates(): void
    {
        $templates = self::getDefaultTemplates();
        
        foreach ($templates as $templateData) {
            self::firstOrCreate(
                ['template_name' => $templateData['template_name']],
                $templateData
            );
        }
    }

    public function getTemplateSummary(): array
    {
        return [
            'id' => $this->id,
            'template_name' => $this->template_name,
            'template_description' => $this->template_description,
            'template_type' => $this->template_type,
            'type_label' => $this->type_label,
            'type_color' => $this->type_color,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_type_label' => $this->vehicle_type_label,
            'category_count' => $this->category_count,
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
            'status_badge' => $this->status_badge,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function applyToInspection(VehicleInspection $inspection): void
    {
        if (!$this->categories) {
            return;
        }
        
        // Get categories with their items
        $categories = InspectionCategory::whereIn('id', $this->categories)
            ->with(['items' => function($query) {
                $query->orderBy('sequence');
            }])
            ->get();
        
        $sequence = 1;
        
        foreach ($categories as $category) {
            foreach ($category->items as $item) {
                // Create inspection item from template item
                InspectionItem::create([
                    'inspection_id' => $inspection->id,
                    'category_id' => $category->id,
                    'item_name' => $item->item_name,
                    'item_description' => $item->item_description,
                    'item_type' => $item->item_type,
                    'item_unit' => $item->item_unit,
                    'min_value' => $item->min_value,
                    'max_value' => $item->max_value,
                    'spec_value' => $item->spec_value,
                    'spec_source' => $item->spec_source,
                    'item_status' => 'pending',
                    'sequence' => $sequence++,
                ]);
            }
        }
        
        $inspection->updateItemCounts();
    }
}