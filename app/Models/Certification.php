<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Certification extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'technician_certifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'technician_id',
        'certification_name',
        'certification_code',
        'issuing_organization',
        'certification_level',
        'description',
        'issue_date',
        'expiry_date',
        'renewal_date',
        'certification_number',
        'certificate_file_path',
        'is_verified',
        'verified_date',
        'verified_by',
        'verification_notes',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'renewal_date' => 'date',
        'verified_date' => 'date',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Certification level constants
     */
    const LEVEL_BASIC = 'basic';
    const LEVEL_INTERMEDIATE = 'intermediate';
    const LEVEL_ADVANCED = 'advanced';
    const LEVEL_MASTER = 'master';
    const LEVEL_SPECIALIST = 'specialist';

    /**
     * Get available certification levels
     *
     * @return array
     */
    public static function getCertificationLevels(): array
    {
        return [
            self::LEVEL_BASIC => 'Basic',
            self::LEVEL_INTERMEDIATE => 'Intermediate',
            self::LEVEL_ADVANCED => 'Advanced',
            self::LEVEL_MASTER => 'Master',
            self::LEVEL_SPECIALIST => 'Specialist',
        ];
    }

    /**
     * Get common issuing organizations
     *
     * @return array
     */
    public static function getIssuingOrganizations(): array
    {
        return [
            'ASE' => 'Automotive Service Excellence',
            'NAPA' => 'NAPA AutoCare',
            'AAA' => 'AAA Approved Auto Repair',
            'I-CAR' => 'Inter-Industry Conference on Auto Collision Repair',
            'MAC' => 'Mobile Air Conditioning Society',
            'NATEF' => 'National Automotive Technicians Education Foundation',
            'SEMA' => 'Specialty Equipment Market Association',
            'ATRA' => 'Automatic Transmission Rebuilders Association',
            'BRAKE' => 'Brake Manufacturers Association',
            'EVT' => 'Electric Vehicle Technician',
            'OEM' => 'Original Equipment Manufacturer',
            'STATE' => 'State Certification',
            'OTHER' => 'Other Organization',
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
     * Relationship: Verifier
     *
     * @return BelongsTo
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
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
     * Scope: Active certifications only
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Verified certifications only
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Expired certifications
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope: Expiring soon (within 30 days)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpiringSoon($query)
    {
        $thirtyDaysFromNow = now()->addDays(30);
        return $query->whereBetween('expiry_date', [now(), $thirtyDaysFromNow]);
    }

    /**
     * Scope: By issuing organization
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $organization
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByOrganization($query, $organization)
    {
        return $query->where('issuing_organization', $organization);
    }

    /**
     * Scope: By certification level
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $level
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('certification_level', $level);
    }

    /**
     * Check if certification is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false; // No expiry date, never expires
        }

        return $this->expiry_date < now();
    }

    /**
     * Check if certification is expiring soon (within 30 days)
     *
     * @return bool
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false; // No expiry date
        }

        $thirtyDaysFromNow = now()->addDays(30);
        return $this->expiry_date >= now() && $this->expiry_date <= $thirtyDaysFromNow;
    }

    /**
     * Check if certification needs renewal
     *
     * @return bool
     */
    public function needsRenewal(): bool
    {
        if (!$this->renewal_date) {
            return false; // No renewal date
        }

        return $this->renewal_date <= now();
    }

    /**
     * Get days until expiry
     *
     * @return int|null
     */
    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null; // No expiry date
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Get certification status
     *
     * @return string
     */
    public function getStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if (!$this->is_verified) {
            return 'pending_verification';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        if ($this->isExpiringSoon()) {
            return 'expiring_soon';
        }

        if ($this->needsRenewal()) {
            return 'needs_renewal';
        }

        return 'active';
    }

    /**
     * Get status color
     *
     * @return string
     */
    public function getStatusColor(): string
    {
        return match($this->getStatus()) {
            'active' => 'success',
            'expiring_soon' => 'warning',
            'needs_renewal' => 'info',
            'pending_verification' => 'secondary',
            'expired' => 'danger',
            'inactive' => 'light',
            default => 'secondary',
        };
    }

    /**
     * Get status badge
     *
     * @return string
     */
    public function getStatusBadge(): string
    {
        $status = $this->getStatus();
        $color = $this->getStatusColor();
        $displayStatus = ucfirst(str_replace('_', ' ', $status));
        
        return "<span class='badge bg-{$color}'>{$displayStatus}</span>";
    }

    /**
     * Get level badge
     *
     * @return string
     */
    public function getLevelBadge(): string
    {
        $level = $this->certification_level;
        $displayLevel = self::getCertificationLevels()[$level] ?? $level;
        
        $color = match($level) {
            self::LEVEL_BASIC => 'secondary',
            self::LEVEL_INTERMEDIATE => 'info',
            self::LEVEL_ADVANCED => 'warning',
            self::LEVEL_MASTER => 'success',
            self::LEVEL_SPECIALIST => 'primary',
            default => 'light',
        };
        
        return "<span class='badge bg-{$color}'>{$displayLevel}</span>";
    }

    /**
     * Verify certification
     *
     * @param int $verifierId
     * @param string $notes
     * @return void
     */
    public function verify(int $verifierId, string $notes = ''): void
    {
        $this->is_verified = true;
        $this->verified_by = $verifierId;
        $this->verified_date = now();
        $this->verification_notes = $notes;
        $this->save();
    }

    /**
     * Unverify certification
     *
     * @param string $reason
     * @return void
     */
    public function unverify(string $reason = ''): void
    {
        $this->is_verified = false;
        $this->verification_notes = $reason;
        $this->save();
    }

    /**
     * Activate certification
     *
     * @return void
     */
    public function activate(): void
    {
        $this->is_active = true;
        $this->save();
    }

    /**
     * Deactivate certification
     *
     * @param string $reason
     * @return void
     */
    public function deactivate(string $reason = ''): void
    {
        $this->is_active = false;
        $this->notes = $reason;
        $this->save();
    }

    /**
     * Renew certification
     *
     * @param string $newExpiryDate
     * @param string $newRenewalDate
     * @param string $notes
     * @return void
     */
    public function renew(string $newExpiryDate, string $newRenewalDate, string $notes = ''): void
    {
        $this->expiry_date = $newExpiryDate;
        $this->renewal_date = $newRenewalDate;
        $this->notes = $notes;
        $this->save();
    }

    /**
     * Get certification validity period in years
     *
     * @return float|null
     */
    public function getValidityPeriod(): ?float
    {
        if (!$this->issue_date || !$this->expiry_date) {
            return null;
        }

        $years = $this->issue_date->diffInYears($this->expiry_date);
        return round($years, 1);
    }

    /**
     * Get certification summary for reports
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'certification_name' => $this->certification_name,
            'certification_code' => $this->certification_code,
            'issuing_organization' => $this->issuing_organization,
            'level' => $this->certification_level,
            'level_display' => self::getCertificationLevels()[$this->certification_level] ?? $this->certification_level,
            'issue_date' => $this->issue_date ? $this->issue_date->format('Y-m-d') : null,
            'expiry_date' => $this->expiry_date ? $this->expiry_date->format('Y-m-d') : null,
            'renewal_date' => $this->renewal_date ? $this->renewal_date->format('Y-m-d') : null,
            'status' => $this->getStatus(),
            'status_color' => $this->getStatusColor(),
            'is_verified' => $this->is_verified,
            'is_active' => $this->is_active,
            'days_until_expiry' => $this->getDaysUntilExpiry(),
            'validity_period' => $this->getValidityPeriod(),
            'certification_number' => $this->certification_number,
            'has_certificate_file' => !empty($this->certificate_file_path),
        ];
    }

    /**
     * Get certification score for performance metrics
     *
     * @return float
     */
    public function calculateCertificationScore(): float
    {
        $baseScore = 0;

        // Level scoring
        $levelScore = match($this->certification_level) {
            self::LEVEL_BASIC => 25,
            self::LEVEL_INTERMEDIATE => 50,
            self::LEVEL_ADVANCED => 75,
            self::LEVEL_MASTER => 90,
            self::LEVEL_SPECIALIST => 100,
            default => 0,
        };

        // Organization prestige scoring
        $organizationScore = match($this->issuing_organization) {
            'ASE' => 100,
            'I-CAR' => 90,
            'NAPA' => 80,
            'AAA' => 85,
            'OEM' => 95,
            'EVT' => 100,
            default => 60,
        };

        // Status scoring
        $statusScore = match($this->getStatus()) {
            'active' => 100,
            'expiring_soon' => 75,
            'needs_renewal' => 50,
            'pending_verification' => 25,
            'expired' => 0,
            'inactive' => 0,
            default => 0,
        };

        // Weighted average: 40% level, 30% organization, 30% status
        $baseScore = ($levelScore * 0.4) + ($organizationScore * 0.3) + ($statusScore * 0.3);

        // Bonus for verified certifications
        if ($this->is_verified) {
            $baseScore += 10;
        }

        // Bonus for having certificate file
        if (!empty($this->certificate_file_path)) {
            $baseScore += 5;
        }

        return min($baseScore, 100);
    }
}