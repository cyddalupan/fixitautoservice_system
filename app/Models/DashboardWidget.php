<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'widget_type',
        'widget_title',
        'metric_name',
        'widget_config',
        'column_position',
        'row_position',
        'width',
        'height',
        'is_visible',
        'is_collapsed',
        'refresh_interval',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'widget_config' => 'array',
        'is_visible' => 'boolean',
        'is_collapsed' => 'boolean',
        'refresh_interval' => 'integer',
        'column_position' => 'integer',
        'row_position' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    /**
     * Get the user that owns the widget.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for visible widgets.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope for widgets by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for widgets by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $type)
    {
        return $query->where('widget_type', $type);
    }

    /**
     * Get widget configuration value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig($key, $default = null)
    {
        $config = $this->widget_config ?? [];
        return $config[$key] ?? $default;
    }

    /**
     * Set widget configuration value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setConfig($key, $value)
    {
        $config = $this->widget_config ?? [];
        $config[$key] = $value;
        $this->widget_config = $config;
    }

    /**
     * Get default widget configuration.
     *
     * @param string $widgetType
     * @return array
     */
    public static function getDefaultConfig($widgetType)
    {
        $defaults = [
            'metric_card' => [
                'show_trend' => true,
                'show_icon' => true,
                'color' => 'primary',
                'format' => 'currency',
                'decimals' => 2,
            ],
            'chart' => [
                'chart_type' => 'line',
                'show_legend' => true,
                'show_grid' => true,
                'height' => 300,
                'colors' => ['#3498db', '#2ecc71', '#e74c3c', '#f39c12'],
            ],
            'table' => [
                'columns' => [],
                'paginate' => true,
                'page_size' => 10,
                'sortable' => true,
                'searchable' => true,
            ],
            'kpi' => [
                'target_value' => null,
                'show_target' => true,
                'show_progress' => true,
                'color_scheme' => 'gradient',
            ],
        ];

        return $defaults[$widgetType] ?? [];
    }

    /**
     * Create a default widget for a user.
     *
     * @param int $userId
     * @param string $widgetType
     * @param string $title
     * @param string|null $metricName
     * @param array $config
     * @param int $column
     * @param int $row
     * @param int $width
     * @param int $height
     * @return DashboardWidget
     */
    public static function createDefaultWidget($userId, $widgetType, $title, $metricName = null, $config = [], $column = 0, $row = 0, $width = 1, $height = 1)
    {
        $defaultConfig = self::getDefaultConfig($widgetType);
        $mergedConfig = array_merge($defaultConfig, $config);

        return self::create([
            'user_id' => $userId,
            'widget_type' => $widgetType,
            'widget_title' => $title,
            'metric_name' => $metricName,
            'widget_config' => $mergedConfig,
            'column_position' => $column,
            'row_position' => $row,
            'width' => $width,
            'height' => $height,
            'is_visible' => true,
            'is_collapsed' => false,
            'refresh_interval' => 300,
        ]);
    }

    /**
     * Get all widgets for a user in grid order.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUserWidgets($userId)
    {
        return self::byUser($userId)
            ->visible()
            ->orderBy('column_position')
            ->orderBy('row_position')
            ->get();
    }

    /**
     * Update widget position.
     *
     * @param int $widgetId
     * @param int $column
     * @param int $row
     * @return bool
     */
    public static function updateWidgetPosition($widgetId, $column, $row)
    {
        return self::where('id', $widgetId)->update([
            'column_position' => $column,
            'row_position' => $row,
        ]);
    }

    /**
     * Toggle widget visibility.
     *
     * @param int $widgetId
     * @return bool
     */
    public static function toggleVisibility($widgetId)
    {
        $widget = self::find($widgetId);
        if (!$widget) {
            return false;
        }

        $widget->is_visible = !$widget->is_visible;
        return $widget->save();
    }

    /**
     * Toggle widget collapsed state.
     *
     * @param int $widgetId
     * @return bool
     */
    public static function toggleCollapsed($widgetId)
    {
        $widget = self::find($widgetId);
        if (!$widget) {
            return false;
        }

        $widget->is_collapsed = !$widget->is_collapsed;
        return $widget->save();
    }
}
