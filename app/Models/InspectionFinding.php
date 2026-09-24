<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionFinding extends Model
{
    protected $fillable = [
        'inspection_id',
        'group_id',
        'category',
        'issue_title',
        'part_name',
        'remarks',
        'quantity',
        'unit_price',
        'detailed_notes',
        'severity',
        'recommended_action',
        'estimated_urgency',
        'estimated_cost',
        'tech_id',
        'photo_path',
        'sort_order',
        'is_linked_to_estimate',
        'is_declined',
        'is_quotation_added',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'is_linked_to_estimate' => 'boolean',
        'is_declined' => 'boolean',
        'is_quotation_added' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The group this finding is dragged into (shared labor cost)
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(InspectionFindingGroup::class, 'group_id');
    }

    /**
     * Line total for this finding (quantity * unit_price).
     */
    public function getLineTotalAttribute(): float
    {
        return (float) $this->quantity * (float) ($this->unit_price ?? 0);
    }

    /**
     * The inspection this finding belongs to
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'inspection_id');
    }

    /**
     * The technician who logged this finding
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tech_id');
    }

    /**
     * Scope: filter by category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: filter by severity
     */
    public function scopeOfSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope: filter by urgency
     */
    public function scopeOfUrgency($query, string $urgency)
    {
        return $query->where('estimated_urgency', $urgency);
    }

    /**
     * Get the severity badge class
     */
    public function getSeverityBadgeAttribute(): string
    {
        return match($this->severity) {
            'low' => 'bg-info',
            'medium' => 'bg-warning text-dark',
            'high' => 'bg-danger',
            'critical' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the urgency badge class
     */
    public function getUrgencyBadgeAttribute(): string
    {
        return match($this->estimated_urgency) {
            'routine' => 'bg-secondary',
            'soon' => 'bg-info',
            'urgent' => 'bg-warning text-dark',
            'immediate' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
