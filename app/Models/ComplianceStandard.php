<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplianceStandard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'requirements',
        'effective_date',
        'expiration_date',
        'is_mandatory',
        'revision_number',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiration_date' => 'date',
        'is_mandatory' => 'boolean',
        'revision_number' => 'integer'
    ];

    protected $dates = [
        'deleted_at'
    ];

    /**
     * Get the user who created the standard
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the standard
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get corrective actions related to this standard
     */
    public function correctiveActions()
    {
        return $this->hasMany(CorrectiveAction::class, 'standard_id');
    }

    /**
     * Get compliance documents for this standard
     */
    public function complianceDocuments()
    {
        return $this->hasMany(ComplianceDocument::class, 'standard_id');
    }

    /**
     * Scope for active standards (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expiration_date')
              ->orWhere('expiration_date', '>=', now());
        });
    }

    /**
     * Scope for mandatory standards
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Scope for standards by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if standard is expired
     */
    public function isExpired()
    {
        return $this->expiration_date && $this->expiration_date < now();
    }

    /**
     * Check if standard is active (not expired and effective)
     */
    public function isActive()
    {
        return $this->effective_date <= now() && !$this->isExpired();
    }

    /**
     * Get days until expiration
     */
    public function daysUntilExpiration()
    {
        if (!$this->expiration_date) {
            return null;
        }
        
        return now()->diffInDays($this->expiration_date, false);
    }

    /**
     * Create a new revision of the standard
     */
    public function createNewRevision($data)
    {
        $newStandard = $this->replicate();
        $newStandard->revision_number = $this->revision_number + 1;
        $newStandard->fill($data);
        $newStandard->save();
        
        return $newStandard;
    }

    /**
     * Get compliance status for a specific entity (work order, vehicle, etc.)
     */
    public function getComplianceStatus($entityId, $entityType)
    {
        // This would check if the entity complies with this standard
        // Implementation would depend on specific compliance checking logic
        return [
            'standard_id' => $this->id,
            'entity_id' => $entityId,
            'entity_type' => $entityType,
            'is_compliant' => false, // Default
            'last_check' => null,
            'notes' => null
        ];
    }
}