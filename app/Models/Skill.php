<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'technician_skills';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'technician_id',
        'skill_name',
        'skill_category',
        'proficiency_level',
        'years_experience',
        'last_used_date',
        'is_primary_skill',
        'certification_required',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_used_date' => 'date',
        'is_primary_skill' => 'boolean',
        'certification_required' => 'boolean',
        'years_experience' => 'decimal:1',
    ];

    /**
     * Proficiency level constants
     */
    const PROFICIENCY_BEGINNER = 'beginner';
    const PROFICIENCY_INTERMEDIATE = 'intermediate';
    const PROFICIENCY_ADVANCED = 'advanced';
    const PROFICIENCY_EXPERT = 'expert';

    /**
     * Skill category constants
     */
    const CATEGORY_MECHANICAL = 'mechanical';
    const CATEGORY_ELECTRICAL = 'electrical';
    const CATEGORY_DIAGNOSTIC = 'diagnostic';
    const CATEGORY_BODYWORK = 'bodywork';
    const CATEGORY_PAINT = 'paint';
    const CATEGORY_UNDERCARRIAGE = 'undercarriage';
    const CATEGORY_ENGINE = 'engine';
    const CATEGORY_TRANSMISSION = 'transmission';
    const CATEGORY_BRAKES = 'brakes';
    const CATEGORY_SUSPENSION = 'suspension';
    const CATEGORY_AC = 'ac';
    const CATEGORY_HYBRID = 'hybrid';
    const CATEGORY_EV = 'ev';

    /**
     * Get available proficiency levels
     *
     * @return array
     */
    public static function getProficiencyLevels(): array
    {
        return [
            self::PROFICIENCY_BEGINNER => 'Beginner',
            self::PROFICIENCY_INTERMEDIATE => 'Intermediate',
            self::PROFICIENCY_ADVANCED => 'Advanced',
            self::PROFICIENCY_EXPERT => 'Expert',
        ];
    }

    /**
     * Get available skill categories
     *
     * @return array
     */
    public static function getSkillCategories(): array
    {
        return [
            self::CATEGORY_MECHANICAL => 'Mechanical',
            self::CATEGORY_ELECTRICAL => 'Electrical',
            self::CATEGORY_DIAGNOSTIC => 'Diagnostic',
            self::CATEGORY_BODYWORK => 'Bodywork',
            self::CATEGORY_PAINT => 'Paint',
            self::CATEGORY_UNDERCARRIAGE => 'Undercarriage',
            self::CATEGORY_ENGINE => 'Engine',
            self::CATEGORY_TRANSMISSION => 'Transmission',
            self::CATEGORY_BRAKES => 'Brakes',
            self::CATEGORY_SUSPENSION => 'Suspension',
            self::CATEGORY_AC => 'A/C & Heating',
            self::CATEGORY_HYBRID => 'Hybrid Systems',
            self::CATEGORY_EV => 'Electric Vehicles',
        ];
    }

    /**
     * Relationship: Technician
     *
     * @return BelongsTo
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Scope: For specific technician
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $technicianId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    /**
     * Scope: For specific category
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCategory($query, $category)
    {
        return $query->where('skill_category', $category);
    }

    /**
     * Scope: For specific proficiency level
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $proficiency
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProficiency($query, $proficiency)
    {
        return $query->where('proficiency_level', $proficiency);
    }

    /**
     * Scope: Primary skills only
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrimarySkills($query)
    {
        return $query->where('is_primary_skill', true);
    }

    /**
     * Scope: Skills requiring certification
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequiringCertification($query)
    {
        return $query->where('certification_required', true);
    }

    /**
     * Get proficiency level color
     *
     * @return string
     */
    public function getProficiencyColor(): string
    {
        return match($this->proficiency_level) {
            self::PROFICIENCY_BEGINNER => 'secondary',
            self::PROFICIENCY_INTERMEDIATE => 'info',
            self::PROFICIENCY_ADVANCED => 'warning',
            self::PROFICIENCY_EXPERT => 'success',
            default => 'light',
        };
    }

    /**
     * Get proficiency level badge
     *
     * @return string
     */
    public function getProficiencyBadge(): string
    {
        $color = $this->getProficiencyColor();
        $level = self::getProficiencyLevels()[$this->proficiency_level] ?? $this->proficiency_level;
        
        return "<span class='badge bg-{$color}'>{$level}</span>";
    }

    /**
     * Check if skill is expired (not used for more than 2 years)
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->last_used_date) {
            return false; // Never used, not expired
        }

        $twoYearsAgo = now()->subYears(2);
        return $this->last_used_date < $twoYearsAgo;
    }

    /**
     * Get skill experience level
     *
     * @return string
     */
    public function getExperienceLevel(): string
    {
        $years = $this->years_experience;

        if ($years >= 10) {
            return 'Veteran';
        } elseif ($years >= 5) {
            return 'Experienced';
        } elseif ($years >= 2) {
            return 'Skilled';
        } elseif ($years >= 1) {
            return 'Developing';
        } else {
            return 'Novice';
        }
    }

    /**
     * Calculate skill score based on proficiency and experience
     *
     * @return float
     */
    public function calculateSkillScore(): float
    {
        $proficiencyScore = match($this->proficiency_level) {
            self::PROFICIENCY_BEGINNER => 25,
            self::PROFICIENCY_INTERMEDIATE => 50,
            self::PROFICIENCY_ADVANCED => 75,
            self::PROFICIENCY_EXPERT => 100,
            default => 0,
        };

        $experienceScore = min($this->years_experience * 10, 100); // Max 10 years = 100 points

        // Weighted average: 60% proficiency, 40% experience
        return ($proficiencyScore * 0.6) + ($experienceScore * 0.4);
    }

    /**
     * Get related certifications for this skill
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedCertifications()
    {
        if (!$this->certification_required) {
            return collect();
        }

        return Certification::where('technician_id', $this->technician_id)
            ->where('certification_name', 'like', "%{$this->skill_name}%")
            ->orWhere('description', 'like', "%{$this->skill_name}%")
            ->get();
    }

    /**
     * Update last used date
     *
     * @return void
     */
    public function markAsUsed(): void
    {
        $this->last_used_date = now();
        $this->save();
    }

    /**
     * Get skill summary for reports
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'skill_name' => $this->skill_name,
            'category' => $this->skill_category,
            'proficiency' => $this->proficiency_level,
            'proficiency_display' => self::getProficiencyLevels()[$this->proficiency_level] ?? $this->proficiency_level,
            'years_experience' => $this->years_experience,
            'experience_level' => $this->getExperienceLevel(),
            'is_primary' => $this->is_primary_skill,
            'requires_certification' => $this->certification_required,
            'last_used' => $this->last_used_date ? $this->last_used_date->format('Y-m-d') : 'Never',
            'is_expired' => $this->isExpired(),
            'skill_score' => $this->calculateSkillScore(),
            'related_certifications' => $this->getRelatedCertifications()->count(),
        ];
    }
}