<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceType extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * key => display name for ALL service types, ordered by sort_order.
     * DB is the single source of truth; config/service-types.php is the
     * fallback only (e.g. before migrations run).
     */
    public static function list(): array
    {
        $rows = static::orderBy('sort_order')->get();
        if ($rows->isEmpty()) {
            return config('service-types.list', []);
        }

        return $rows->pluck('name', 'key')->all();
    }

    /** key => icon for all service types (DB first, config fallback). */
    public static function icons(): array
    {
        $rows = static::orderBy('sort_order')->get();
        if ($rows->isEmpty()) {
            return config('service-types.icons', []);
        }

        return $rows->pluck('icon', 'key')->all();
    }

    /** All service type keys (for validation rules). */
    public static function keys(): array
    {
        return array_keys(static::list());
    }

    /** key => display name for ACTIVE service types only. */
    public static function activeList(): array
    {
        $rows = static::where('is_active', true)->orderBy('sort_order')->get();
        if ($rows->isEmpty()) {
            return config('service-types.list', []);
        }

        return $rows->pluck('name', 'key')->all();
    }

    /** Active service type keys (for booking/validation). */
    public static function activeKeys(): array
    {
        return array_keys(static::activeList());
    }

    /** Display name for a single key (DB first, readable fallback). */
    public static function name(string $key): string
    {
        $list = static::list();

        return $list[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
}
