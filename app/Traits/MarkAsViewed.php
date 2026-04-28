<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

trait MarkAsViewed
{
    /**
     * Mark a record as viewed if it hasn't been viewed yet.
     * Uses a cache guard to only write to DB once per record per session.
     */
    public function markAsViewed(Model $record): void
    {
        if ($record->viewed_at === null) {
            $cacheKey = 'viewed_' . get_class($record) . '_' . $record->id;
            if (!Cache::has($cacheKey)) {
                $record->update(['viewed_at' => now()]);
                Cache::put($cacheKey, true, now()->addDay());
            }
        }
    }

    /**
     * Query scope: only records that haven't been viewed.
     */
    public function scopeUnviewed($query)
    {
        return $query->whereNull('viewed_at');
    }

    /**
     * Mark as unviewed (when record is updated).
     */
    public function markAsUnviewed(Model $record): void
    {
        $record->update(['viewed_at' => null]);
        $cacheKey = 'viewed_' . get_class($record) . '_' . $record->id;
        Cache::forget($cacheKey);
    }
}
