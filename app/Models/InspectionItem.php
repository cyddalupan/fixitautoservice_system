<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InspectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'category_id',
        'item_name',
        'item_description',
        'item_type',
        'item_unit',
        'min_value',
        'max_value',
        'spec_value',
        'spec_source',
        'item_status',
        'measured_value',
        'technician_notes',
        'customer_notes',
        'requires_attention',
        'is_safety_issue',
        'is_urgent_issue',
        'is_critical_issue',
        'recommendation',
        'estimated_cost',
        'estimated_time_hours',
        'priority',
        'customer_approved',
        'photos',
        'videos',
        'attachments',
        'sequence',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'spec_value' => 'decimal:2',
        'measured_value' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'estimated_time_hours' => 'decimal:2',
        'requires_attention' => 'boolean',
        'is_safety_issue' => 'boolean',
        'is_urgent_issue' => 'boolean',
        'is_critical_issue' => 'boolean',
        'customer_approved' => 'boolean',
        'photos' => 'array',
        'videos' => 'array',
        'attachments' => 'array',
    ];

    /**
     * Relationships
     */
    public function inspection()
    {
        return $this->belongsTo(VehicleInspection::class, 'inspection_id');
    }

    public function category()
    {
        return $this->belongsTo(InspectionCategory::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('item_status', 'pending');
    }

    public function scopePassed($query)
    {
        return $query->where('item_status', 'passed');
    }

    public function scopeFailed($query)
    {
        return $query->where('item_status', 'failed');
    }

    public function scopeAttentionNeeded($query)
    {
        return $query->where('item_status', 'attention_needed');
    }

    public function scopeNotApplicable($query)
    {
        return $query->where('item_status', 'not_applicable');
    }

    public function scopeRequiresAttention($query)
    {
        return $query->where('requires_attention', true);
    }

    public function scopeSafetyIssues($query)
    {
        return $query->where('is_safety_issue', true);
    }

    public function scopeUrgentIssues($query)
    {
        return $query->where('is_urgent_issue', true);
    }

    public function scopeCriticalIssues($query)
    {
        return $query->where('is_critical_issue', true);
    }

    public function scopeCustomerApproved($query)
    {
        return $query->where('customer_approved', true);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Accessors
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->item_status) {
            'pending' => 'secondary',
            'passed' => 'success',
            'failed' => 'danger',
            'attention_needed' => 'warning',
            'not_applicable' => 'info',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->item_status) {
            'pending' => 'Pending',
            'passed' => 'Passed',
            'failed' => 'Failed',
            'attention_needed' => 'Attention Needed',
            'not_applicable' => 'Not Applicable',
            default => ucfirst(str_replace('_', ' ', $this->item_status)),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->item_type) {
            'check' => 'Visual Check',
            'measurement' => 'Measurement',
            'test' => 'Functional Test',
            'visual' => 'Visual Inspection',
            default => ucfirst($this->item_type),
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'danger',
            default => 'secondary',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return $this->priority ? ucfirst($this->priority) : 'Not Set';
    }

    public function getFormattedMeasuredValueAttribute(): string
    {
        if ($this->measured_value === null) {
            return 'Not measured';
        }
        
        $value = number_format($this->measured_value, 2);
        return $this->item_unit ? $value . ' ' . $this->item_unit : $value;
    }

    public function getFormattedSpecRangeAttribute(): string
    {
        if ($this->min_value !== null && $this->max_value !== null) {
            return number_format($this->min_value, 2) . ' - ' . number_format($this->max_value, 2) . ' ' . $this->item_unit;
        } elseif ($this->min_value !== null) {
            return '≥ ' . number_format($this->min_value, 2) . ' ' . $this->item_unit;
        } elseif ($this->max_value !== null) {
            return '≤ ' . number_format($this->max_value, 2) . ' ' . $this->item_unit;
        } elseif ($this->spec_value !== null) {
            return number_format($this->spec_value, 2) . ' ' . $this->item_unit;
        }
        
        return 'N/A';
    }

    public function getFormattedEstimatedCostAttribute(): string
    {
        return $this->estimated_cost ? '$' . number_format($this->estimated_cost, 2) : 'N/A';
    }

    public function getFormattedEstimatedTimeAttribute(): string
    {
        if (!$this->estimated_time_hours) {
            return 'N/A';
        }
        
        if ($this->estimated_time_hours < 1) {
            return number_format($this->estimated_time_hours * 60, 0) . ' minutes';
        }
        
        return number_format($this->estimated_time_hours, 1) . ' hours';
    }

    public function getIsWithinSpecAttribute(): bool
    {
        if ($this->measured_value === null) {
            return false;
        }
        
        if ($this->min_value !== null && $this->measured_value < $this->min_value) {
            return false;
        }
        
        if ($this->max_value !== null && $this->measured_value > $this->max_value) {
            return false;
        }
        
        if ($this->spec_value !== null && abs($this->measured_value - $this->spec_value) > 0.01) {
            return false;
        }
        
        return true;
    }

    public function getDeviationAttribute(): ?float
    {
        if ($this->measured_value === null) {
            return null;
        }
        
        if ($this->spec_value !== null) {
            return $this->measured_value - $this->spec_value;
        }
        
        if ($this->min_value !== null && $this->measured_value < $this->min_value) {
            return $this->measured_value - $this->min_value;
        }
        
        if ($this->max_value !== null && $this->measured_value > $this->max_value) {
            return $this->measured_value - $this->max_value;
        }
        
        return 0;
    }

    public function getFormattedDeviationAttribute(): string
    {
        if ($deviation = $this->deviation) {
            $sign = $deviation > 0 ? '+' : '';
            return $sign . number_format($deviation, 2) . ' ' . $this->item_unit;
        }
        
        return 'Within spec';
    }

    public function getHasMediaAttribute(): bool
    {
        return !empty($this->photos) || !empty($this->videos) || !empty($this->attachments);
    }

    public function getMediaCountAttribute(): int
    {
        $count = 0;
        if ($this->photos) $count += count($this->photos);
        if ($this->videos) $count += count($this->videos);
        if ($this->attachments) $count += count($this->attachments);
        return $count;
    }

    /**
     * Methods
     */
    public function markAsPassed(string $notes = null): bool
    {
        $this->update([
            'item_status' => 'passed',
            'technician_notes' => $notes,
            'requires_attention' => false,
        ]);
        
        $this->inspection->updateItemCounts();
        return true;
    }

    public function markAsFailed(string $notes = null, bool $requiresAttention = true): bool
    {
        $this->update([
            'item_status' => 'failed',
            'technician_notes' => $notes,
            'requires_attention' => $requiresAttention,
        ]);
        
        $this->inspection->updateItemCounts();
        return true;
    }

    public function markAsAttentionNeeded(string $notes = null, string $recommendation = null): bool
    {
        $this->update([
            'item_status' => 'attention_needed',
            'technician_notes' => $notes,
            'recommendation' => $recommendation,
            'requires_attention' => true,
        ]);
        
        $this->inspection->updateItemCounts();
        return true;
    }

    public function markAsNotApplicable(string $notes = null): bool
    {
        $this->update([
            'item_status' => 'not_applicable',
            'technician_notes' => $notes,
            'requires_attention' => false,
        ]);
        
        $this->inspection->updateItemCounts();
        return true;
    }

    public function setMeasurement(float $value, string $notes = null): bool
    {
        $this->update([
            'measured_value' => $value,
            'technician_notes' => $notes,
        ]);
        
        // Auto-determine status based on spec
        if ($this->is_within_spec) {
            $this->markAsPassed($notes);
        } else {
            $this->markAsFailed($notes . ' (Out of spec: ' . $this->formatted_deviation . ')');
        }
        
        return true;
    }

    public function addRecommendation(string $recommendation, float $estimatedCost = null, float $estimatedTime = null, string $priority = 'medium'): bool
    {
        $this->update([
            'recommendation' => $recommendation,
            'estimated_cost' => $estimatedCost,
            'estimated_time_hours' => $estimatedTime,
            'priority' => $priority,
            'requires_attention' => true,
        ]);
        
        return true;
    }

    public function markAsSafetyIssue(bool $isSafety = true): bool
    {
        $this->update([
            'is_safety_issue' => $isSafety,
            'requires_attention' => $isSafety,
        ]);
        
        if ($isSafety && !$this->is_urgent_issue) {
            $this->update(['priority' => 'high']);
        }
        
        $this->inspection->updateItemCounts();
        return true;
    }

    public function markAsUrgentIssue(bool $isUrgent = true): bool
    {
        $this->update([
            'is_urgent_issue' => $isUrgent,
            'requires_attention' => $isUrgent,
        ]);
        
        if ($isUrgent) {
            $this->update(['priority' => 'critical']);
        }
        
        $this->inspection->updateItemCounts();
        return true;
    }

    public function markAsCriticalIssue(bool $isCritical = true): bool
    {
        $this->update([
            'is_critical_issue' => $isCritical,
            'requires_attention' => $isCritical,
        ]);
        
        if ($isCritical) {
            $this->update([
                'is_safety_issue' => true,
                'is_urgent_issue' => true,
                'priority' => 'critical',
            ]);
        }
        
        $this->inspection->updateItemCounts();
        return true;
    }

    public function approveByCustomer(): bool
    {
        $this->update(['customer_approved' => true]);
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

    public function getItemSummary(): array
    {
        return [
            'id' => $this->id,
            'item_name' => $this->item_name,
            'item_description' => $this->item_description,
            'item_type' => $this->item_type,
            'item_status' => $this->item_status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'measured_value' => $this->formatted_measured_value,
            'spec_range' => $this->formatted_spec_range,
            'is_within_spec' => $this->is_within_spec,
            'deviation' => $this->formatted_deviation,
            'requires_attention' => $this->requires_attention,
            'is_safety_issue' => $this->is_safety_issue,
            'is_urgent_issue' => $this->is_urgent_issue,
            'is_critical_issue' => $this->is_critical_issue,
            'recommendation' => $this->recommendation,
            'estimated_cost' => $this->formatted_estimated_cost,
            'estimated_time' => $this->formatted_estimated_time,
            'priority' => $this->priority_label,
            'priority_color' => $this->priority_color,
            'customer_approved' => $this->customer_approved,
            'technician_notes' => $this->technician_notes,
            'has_media' => $this->has_media,
            'media_count' => $this->media_count,
            'sequence' => $this->sequence,
        ];
    }
}