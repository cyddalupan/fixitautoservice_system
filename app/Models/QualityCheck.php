<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityCheck extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'category',
        'checklist_items',
        'is_active',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'checklist_items' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Category constants
     */
    public const CATEGORY_SAFETY = 'safety';
    public const CATEGORY_WORKMANSHIP = 'workmanship';
    public const CATEGORY_CLEANLINESS = 'cleanliness';
    public const CATEGORY_DOCUMENTATION = 'documentation';
    public const CATEGORY_PARTS = 'parts';
    public const CATEGORY_CUSTOMER_SERVICE = 'customer_service';

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_SAFETY => 'Safety',
            self::CATEGORY_WORKMANSHIP => 'Workmanship',
            self::CATEGORY_CLEANLINESS => 'Cleanliness',
            self::CATEGORY_DOCUMENTATION => 'Documentation',
            self::CATEGORY_PARTS => 'Parts',
            self::CATEGORY_CUSTOMER_SERVICE => 'Customer Service',
        ];
    }

    /**
     * Get category label
     */
    public function getCategoryLabel(): string
    {
        return self::getCategories()[$this->category] ?? $this->category;
    }

    /**
     * Get checklist items count
     */
    public function getChecklistItemsCount(): int
    {
        return count($this->checklist_items ?? []);
    }

    /**
     * Check if quality check has checklist items
     */
    public function hasChecklistItems(): bool
    {
        return !empty($this->checklist_items) && is_array($this->checklist_items);
    }

    /**
     * Get formatted checklist items
     */
    public function getFormattedChecklistItems(): array
    {
        if (!$this->hasChecklistItems()) {
            return [];
        }

        return array_map(function ($item) {
            return [
                'item' => $item['item'] ?? '',
                'description' => $item['description'] ?? '',
                'is_required' => $item['is_required'] ?? true,
                'pass_criteria' => $item['pass_criteria'] ?? '',
            ];
        }, $this->checklist_items);
    }

    /**
     * Scope: Active quality checks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Relationship: Work order quality checks
     */
    public function workOrderQualityChecks(): HasMany
    {
        return $this->hasMany(WorkOrderQualityCheck::class);
    }

    /**
     * Get completion statistics
     */
    public function getCompletionStats(): array
    {
        $total = $this->workOrderQualityChecks()->count();
        $completed = $this->workOrderQualityChecks()->whereIn('status', ['passed', 'approved'])->count();
        $failed = $this->workOrderQualityChecks()->whereIn('status', ['failed', 'rejected'])->count();
        $pending = $this->workOrderQualityChecks()->whereIn('status', ['pending', 'in_progress'])->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'pending' => $pending,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'failure_rate' => $total > 0 ? round(($failed / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Check if quality check can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->workOrderQualityChecks()->count() === 0;
    }

    /**
     * Duplicate quality check
     */
    public function duplicate(string $newName = null): QualityCheck
    {
        $duplicate = $this->replicate();
        $duplicate->name = $newName ?? $this->name . ' (Copy)';
        $duplicate->push();

        return $duplicate;
    }
}