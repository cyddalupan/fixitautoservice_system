<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityControlSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'setting_key',
        'setting_value',
        'data_type',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Data type constants
     */
    public const DATA_TYPE_STRING = 'string';
    public const DATA_TYPE_INTEGER = 'integer';
    public const DATA_TYPE_BOOLEAN = 'boolean';
    public const DATA_TYPE_ARRAY = 'array';
    public const DATA_TYPE_JSON = 'json';

    /**
     * Setting key constants
     */
    public const KEY_REQUIRE_QUALITY_CHECKS = 'require_quality_checks';
    public const KEY_AUTO_CREATE_SURVEYS = 'auto_create_surveys';
    public const KEY_SURVEY_DAYS_AFTER_SERVICE = 'survey_days_after_service';
    public const KEY_MINIMUM_PASS_RATE = 'minimum_pass_rate';
    public const KEY_REQUIRE_SUPERVISOR_APPROVAL = 'require_supervisor_approval';
    public const KEY_EXPIRATION_REMINDER_DAYS = 'expiration_reminder_days';
    public const KEY_DEFAULT_QUALITY_CHECK_CATEGORY = 'default_quality_check_category';
    public const KEY_ENABLE_COMPLIANCE_ALERTS = 'enable_compliance_alerts';
    public const KEY_SURVEY_RESPONSE_RATE_TARGET = 'survey_response_rate_target';

    /**
     * Get all data types
     */
    public static function getDataTypes(): array
    {
        return [
            self::DATA_TYPE_STRING => 'String',
            self::DATA_TYPE_INTEGER => 'Integer',
            self::DATA_TYPE_BOOLEAN => 'Boolean',
            self::DATA_TYPE_ARRAY => 'Array',
            self::DATA_TYPE_JSON => 'JSON',
        ];
    }

    /**
     * Get all setting keys with defaults
     */
    public static function getDefaultSettings(): array
    {
        return [
            self::KEY_REQUIRE_QUALITY_CHECKS => [
                'value' => 'true',
                'data_type' => self::DATA_TYPE_BOOLEAN,
                'description' => 'Require quality checks for completed work orders',
            ],
            self::KEY_AUTO_CREATE_SURVEYS => [
                'value' => 'true',
                'data_type' => self::DATA_TYPE_BOOLEAN,
                'description' => 'Automatically create customer satisfaction surveys',
            ],
            self::KEY_SURVEY_DAYS_AFTER_SERVICE => [
                'value' => '3',
                'data_type' => self::DATA_TYPE_INTEGER,
                'description' => 'Days after service to send satisfaction survey',
            ],
            self::KEY_MINIMUM_PASS_RATE => [
                'value' => '85',
                'data_type' => self::DATA_TYPE_INTEGER,
                'description' => 'Minimum pass rate percentage for quality checks',
            ],
            self::KEY_REQUIRE_SUPERVISOR_APPROVAL => [
                'value' => 'true',
                'data_type' => self::DATA_TYPE_BOOLEAN,
                'description' => 'Require supervisor approval for quality checks',
            ],
            self::KEY_EXPIRATION_REMINDER_DAYS => [
                'value' => '30',
                'data_type' => self::DATA_TYPE_INTEGER,
                'description' => 'Days before expiration to send reminders',
            ],
            self::KEY_DEFAULT_QUALITY_CHECK_CATEGORY => [
                'value' => 'workmanship',
                'data_type' => self::DATA_TYPE_STRING,
                'description' => 'Default category for quality checks',
            ],
            self::KEY_ENABLE_COMPLIANCE_ALERTS => [
                'value' => 'true',
                'data_type' => self::DATA_TYPE_BOOLEAN,
                'description' => 'Enable compliance document expiration alerts',
            ],
            self::KEY_SURVEY_RESPONSE_RATE_TARGET => [
                'value' => '25',
                'data_type' => self::DATA_TYPE_INTEGER,
                'description' => 'Target response rate percentage for surveys',
            ],
        ];
    }

    /**
     * Get setting value with proper type casting
     */
    public function getValue()
    {
        return match($this->data_type) {
            self::DATA_TYPE_INTEGER => (int) $this->setting_value,
            self::DATA_TYPE_BOOLEAN => filter_var($this->setting_value, FILTER_VALIDATE_BOOLEAN),
            self::DATA_TYPE_ARRAY => is_array($this->setting_value) ? $this->setting_value : explode(',', $this->setting_value),
            self::DATA_TYPE_JSON => json_decode($this->setting_value, true) ?? $this->setting_value,
            default => $this->setting_value,
        };
    }

    /**
     * Set setting value with proper formatting
     */
    public function setValue($value): void
    {
        $this->setting_value = match($this->data_type) {
            self::DATA_TYPE_INTEGER => (string) $value,
            self::DATA_TYPE_BOOLEAN => $value ? 'true' : 'false',
            self::DATA_TYPE_ARRAY => is_array($value) ? implode(',', $value) : $value,
            self::DATA_TYPE_JSON => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get setting by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        
        if ($setting) {
            return $setting->getValue();
        }

        // Check defaults
        $defaults = self::getDefaultSettings();
        if (isset($defaults[$key])) {
            return match($defaults[$key]['data_type']) {
                self::DATA_TYPE_INTEGER => (int) $defaults[$key]['value'],
                self::DATA_TYPE_BOOLEAN => filter_var($defaults[$key]['value'], FILTER_VALIDATE_BOOLEAN),
                self::DATA_TYPE_ARRAY => explode(',', $defaults[$key]['value']),
                self::DATA_TYPE_JSON => json_decode($defaults[$key]['value'], true) ?? $defaults[$key]['value'],
                default => $defaults[$key]['value'],
            };
        }

        return $default;
    }

    /**
     * Set setting by key
     */
    public static function set(string $key, $value, string $description = null): bool
    {
        $setting = self::where('setting_key', $key)->first();
        
        if (!$setting) {
            $defaults = self::getDefaultSettings();
            
            $setting = new self();
            $setting->setting_key = $key;
            $setting->data_type = $defaults[$key]['data_type'] ?? self::DATA_TYPE_STRING;
            $setting->description = $description ?? $defaults[$key]['description'] ?? null;
        }

        $setting->setValue($value);
        return $setting->save();
    }

    /**
     * Check if setting exists
     */
    public static function has(string $key): bool
    {
        return self::where('setting_key', $key)->exists();
    }

    /**
     * Initialize default settings
     */
    public static function initializeDefaults(): void
    {
        $defaults = self::getDefaultSettings();
        
        foreach ($defaults as $key => $config) {
            if (!self::has($key)) {
                self::create([
                    'setting_key' => $key,
                    'setting_value' => $config['value'],
                    'data_type' => $config['data_type'],
                    'description' => $config['description'],
                ]);
            }
        }
    }

    /**
     * Get all settings as array
     */
    public static function getAll(): array
    {
        $settings = [];
        $dbSettings = self::all();
        
        foreach ($dbSettings as $setting) {
            $settings[$setting->setting_key] = $setting->getValue();
        }

        // Merge with defaults for any missing settings
        $defaults = self::getDefaultSettings();
        foreach ($defaults as $key => $config) {
            if (!isset($settings[$key])) {
                $settings[$key] = match($config['data_type']) {
                    self::DATA_TYPE_INTEGER => (int) $config['value'],
                    self::DATA_TYPE_BOOLEAN => filter_var($config['value'], FILTER_VALIDATE_BOOLEAN),
                    self::DATA_TYPE_ARRAY => explode(',', $config['value']),
                    self::DATA_TYPE_JSON => json_decode($config['value'], true) ?? $config['value'],
                    default => $config['value'],
                };
            }
        }

        return $settings;
    }

    /**
     * Get setting description
     */
    public function getDescription(): string
    {
        return $this->description ?? 'No description available';
    }

    /**
     * Scope: By key
     */
    public function scopeByKey($query, $key)
    {
        return $query->where('setting_key', $key);
    }

    /**
     * Scope: By data type
     */
    public function scopeByDataType($query, $dataType)
    {
        return $query->where('data_type', $dataType);
    }
}