<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingSetting extends Model
{
    protected $table = 'booking_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get a setting by key.
     */
    public static function getValue(string $key, $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }
        // Try to decode as JSON if it's a string (for array values)
        $value = $setting->value;
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }
        return $value;
    }

    /**
     * Set a setting value.
     */
    public static function setValue(string $key, mixed $value): bool
    {
        // Convert arrays to JSON for storage
        $stored = is_array($value) ? json_encode($value) : $value;
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored]
        ) ? true : false;
    }

    /**
     * Get default working hours.
     */
    public static function getWorkingHours(): array
    {
        $hours = static::getValue('working_hours', [
            'monday'    => ['enabled' => true,  'start' => '08:00', 'end' => '17:00'],
            'tuesday'   => ['enabled' => true,  'start' => '08:00', 'end' => '17:00'],
            'wednesday' => ['enabled' => true,  'start' => '08:00', 'end' => '17:00'],
            'thursday'  => ['enabled' => true,  'start' => '08:00', 'end' => '17:00'],
            'friday'    => ['enabled' => true,  'start' => '08:00', 'end' => '17:00'],
            'saturday'  => ['enabled' => true,  'start' => '08:00', 'end' => '12:00'],
            'sunday'    => ['enabled' => false, 'start' => '08:00', 'end' => '17:00'],
        ]);
        return $hours;
    }

    /**
     * Get slot duration in minutes.
     */
    public static function getSlotDuration(): int
    {
        return (int) static::getValue('slot_duration', 30);
    }

    /**
     * Get max days ahead for booking.
     */
    public static function getMaxDaysAhead(): int
    {
        return (int) static::getValue('max_days_ahead', 30);
    }
}
