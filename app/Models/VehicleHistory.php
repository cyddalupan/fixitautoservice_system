<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_description',
        'use_count',
        'last_used_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    /**
     * Increment the use count and update last used timestamp.
     */
    public function incrementUse(): void
    {
        $this->update([
            'use_count' => $this->use_count + 1,
            'last_used_at' => now(),
        ]);
    }

    /**
     * Find or create a vehicle history entry.
     */
    public static function findOrCreate(string $description): self
    {
        $description = trim($description);
        
        $history = self::where('vehicle_description', $description)->first();
        
        if (!$history) {
            $history = self::create([
                'vehicle_description' => $description,
                'use_count' => 0,
            ]);
        }
        
        return $history;
    }

    /**
     * Get popular vehicle descriptions (most used).
     */
    public static function getPopular(int $limit = 10): array
    {
        return self::orderBy('use_count', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->limit($limit)
            ->pluck('vehicle_description')
            ->toArray();
    }

    /**
     * Search for vehicle descriptions.
     */
    public static function search(string $query, int $limit = 10): array
    {
        return self::where('vehicle_description', 'like', "%{$query}%")
            ->orderBy('use_count', 'desc')
            ->orderBy('last_used_at', 'desc')
            ->limit($limit)
            ->pluck('vehicle_description')
            ->toArray();
    }
}
