<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Helper trait for migrations that may encounter duplicate schema objects
 * in SQLite testing. MySQL/MariaDB handles duplicates gracefully.
 *
 * These guards are needed because this legacy app has multiple migration
 * files that create the same tables/indexes (e.g., tax_rates appears in
 * 3 different POS migrations).
 */
trait DatabaseMigrationGuard
{
    /**
     * Create a table only if it doesn't already exist.
     */
    protected function createTableIfNotExists(string $table, \Closure $callback): void
    {
        if (!Schema::hasTable($table)) {
            Schema::create($table, $callback);
        }
    }

    /**
     * Add an index to a table, silently skipping if it already exists.
     * This is needed because SQLite rejects duplicate index names.
     */
    protected function addIndexIfNotExists(
        string $table,
        array|string $columns,
        ?string $indexName = null
    ): void {
        if (config('database.default') !== 'sqlite') {
            // MySQL handles duplicates gracefully
            Schema::table($table, function ($t) use ($columns, $indexName) {
                $method = is_array($columns) ? 'index' : 'index';
                if ($indexName) {
                    $t->index($columns, $indexName);
                } else {
                    $t->index($columns);
                }
            });
            return;
        }

        // SQLite: check index existence first
        $name = $indexName ?? $table . '_' . implode('_', (array)$columns) . '_index';
        $exists = DB::select(
            "SELECT 1 FROM sqlite_master WHERE type='index' AND name=?",
            [$name]
        );
        if (empty($exists)) {
            Schema::table($table, function ($t) use ($columns, $name) {
                $t->index($columns, $name);
            });
        }
    }
}
