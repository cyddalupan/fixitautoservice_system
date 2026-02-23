<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class VehicleInspection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'appointment_id',
        'customer_id',
        'vehicle_id',
        'technician_id',
        'service_advisor_id',
        'inspection_type',
        'inspection_status',
        'inspection_name',
        'inspection_notes',
        'technician_notes',
        'customer_concerns',
        'recommended_services',
        'additional_notes',
        'total_items_checked',
        'items_passed',
        'items_failed',
        'items_attention_needed',
        'items_not_applicable',
        'inspection_score',
        'has_safety_concerns',
        'has_urgent_issues',
        'has_critical_issues',
        'safety_notes',
        'urgent_issues_notes',
        'requires_customer_approval',
        'customer_approved',
        'customer_approval_method',
        'customer_approved_at',
        'customer_approval_notes',
        'customer_signature_path',
        'has_upsell_opportunities',
        'upsell_notes',
        'estimated_upsell_value',
        'actual_upsell_value',
        'photos',
        'videos',
        'documents',
        'attachments',
        'inspection_started_at',
        'inspection_completed_at',
        'report_generated_at',
        'report_sent_at',
        'created_by',
        'updated_by',
        'approved_by',
    ];

    protected $casts = [
        'photos' => 'array',
        'videos' => 'array',
        'documents' => 'array',
        'attachments' => 'array',
        'has_safety_concerns' => 'boolean',
        'has_urgent_issues' => 'boolean',
        'has_critical_issues' => 'boolean',
        'requires_customer_approval' => 'boolean',
        'customer_approved' => 'boolean',
        'has_upsell_opportunities' => 'boolean',
        'estimated_upsell_value' => 'decimal:2',
        'actual_upsell_value' => 'decimal:2',
        'inspection_score' => 'decimal:2',
        'inspection_started_at' => 'datetime',
        'inspection_completed_at' => 'datetime',
        'report_generated_at' => 'datetime',
        'report_sent_at' => 'datetime',
        'customer_approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function serviceAdvisor()
    {
        return $this->belongsTo(User::class, 'service_advisor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(InspectionItem::class, 'inspection_id');
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('inspection_status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('inspection_status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('inspection_status', 'completed');
    }

    public function scopeApproved($query)
    {
        return $query->where('inspection_status', 'approved');
    }

    public function scopeWithSafetyConcerns($query)
    {
        return $query->where('has_safety_concerns', true);
    }

    public function scopeWithUrgentIssues($query)
    {
        return $query->where('has_urgent_issues', true);
    }

    public function scopeWithCriticalIssues($query)
    {
        return $query->where('has_critical_issues', true);
    }

    public function scopeCustomerApproved($query)
    {
        return $query->where('customer_approved', true);
    }

    public function scopePreService($query)
    {
        return $query->where('inspection_type', 'pre_service');
    }

    public function scopePostService($query)
    {
        return $query->where('inspection_type', 'post_service');
    }

    public function scopeSafety($query)
    {
        return $query->where('inspection_type', 'safety');
    }

    public function scopeComprehensive($query)
    {
        return $query->where('inspection_type', 'comprehensive');
    }

    /**
     * Accessors
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->inspection_status) {
            'draft' => 'secondary',
            'in_progress' => 'primary',
            'completed' => 'info',
            'approved' => 'success',
            'rejected' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->inspection_type) {
            'pre_service' => 'Pre-Service Inspection',
            'post_service' => 'Post-Service Inspection',
            'safety' => 'Safety Inspection',
            'comprehensive' => 'Comprehensive Inspection',
            'custom' => 'Custom Inspection',
            default => ucfirst(str_replace('_', ' ', $this->inspection_type)),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->inspection_type) {
            'pre_service' => 'primary',
            'post_service' => 'success',
            'safety' => 'warning',
            'comprehensive' => 'info',
            'custom' => 'secondary',
            default => 'secondary',
        };
    }

    public function getFormattedScoreAttribute(): string
    {
        return $this->inspection_score ? number_format($this->inspection_score, 1) . '%' : 'N/A';
    }

    public function getPassRateAttribute(): float
    {
        if ($this->total_items_checked > 0) {
            return ($this->items_passed / $this->total_items_checked) * 100;
        }
        return 0;
    }

    public function getFormattedPassRateAttribute(): string
    {
        return number_format($this->pass_rate, 1) . '%';
    }

    public function getHasMediaAttribute(): bool
    {
        return !empty($this->photos) || !empty($this->videos) || !empty($this->documents);
    }

    public function getMediaCountAttribute(): int
    {
        $count = 0;
        if ($this->photos) $count += count($this->photos);
        if ($this->videos) $count += count($this->videos);
        if ($this->documents) $count += count($this->documents);
        return $count;
    }

    public function getEstimatedUpsellFormattedAttribute(): string
    {
        return $this->estimated_upsell_value ? '$' . number_format($this->estimated_upsell_value, 2) : 'N/A';
    }

    public function getActualUpsellFormattedAttribute(): string
    {
        return $this->actual_upsell_value ? '$' . number_format($this->actual_upsell_value, 2) : 'N/A';
    }

    public function getInspectionDurationAttribute(): ?int
    {
        if ($this->inspection_started_at && $this->inspection_completed_at) {
            return $this->inspection_started_at->diffInMinutes($this->inspection_completed_at);
        }
        return null;
    }

    public function getFormattedDurationAttribute(): string
    {
        if ($duration = $this->inspection_duration) {
            if ($duration < 60) {
                return $duration . ' minutes';
            }
            return floor($duration / 60) . 'h ' . ($duration % 60) . 'm';
        }
        return 'N/A';
    }

    /**
     * Methods
     */
    public function startInspection(): bool
    {
        if ($this->inspection_status === 'draft') {
            $this->update([
                'inspection_status' => 'in_progress',
                'inspection_started_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    public function completeInspection(): bool
    {
        if ($this->inspection_status === 'in_progress') {
            $this->update([
                'inspection_status' => 'completed',
                'inspection_completed_at' => now(),
            ]);
            return true;
        }
        return false;
    }

    public function approveInspection(User $approver): bool
    {
        if ($this->inspection_status === 'completed') {
            $this->update([
                'inspection_status' => 'approved',
                'approved_by' => $approver->id,
            ]);
            return true;
        }
        return false;
    }

    public function requestCustomerApproval(string $method = 'digital_signature'): bool
    {
        if ($this->inspection_status === 'completed') {
            $this->update([
                'requires_customer_approval' => true,
                'customer_approval_method' => $method,
            ]);
            return true;
        }
        return false;
    }

    public function approveByCustomer(string $method = 'digital_signature', string $notes = null): bool
    {
        $this->update([
            'customer_approved' => true,
            'customer_approved_at' => now(),
            'customer_approval_method' => $method,
            'customer_approval_notes' => $notes,
        ]);
        return true;
    }

    public function addPhoto(string $path, string $description = null): void
    {
        $photos = $this->photos ?? [];
        $photos[] = [
            'path' => $path,
            'description' => $description,
            'uploaded_at' => now()->toISOString(),
        ];
        $this->update(['photos' => $photos]);
    }

    public function addVideo(string $path, string $description = null): void
    {
        $videos = $this->videos ?? [];
        $videos[] = [
            'path' => $path,
            'description' => $description,
            'uploaded_at' => now()->toISOString(),
        ];
        $this->update(['videos' => $videos]);
    }

    public function addDocument(string $path, string $name, string $type = 'pdf'): void
    {
        $documents = $this->documents ?? [];
        $documents[] = [
            'path' => $path,
            'name' => $name,
            'type' => $type,
            'uploaded_at' => now()->toISOString(),
        ];
        $this->update(['documents' => $documents]);
    }

    public function calculateScore(): void
    {
        $total = $this->total_items_checked;
        $passed = $this->items_passed;
        
        if ($total > 0) {
            $score = ($passed / $total) * 100;
            $this->update(['inspection_score' => $score]);
        }
    }

    public function updateItemCounts(): void
    {
        $items = $this->items;
        
        $counts = [
            'total' => $items->count(),
            'passed' => $items->where('item_status', 'passed')->count(),
            'failed' => $items->where('item_status', 'failed')->count(),
            'attention_needed' => $items->where('item_status', 'attention_needed')->count(),
            'not_applicable' => $items->where('item_status', 'not_applicable')->count(),
        ];
        
        $this->update([
            'total_items_checked' => $counts['total'],
            'items_passed' => $counts['passed'],
            'items_failed' => $counts['failed'],
            'items_attention_needed' => $counts['attention_needed'],
            'items_not_applicable' => $counts['not_applicable'],
        ]);
        
        // Update safety/urgent flags
        $hasSafety = $items->where('is_safety_issue', true)->exists();
        $hasUrgent = $items->where('is_urgent_issue', true)->exists();
        $hasCritical = $items->where('is_critical_issue', true)->exists();
        
        $this->update([
            'has_safety_concerns' => $hasSafety,
            'has_urgent_issues' => $hasUrgent,
            'has_critical_issues' => $hasCritical,
        ]);
        
        // Calculate upsell opportunities
        $estimatedUpsell = $items->where('requires_attention', true)
            ->where('estimated_cost', '>', 0)
            ->sum('estimated_cost');
        
        $this->update([
            'has_upsell_opportunities' => $estimatedUpsell > 0,
            'estimated_upsell_value' => $estimatedUpsell,
        ]);
        
        // Recalculate score
        $this->calculateScore();
    }

    public function generateReport(): array
    {
        return [
            'inspection_id' => $this->id,
            'inspection_number' => 'INSP-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'customer_name' => $this->customer->full_name,
            'vehicle_info' => $this->vehicle->vehicle_info,
            'inspection_type' => $this->type_label,
            'inspection_date' => $this->created_at->format('F d, Y'),
            'technician_name' => $this->technician ? $this->technician->name : 'Not assigned',
            'inspection_score' => $this->formatted_score,
            'pass_rate' => $this->formatted_pass_rate,
            'total_items' => $this->total_items_checked,
            'items_passed' => $this->items_passed,
            'items_failed' => $this->items_failed,
            'items_attention_needed' => $this->items_attention_needed,
            'safety_concerns' => $this->has_safety_concerns,
            'urgent_issues' => $this->has_urgent_issues,
            'critical_issues' => $this->has_critical_issues,
            'customer_approved' => $this->customer_approved,
            'estimated_upsell' => $this->estimated_upsell_formatted,
            'inspection_duration' => $this->formatted_duration,
            'media_count' => $this->media_count,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public static function createFromTemplate(int $workOrderId, int $customerId, int $vehicleId, string $templateType = 'pre_service'): self
    {
        $inspection = self::create([
            'work_order_id' => $workOrderId,
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'inspection_type' => $templateType,
            'inspection_status' => 'draft',
            'inspection_name' => ucfirst($templateType) . ' Inspection',
            'requires_customer_approval' => true,
        ]);

        // In production, you would load items from a template database
        // For now, we'll create a basic inspection structure
        $inspection->createDefaultItems();

        return $inspection;
    }

    private function createDefaultItems(): void
    {
        $defaultItems = [
            [
                'item_name' => 'Engine Oil Level',
                'item_type' => 'check',
                'item_description' => 'Check engine oil level and condition',
                'item_unit' => null,
                'spec_source' => 'Manufacturer',
            ],
            [
                'item_name' => 'Brake Fluid Level',
                'item_type' => 'check',
                'item_description' => 'Check brake fluid level and condition',
                'item_unit' => null,
                'spec_source' => 'Manufacturer',
            ],
            [
                'item_name' => 'Coolant Level',
                'item_type' => 'check',
                'item_description' => 'Check coolant level and condition',
                'item_unit' => null,
                'spec_source' => 'Manufacturer',
            ],
            [
                'item_name' => 'Brake Pad Thickness',
                'item_type' => 'measurement',
                'item_description' => 'Measure front and rear brake pad thickness',
                'item_unit' => 'mm',
                'min_value' => 3.0,
                'max_value' => null,
                'spec_source' => 'Safety Standard',
            ],
            [
                'item_name' => 'Tire Tread Depth',
                'item_type' => 'measurement',
                'item_description' => 'Measure tire tread depth on all four tires',
                'item_unit' => 'mm',
                'min_value' => 1.6,
                'max_value' => null,
                'spec_source' => 'Safety Standard',
            ],
            [
                'item_name' => 'Battery Voltage',
                'item_type' => 'measurement',
                'item_description' => 'Test battery voltage and condition',
                'item_unit' => 'volts',
                'min_value' => 12.4,
                'max_value' => 12.8,
                'spec_source' => 'Manufacturer',
            ],
            [
                'item_name' => 'Headlights Operation',
                'item_type' => 'check',
                'item_description' => 'Check all headlights, high beams, and indicators',
                'item_unit' => null,
                'spec_source' => 'Safety Standard',
            ],
            [
                'item_name' => 'Brake Lights Operation',
                'item_type' => 'check',
                'item_description' => 'Check brake lights and third brake light',
                'item_unit' => null,
                'spec_source' => 'Safety Standard',
            ],
            [
                'item_name' => 'Windshield Wipers',
                'item_type' => 'check',
                'item_description' => 'Check wiper blades and washer fluid',
                'item_unit' => null,
                'spec_source' => 'Safety Standard',
            ],
            [
                'item_name' => 'Air Filter Condition',
                'item_type' => 'check',
                'item_description' => 'Inspect engine air filter',
                'item_unit' => null,
                'spec_source' => 'Maintenance',
            ],
        ];

        foreach ($defaultItems as $index => $itemData) {
            InspectionItem::create([
                'inspection_id' => $this->id,
                'item_name' => $itemData['item_name'],
                'item_type' => $itemData['item_type'],
                'item_description' => $itemData['item_description'],
                'item_unit' => $itemData['item_unit'] ?? null,
                'min_value' => $itemData['min_value'] ?? null,
                'max_value' => $itemData['max_value'] ?? null,
                'spec_source' => $itemData['spec_source'],
                'item_status' => 'pending',
                'sequence' => $index + 1,
            ]);
        }

        $this->updateItemCounts();
    }
}