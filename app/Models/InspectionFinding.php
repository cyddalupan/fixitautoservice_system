<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionFinding extends Model
{
    protected $fillable = [
        'inspection_id',
        'category',
        'issue_title',
        'detailed_notes',
        'severity',
        'recommended_action',
        'estimated_urgency',
        'estimated_cost',
        'tech_id',
        'photo_path',
        'sort_order',
        'is_linked_to_estimate',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'is_linked_to_estimate' => 'boolean',
        'sort_order' => 'integer',
    ];

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
