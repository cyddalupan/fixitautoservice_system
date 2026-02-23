<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PerformanceMetric extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'technician_performance_metrics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'technician_id',
        'metric_date',
        'metric_type',
        'metric_value',
        'target_value',
        'score',
        'weight',
        'period',
        'work_order_id',
        'appointment_id',
        'notes',
        'calculated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metric_date' => 'date',
        'metric_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'score' => 'decimal:2',
        'weight' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    /**
     * Metric type constants
     */
    const TYPE_EFFICIENCY = 'efficiency';
    const TYPE_QUALITY = 'quality';
    const TYPE_PRODUCTIVITY = 'productivity';
    const TYPE_CUSTOMER_SATISFACTION = 'customer_satisfaction';
    const TYPE_SAFETY = 'safety';

    /**
     * Period constants
     */
    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_QUARTERLY = 'quarterly';
    const PERIOD_YEARLY = 'yearly';

    /**
     * Get available metric types
     *
     * @return array
     */
    public static function getMetricTypes(): array
    {
        return [
            self::TYPE_EFFICIENCY => 'Efficiency',
            self::TYPE_QUALITY => 'Quality',
            self::TYPE_PRODUCTIVITY => 'Productivity',
            self::TYPE_CUSTOMER_SATISFACTION => 'Customer Satisfaction',
            self::TYPE_SAFETY => 'Safety',
        ];
    }

    /**
     * Get available periods
     *
     * @return array
     */
    public static function getPeriods(): array
    {
        return [
            self::PERIOD_DAILY => 'Daily',
            self::PERIOD_WEEKLY => 'Weekly',
            self::PERIOD_MONTHLY => 'Monthly',
            self::PERIOD_QUARTERLY => 'Quarterly',
            self::PERIOD_YEARLY => 'Yearly',
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
     * Relationship: Work Order
     *
     * @return BelongsTo
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * Relationship: Appointment
     *
     * @return BelongsTo
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
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
     * Scope: For specific metric type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $metricType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMetricType($query, $metricType)
    {
        return $query->where('metric_type', $metricType);
    }

    /**
     * Scope: For specific period
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $period
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope: Between dates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('metric_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Latest metrics
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatestMetrics($query, $limit = 10)
    {
        return $query->orderBy('metric_date', 'desc')->limit($limit);
    }

    /**
     * Calculate performance score
     *
     * @return float
     */
    public function calculateScore(): float
    {
        if ($this->target_value === null || $this->target_value == 0) {
            return $this->metric_value;
        }

        // Calculate percentage of target achieved
        $percentage = ($this->metric_value / $this->target_value) * 100;
        
        // Cap at 100% for bonus scenarios
        return min($percentage, 100);
    }

    /**
     * Check if metric meets target
     *
     * @return bool
     */
    public function meetsTarget(): bool
    {
        if ($this->target_value === null) {
            return true; // No target set
        }

        return $this->metric_value >= $this->target_value;
    }

    /**
     * Get performance rating
     *
     * @return string
     */
    public function getPerformanceRating(): string
    {
        $score = $this->score ?? $this->calculateScore();

        if ($score >= 90) {
            return 'Excellent';
        } elseif ($score >= 80) {
            return 'Good';
        } elseif ($score >= 70) {
            return 'Average';
        } elseif ($score >= 60) {
            return 'Needs Improvement';
        } else {
            return 'Poor';
        }
    }

    /**
     * Get performance color
     *
     * @return string
     */
    public function getPerformanceColor(): string
    {
        $score = $this->score ?? $this->calculateScore();

        if ($score >= 90) {
            return 'success';
        } elseif ($score >= 80) {
            return 'info';
        } elseif ($score >= 70) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    /**
     * Calculate composite score for a technician
     *
     * @param int $technicianId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function calculateCompositeScore(int $technicianId, string $startDate, string $endDate): array
    {
        $metrics = self::forTechnician($technicianId)
            ->betweenDates($startDate, $endDate)
            ->get();

        $totalScore = 0;
        $totalWeight = 0;
        $metricScores = [];

        foreach ($metrics as $metric) {
            $score = $metric->score ?? $metric->calculateScore();
            $weight = $metric->weight ?? 1.0;
            
            $totalScore += $score * $weight;
            $totalWeight += $weight;
            
            $metricScores[$metric->metric_type] = [
                'score' => $score,
                'weight' => $weight,
                'meets_target' => $metric->meetsTarget(),
                'rating' => $metric->getPerformanceRating(),
            ];
        }

        $compositeScore = $totalWeight > 0 ? $totalScore / $totalWeight : 0;

        return [
            'composite_score' => round($compositeScore, 2),
            'total_metrics' => $metrics->count(),
            'metric_details' => $metricScores,
            'overall_rating' => self::getRatingFromScore($compositeScore),
        ];
    }

    /**
     * Get rating from score
     *
     * @param float $score
     * @return string
     */
    private static function getRatingFromScore(float $score): string
    {
        if ($score >= 90) {
            return 'Excellent';
        } elseif ($score >= 80) {
            return 'Good';
        } elseif ($score >= 70) {
            return 'Average';
        } elseif ($score >= 60) {
            return 'Needs Improvement';
        } else {
            return 'Poor';
        }
    }

    /**
     * Generate technician performance report
     *
     * @param int $technicianId
     * @param string $period
     * @return array
     */
    public static function generatePerformanceReport(int $technicianId, string $period = 'monthly'): array
    {
        $endDate = Carbon::now();
        
        switch ($period) {
            case 'daily':
                $startDate = $endDate->copy()->subDay();
                break;
            case 'weekly':
                $startDate = $endDate->copy()->subWeek();
                break;
            case 'monthly':
                $startDate = $endDate->copy()->subMonth();
                break;
            case 'quarterly':
                $startDate = $endDate->copy()->subQuarter();
                break;
            case 'yearly':
                $startDate = $endDate->copy()->subYear();
                break;
            default:
                $startDate = $endDate->copy()->subMonth();
        }

        $compositeScore = self::calculateCompositeScore($technicianId, $startDate->toDateString(), $endDate->toDateString());

        // Get trend data
        $previousPeriodStart = $startDate->copy()->sub($period);
        $previousPeriodEnd = $startDate->copy()->subDay();
        
        $previousCompositeScore = self::calculateCompositeScore($technicianId, $previousPeriodStart->toDateString(), $previousPeriodEnd->toDateString());

        $trend = $compositeScore['composite_score'] - $previousCompositeScore['composite_score'];

        return [
            'technician_id' => $technicianId,
            'period' => $period,
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'composite_score' => $compositeScore['composite_score'],
            'previous_composite_score' => $previousCompositeScore['composite_score'],
            'trend' => $trend,
            'trend_direction' => $trend >= 0 ? 'up' : 'down',
            'metric_details' => $compositeScore['metric_details'],
            'overall_rating' => $compositeScore['overall_rating'],
            'total_metrics_analyzed' => $compositeScore['total_metrics'],
            'recommendations' => self::generateRecommendations($compositeScore['metric_details']),
        ];
    }

    /**
     * Generate recommendations based on metric scores
     *
     * @param array $metricDetails
     * @return array
     */
    private static function generateRecommendations(array $metricDetails): array
    {
        $recommendations = [];

        foreach ($metricDetails as $metricType => $details) {
            if ($details['score'] < 70) {
                $recommendations[] = match($metricType) {
                    'efficiency' => 'Focus on improving workflow efficiency and reducing time per job',
                    'quality' => 'Increase quality control checks and follow standardized procedures',
                    'productivity' => 'Optimize task scheduling and reduce non-productive time',
                    'customer_satisfaction' => 'Improve communication with customers and follow-up procedures',
                    'safety' => 'Review safety protocols and complete safety training',
                    default => "Improve {$metricType} performance through targeted training",
                };
            } elseif ($details['score'] >= 90) {
                $recommendations[] = "Excellent {$metricType} performance - consider mentoring other technicians";
            }
        }

        return array_slice($recommendations, 0, 3); // Return top 3 recommendations
    }

    /**
     * Check if metric is calculated
     *
     * @return bool
     */
    public function isCalculated(): bool
    {
        return $this->calculated_at !== null;
    }

    /**
     * Mark as calculated
     *
     * @return void
     */
    public function markAsCalculated(): void
    {
        $this->calculated_at = Carbon::now();
        $this->save();
    }
}