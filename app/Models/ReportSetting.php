<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportSetting extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'report_type',
        'columns',
        'filters',
        'schedule',
        'recipients',
        'format',
        'is_default',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the report setting.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default settings for a report type.
     */
    public static function getDefaultSettings(string $reportType): ?self
    {
        return self::where('report_type', $reportType)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get user-specific settings for a report type.
     */
    public static function getUserSettings(string $reportType, int $userId): ?self
    {
        return self::where('report_type', $reportType)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get effective settings (user-specific or default).
     */
    public static function getEffectiveSettings(string $reportType, ?int $userId = null): array
    {
        $settings = [];
        
        // Try to get user-specific settings first
        if ($userId) {
            $userSettings = self::getUserSettings($reportType, $userId);
            if ($userSettings) {
                $settings = $userSettings->toArray();
            }
        }
        
        // If no user settings, get default settings
        if (empty($settings)) {
            $defaultSettings = self::getDefaultSettings($reportType);
            if ($defaultSettings) {
                $settings = $defaultSettings->toArray();
            }
        }
        
        // Return default structure if no settings found
        if (empty($settings)) {
            $settings = [
                'columns' => self::getDefaultColumns($reportType),
                'filters' => [],
                'format' => 'pdf',
            ];
        }
        
        return $settings;
    }

    /**
     * Get default columns for a report type.
     */
    public static function getDefaultColumns(string $reportType): array
    {
        return match($reportType) {
            'daily_activity' => [
                'date', 'appointments', 'work_orders', 'invoices', 
                'revenue', 'payments', 'new_customers'
            ],
            'monthly_performance' => [
                'month', 'revenue', 'expenses', 'profit', 
                'jobs_completed', 'avg_job_value', 'new_customers',
                'repeat_customers', 'customer_satisfaction'
            ],
            'customer_history' => [
                'customer_name', 'vehicle', 'service_date', 
                'service_type', 'cost', 'technician', 'status'
            ],
            default => [],
        };
    }
}