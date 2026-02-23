<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetentionAnalytics extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'first_service_date',
        'last_service_date',
        'total_services',
        'total_spent',
        'days_since_last_service',
        'retention_status',
        'retention_score',
        'service_pattern',
        'preferred_services',
        'next_expected_service',
        'is_at_risk',
        'risk_assessed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'first_service_date' => 'date',
        'last_service_date' => 'date',
        'total_spent' => 'decimal:2',
        'retention_score' => 'decimal:2',
        'service_pattern' => 'array',
        'preferred_services' => 'array',
        'next_expected_service' => 'date',
        'is_at_risk' => 'boolean',
        'risk_assessed_at' => 'datetime',
        'days_since_last_service' => 'integer',
        'total_services' => 'integer',
    ];

    /**
     * Get the customer that owns the retention analytics.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope for customers by retention status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('retention_status', $status);
    }

    /**
     * Scope for at-risk customers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAtRisk($query)
    {
        return $query->where('is_at_risk', true);
    }

    /**
     * Scope for customers with upcoming expected service.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $daysAhead
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithUpcomingService($query, $daysAhead = 30)
    {
        $date = now()->addDays($daysAhead)->toDateString();
        return $query->whereNotNull('next_expected_service')
            ->where('next_expected_service', '<=', $date);
    }

    /**
     * Scope for customers who have lapsed.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $daysThreshold
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLapsed($query, $daysThreshold = 90)
    {
        return $query->where('days_since_last_service', '>', $daysThreshold)
            ->where('retention_status', '!=', 'lost');
    }

    /**
     * Calculate retention score based on various factors.
     *
     * @param int $customerId
     * @return float
     */
    public static function calculateRetentionScore($customerId)
    {
        $analytics = self::where('customer_id', $customerId)->first();
        if (!$analytics) {
            return 0;
        }

        $score = 0;
        $maxScore = 100;

        // Factor 1: Recency (0-30 points)
        if ($analytics->days_since_last_service <= 30) {
            $score += 30;
        } elseif ($analytics->days_since_last_service <= 60) {
            $score += 20;
        } elseif ($analytics->days_since_last_service <= 90) {
            $score += 10;
        }

        // Factor 2: Frequency (0-30 points)
        if ($analytics->total_services >= 5) {
            $score += 30;
        } elseif ($analytics->total_services >= 3) {
            $score += 20;
        } elseif ($analytics->total_services >= 1) {
            $score += 10;
        }

        // Factor 3: Monetary (0-30 points)
        if ($analytics->total_spent >= 5000) {
            $score += 30;
        } elseif ($analytics->total_spent >= 2000) {
            $score += 20;
        } elseif ($analytics->total_spent >= 500) {
            $score += 10;
        }

        // Factor 4: Service pattern consistency (0-10 points)
        $servicePattern = $analytics->service_pattern ?? [];
        if (!empty($servicePattern) && count($servicePattern) >= 3) {
            $score += 10;
        }

        return min($score, $maxScore);
    }

    /**
     * Determine retention status based on score and recency.
     *
     * @param int $customerId
     * @return string
     */
    public static function determineRetentionStatus($customerId)
    {
        $analytics = self::where('customer_id', $customerId)->first();
        if (!$analytics) {
            return 'new';
        }

        $score = $analytics->retention_score;
        $daysSinceLastService = $analytics->days_since_last_service;

        if ($daysSinceLastService === null) {
            return 'new';
        }

        if ($daysSinceLastService <= 30) {
            if ($score >= 70) {
                return 'active';
            } elseif ($score >= 40) {
                return 'at_risk';
            } else {
                return 'lapsed';
            }
        } elseif ($daysSinceLastService <= 90) {
            if ($score >= 50) {
                return 'at_risk';
            } else {
                return 'lapsed';
            }
        } else {
            if ($daysSinceLastService > 365) {
                return 'lost';
            }
            return 'lapsed';
        }
    }

    /**
     * Update retention analytics for a customer.
     *
     * @param int $customerId
     * @return RetentionAnalytics
     */
    public static function updateCustomerRetention($customerId)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            return null;
        }

        // Get customer service history
        $services = $customer->serviceRecords()->orderBy('service_date')->get();
        
        if ($services->isEmpty()) {
            return null;
        }

        $firstService = $services->first();
        $lastService = $services->last();
        $totalServices = $services->count();
        $totalSpent = $services->sum('total_cost');
        $daysSinceLastService = now()->diffInDays($lastService->service_date);

        // Calculate service pattern
        $servicePattern = [];
        $previousDate = null;
        foreach ($services as $service) {
            if ($previousDate) {
                $interval = $previousDate->diffInDays($service->service_date);
                $servicePattern[] = $interval;
            }
            $previousDate = $service->service_date;
        }

        // Calculate preferred services
        $serviceTypes = $services->groupBy('service_type')->map->count();
        $preferredServices = $serviceTypes->sortDesc()->take(3)->keys()->toArray();

        // Calculate next expected service (average interval + 30 days buffer)
        $nextExpectedService = null;
        if (!empty($servicePattern)) {
            $averageInterval = array_sum($servicePattern) / count($servicePattern);
            $nextExpectedService = $lastService->service_date->addDays($averageInterval + 30);
        }

        // Calculate retention score
        $retentionScore = self::calculateRetentionScore($customerId);

        // Determine retention status
        $retentionStatus = self::determineRetentionStatus($customerId);

        // Determine if at risk
        $isAtRisk = in_array($retentionStatus, ['at_risk', 'lapsed']) || 
                   ($daysSinceLastService > 60 && $retentionScore < 50);

        return self::updateOrCreate(
            ['customer_id' => $customerId],
            [
                'first_service_date' => $firstService->service_date,
                'last_service_date' => $lastService->service_date,
                'total_services' => $totalServices,
                'total_spent' => $totalSpent,
                'days_since_last_service' => $daysSinceLastService,
                'retention_status' => $retentionStatus,
                'retention_score' => $retentionScore,
                'service_pattern' => $servicePattern,
                'preferred_services' => $preferredServices,
                'next_expected_service' => $nextExpectedService,
                'is_at_risk' => $isAtRisk,
                'risk_assessed_at' => $isAtRisk ? now() : null,
            ]
        );
    }

    /**
     * Get retention statistics.
     *
     * @return array
     */
    public static function getRetentionStats()
    {
        $totalCustomers = Customer::count();
        $retentionData = self::select('retention_status')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('ROUND(COUNT(*) * 100.0 / ?, 2) as percentage', [$totalCustomers])
            ->groupBy('retention_status')
            ->get()
            ->keyBy('retention_status');

        $stats = [
            'total_customers' => $totalCustomers,
            'active' => $retentionData->get('active', (object)['count' => 0, 'percentage' => 0]),
            'at_risk' => $retentionData->get('at_risk', (object)['count' => 0, 'percentage' => 0]),
            'lapsed' => $retentionData->get('lapsed', (object)['count' => 0, 'percentage' => 0]),
            'lost' => $retentionData->get('lost', (object)['count' => 0, 'percentage' => 0]),
            'new' => $retentionData->get('new', (object)['count' => 0, 'percentage' => 0]),
        ];

        // Calculate average retention score
        $averageScore = self::avg('retention_score') ?? 0;
        $stats['average_retention_score'] = round($averageScore, 2);

        // Calculate at-risk customers count
        $stats['at_risk_count'] = self::atRisk()->count();

        // Calculate upcoming services count
        $stats['upcoming_services'] = self::withUpcomingService(30)->count();

        return $stats;
    }

    /**
     * Get customers needing retention attention.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCustomersNeedingAttention($limit = 20)
    {
        return self::with('customer')
            ->where(function ($query) {
                $query->where('is_at_risk', true)
                    ->orWhere('retention_status', 'at_risk')
                    ->orWhere('retention_status', 'lapsed')
                    ->orWhereNull('next_expected_service');
            })
            ->orderBy('retention_score')
            ->orderBy('days_since_last_service', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Predict next service date for a customer.
     *
     * @param int $customerId
     * @return \Carbon\Carbon|null
     */
    public static function predictNextService($customerId)
    {
        $analytics = self::where('customer_id', $customerId)->first();
        if (!$analytics || empty($analytics->service_pattern)) {
            return null;
        }

        $averageInterval = array_sum($analytics->service_pattern) / count($analytics->service_pattern);
        return $analytics->last_service_date->addDays($averageInterval);
    }
}
