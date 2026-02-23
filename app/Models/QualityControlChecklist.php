<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityControlChecklist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'service_type',
        'checklist_items',
        'passing_score',
        'is_active',
        'version',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'checklist_items' => 'array',
        'is_active' => 'boolean',
        'passing_score' => 'integer',
        'version' => 'integer'
    ];

    protected $dates = [
        'deleted_at'
    ];

    /**
     * Get the user who created the checklist
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the checklist
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get audits using this checklist
     */
    public function audits()
    {
        return $this->hasMany(QualityAudit::class, 'checklist_id');
    }

    /**
     * Scope for active checklists
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for checklists by service type
     */
    public function scopeByServiceType($query, $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    /**
     * Get checklist items as array with scores
     */
    public function getChecklistItemsWithScores()
    {
        $items = $this->checklist_items ?? [];
        $maxScore = 0;
        
        foreach ($items as &$item) {
            $item['score'] = $item['score'] ?? 0;
            $item['max_score'] = $item['max_score'] ?? 10;
            $maxScore += $item['max_score'];
        }
        
        return [
            'items' => $items,
            'max_score' => $maxScore
        ];
    }

    /**
     * Calculate score for given answers
     */
    public function calculateScore($answers)
    {
        $items = $this->getChecklistItemsWithScores();
        $totalScore = 0;
        
        foreach ($items['items'] as $index => $item) {
            if (isset($answers[$index])) {
                $answer = $answers[$index];
                if ($answer['passed'] ?? false) {
                    $totalScore += $item['score'];
                }
            }
        }
        
        $percentage = $items['max_score'] > 0 ? ($totalScore / $items['max_score']) * 100 : 0;
        
        return [
            'total_score' => $totalScore,
            'max_score' => $items['max_score'],
            'percentage' => round($percentage, 2),
            'passed' => $percentage >= $this->passing_score
        ];
    }

    /**
     * Create a new version of the checklist
     */
    public function createNewVersion($data)
    {
        $newChecklist = $this->replicate();
        $newChecklist->version = $this->version + 1;
        $newChecklist->fill($data);
        $newChecklist->save();
        
        // Deactivate old version
        $this->update(['is_active' => false]);
        
        return $newChecklist;
    }
}