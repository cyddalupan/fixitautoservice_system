<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSatisfactionSurvey extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'work_order_id',
        'customer_id',
        'technician_id',
        'overall_rating',
        'quality_rating',
        'timeliness_rating',
        'communication_rating',
        'cleanliness_rating',
        'value_rating',
        'positive_comments',
        'improvement_suggestions',
        'would_recommend',
        'would_return',
        'status',
        'follow_up_notes',
        'follow_up_by',
        'follow_up_date',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'overall_rating' => 'integer',
        'quality_rating' => 'integer',
        'timeliness_rating' => 'integer',
        'communication_rating' => 'integer',
        'cleanliness_rating' => 'integer',
        'value_rating' => 'integer',
        'would_recommend' => 'boolean',
        'would_return' => 'boolean',
        'follow_up_date' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FOLLOW_UP_NEEDED = 'follow_up_needed';
    public const STATUS_RESOLVED = 'resolved';

    /**
     * Get all statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FOLLOW_UP_NEEDED => 'Follow-up Needed',
            self::STATUS_RESOLVED => 'Resolved',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get status color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FOLLOW_UP_NEEDED => 'danger',
            self::STATUS_RESOLVED => 'info',
            default => 'secondary',
        };
    }

    /**
     * Calculate average rating
     */
    public function calculateAverageRating(): float
    {
        $ratings = [
            $this->overall_rating,
            $this->quality_rating,
            $this->timeliness_rating,
            $this->communication_rating,
            $this->cleanliness_rating,
            $this->value_rating,
        ];

        $validRatings = array_filter($ratings, function ($rating) {
            return $rating > 0;
        });

        if (empty($validRatings)) {
            return 0;
        }

        return round(array_sum($validRatings) / count($validRatings), 2);
    }

    /**
     * Get rating label
     */
    public function getRatingLabel(int $rating): string
    {
        return match($rating) {
            1 => 'Poor',
            2 => 'Fair',
            3 => 'Good',
            4 => 'Very Good',
            5 => 'Excellent',
            default => 'Not Rated',
        };
    }

    /**
     * Get rating color
     */
    public function getRatingColor(int $rating): string
    {
        return match($rating) {
            1, 2 => 'danger',
            3 => 'warning',
            4, 5 => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get star rating HTML
     */
    public function getStarRating(int $rating): string
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $rating ? '★' : '☆';
        }
        return $stars;
    }

    /**
     * Check if survey is positive
     */
    public function isPositive(): bool
    {
        return $this->calculateAverageRating() >= 4 && $this->would_recommend && $this->would_return;
    }

    /**
     * Check if survey is negative
     */
    public function isNegative(): bool
    {
        return $this->calculateAverageRating() <= 2 || !$this->would_recommend || !$this->would_return;
    }

    /**
     * Check if survey is neutral
     */
    public function isNeutral(): bool
    {
        return !$this->isPositive() && !$this->isNegative();
    }

    /**
     * Get sentiment
     */
    public function getSentiment(): string
    {
        if ($this->isPositive()) {
            return 'positive';
        } elseif ($this->isNegative()) {
            return 'negative';
        } else {
            return 'neutral';
        }
    }

    /**
     * Get sentiment label
     */
    public function getSentimentLabel(): string
    {
        return match($this->getSentiment()) {
            'positive' => 'Positive',
            'negative' => 'Negative',
            'neutral' => 'Neutral',
            default => 'Unknown',
        };
    }

    /**
     * Get sentiment color
     */
    public function getSentimentColor(): string
    {
        return match($this->getSentiment()) {
            'positive' => 'success',
            'negative' => 'danger',
            'neutral' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Check if survey needs follow-up
     */
    public function needsFollowUp(): bool
    {
        return $this->isNegative() || 
               !empty($this->improvement_suggestions) ||
               $this->status === self::STATUS_FOLLOW_UP_NEEDED;
    }

    /**
     * Complete survey
     */
    public function complete(array $data): bool
    {
        $this->fill($data);
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        
        if ($this->needsFollowUp()) {
            $this->status = self::STATUS_FOLLOW_UP_NEEDED;
        }

        return $this->save();
    }

    /**
     * Mark as resolved
     */
    public function markAsResolved(int $userId, string $notes): bool
    {
        $this->status = self::STATUS_RESOLVED;
        $this->follow_up_by = $userId;
        $this->follow_up_date = now();
        $this->follow_up_notes = $notes;
        return $this->save();
    }

    /**
     * Get rating breakdown
     */
    public function getRatingBreakdown(): array
    {
        return [
            'overall' => [
                'rating' => $this->overall_rating,
                'label' => $this->getRatingLabel($this->overall_rating),
                'color' => $this->getRatingColor($this->overall_rating),
                'stars' => $this->getStarRating($this->overall_rating),
            ],
            'quality' => [
                'rating' => $this->quality_rating,
                'label' => $this->getRatingLabel($this->quality_rating),
                'color' => $this->getRatingColor($this->quality_rating),
                'stars' => $this->getStarRating($this->quality_rating),
            ],
            'timeliness' => [
                'rating' => $this->timeliness_rating,
                'label' => $this->getRatingLabel($this->timeliness_rating),
                'color' => $this->getRatingColor($this->timeliness_rating),
                'stars' => $this->getStarRating($this->timeliness_rating),
            ],
            'communication' => [
                'rating' => $this->communication_rating,
                'label' => $this->getRatingLabel($this->communication_rating),
                'color' => $this->getRatingColor($this->communication_rating),
                'stars' => $this->getStarRating($this->communication_rating),
            ],
            'cleanliness' => [
                'rating' => $this->cleanliness_rating,
                'label' => $this->getRatingLabel($this->cleanliness_rating),
                'color' => $this->getRatingColor($this->cleanliness_rating),
                'stars' => $this->getStarRating($this->cleanliness_rating),
            ],
            'value' => [
                'rating' => $this->value_rating,
                'label' => $this->getRatingLabel($this->value_rating),
                'color' => $this->getRatingColor($this->value_rating),
                'stars' => $this->getStarRating($this->value_rating),
            ],
        ];
    }

    /**
     * Scope: Pending surveys
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Completed surveys
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Needs follow-up
     */
    public function scopeNeedsFollowUp($query)
    {
        return $query->where('status', self::STATUS_FOLLOW_UP_NEEDED);
    }

    /**
     * Scope: Positive surveys
     */
    public function scopePositive($query)
    {
        return $query->where(function ($q) {
            $q->where('overall_rating', '>=', 4)
              ->where('would_recommend', true)
              ->where('would_return', true);
        });
    }

    /**
     * Scope: Negative surveys
     */
    public function scopeNegative($query)
    {
        return $query->where(function ($q) {
            $q->where('overall_rating', '<=', 2)
              ->orWhere('would_recommend', false)
              ->orWhere('would_return', false);
        });
    }

    /**
     * Scope: By customer
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: By technician
     */
    public function scopeByTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }

    /**
     * Relationship: Work order
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Relationship: Customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship: Technician
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Relationship: Follow-up user
     */
    public function followUpUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follow_up_by');
    }
}