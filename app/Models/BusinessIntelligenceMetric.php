<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessIntelligenceMetric extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'metric_name',
        'metric_type',
        'metric_date',
        'metric_value',
        'metric_breakdown',
        'category',
        'is_calculated',
        'calculated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metric_value' => 'decimal:2',
        'metric_breakdown' => 'array',
        'is_calculated' => 'boolean',
        'calculated_at' => 'datetime',
        'metric_date' => 'date',
    ];

    /**
     * Scope for metrics by category.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for metrics by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $type)
    {
        return $query->where('metric_type', $type);
    }

    /**
     * Scope for metrics by date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('metric_date', [$startDate, $endDate]);
    }

    /**
     * Scope for calculated metrics.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCalculated($query)
    {
        return $query->where('is_calculated', true);
    }

    /**
     * Get metrics for a specific period.
     *
     * @param string $metricName
     * @param string $periodType daily|weekly|monthly|quarterly|yearly
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getMetricsForPeriod($metricName, $periodType, $startDate, $endDate)
    {
        return self::where('metric_name', $metricName)
            ->where('metric_type', $periodType)
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->orderBy('metric_date')
            ->get();
    }

    /**
     * Calculate and store a metric.
     *
     * @param string $metricName
     * @param string $periodType
     * @param string $date
     * @param float $value
     * @param array $breakdown
     * @param string $category
     * @return BusinessIntelligenceMetric
     */
    public static function calculateMetric($metricName, $periodType, $date, $value, $breakdown = [], $category = 'general')
    {
        return self::updateOrCreate(
            [
                'metric_name' => $metricName,
                'metric_type' => $periodType,
                'metric_date' => $date,
            ],
            [
                'metric_value' => $value,
                'metric_breakdown' => $breakdown,
                'category' => $category,
                'is_calculated' => true,
                'calculated_at' => now(),
            ]
        );
    }

    /**
     * Get the latest metric value.
     *
     * @param string $metricName
     * @param string $periodType
     * @return float|null
     */
    public static function getLatestValue($metricName, $periodType = 'daily')
    {
        $metric = self::where('metric_name', $metricName)
            ->where('metric_type', $periodType)
            ->orderBy('metric_date', 'desc')
            ->first();

        return $metric ? $metric->metric_value : null;
    }

    /**
     * Get metric trend (increase/decrease percentage).
     *
     * @param string $metricName
     * @param string $periodType
     * @param int $periodsBack
     * @return array|null
     */
    public static function getMetricTrend($metricName, $periodType = 'daily', $periodsBack = 1)
    {
        $metrics = self::where('metric_name', $metricName)
            ->where('metric_type', $periodType)
            ->orderBy('metric_date', 'desc')
            ->take($periodsBack + 1)
            ->get();

        if ($metrics->count() < 2) {
            return null;
        }

        $current = $metrics->first()->metric_value;
        $previous = $metrics->last()->metric_value;

        if ($previous == 0) {
            return [
                'current' => $current,
                'previous' => $previous,
                'change' => 0,
                'percentage' => 0,
                'direction' => 'neutral',
            ];
        }

        $change = $current - $previous;
        $percentage = ($change / abs($previous)) * 100;

        return [
            'current' => $current,
            'previous' => $previous,
            'change' => $change,
            'percentage' => round($percentage, 2),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }
}
