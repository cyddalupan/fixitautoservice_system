<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingModule extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'training_modules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'module_code',
        'title',
        'description',
        'category',
        'difficulty_level',
        'estimated_hours',
        'content_type',
        'content_url',
        'is_active',
        'required_certification',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estimated_hours' => 'decimal:1',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the creator of this training module.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all training records for this module.
     */
    public function trainingRecords()
    {
        return $this->hasMany(TrainingRecord::class, 'training_module_id');
    }

    /**
     * Scope a query to only include active modules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by difficulty level.
     */
    public function scopeDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }

    /**
     * Get all available categories.
     */
    public static function getCategories(): array
    {
        return [
            'safety' => 'Safety',
            'technical' => 'Technical',
            'customer_service' => 'Customer Service',
            'administrative' => 'Administrative',
            'compliance' => 'Compliance',
        ];
    }

    /**
     * Get all difficulty levels.
     */
    public static function getDifficultyLevels(): array
    {
        return [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
            'expert' => 'Expert',
        ];
    }

    /**
     * Get all content types.
     */
    public static function getContentTypes(): array
    {
        return [
            'video' => 'Video',
            'document' => 'Document',
            'quiz' => 'Quiz',
            'practical' => 'Practical',
            'online_course' => 'Online Course',
        ];
    }

    /**
     * Get the difficulty badge color.
     */
    public function getDifficultyBadgeColorAttribute(): string
    {
        return match($this->difficulty_level) {
            'beginner' => 'success',
            'intermediate' => 'primary',
            'advanced' => 'warning',
            'expert' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the formatted difficulty level.
     */
    public function getFormattedDifficultyAttribute(): string
    {
        return ucfirst($this->difficulty_level);
    }

    /**
     * Get the formatted category.
     */
    public function getFormattedCategoryAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->category));
    }

    /**
     * Get the completion rate for this module.
     */
    public function getCompletionRateAttribute(): float
    {
        $total = $this->trainingRecords()->count();
        $completed = $this->trainingRecords()->completed()->count();

        if ($total === 0) {
            return 0.0;
        }

        return round(($completed / $total) * 100, 1);
    }

    /**
     * Get the average score for this module.
     */
    public function getAverageScoreAttribute(): ?float
    {
        $completed = $this->trainingRecords()->completed()->whereNotNull('score')->get();
        
        if ($completed->isEmpty()) {
            return null;
        }

        return round($completed->avg('score'), 1);
    }

    /**
     * Check if this module is required for a specific certification.
     */
    public function isRequiredForCertification(string $certification): bool
    {
        return $this->required_certification === $certification;
    }
}