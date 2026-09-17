<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Run migrations on a throwaway file sqlite test DB so feature tests
    // (e.g. FixitBlueprintSpecTest) can exercise real schema.
    use RefreshDatabase;

    // FixTDDHarness (2026-08-06): Passing --drop-views/--drop-types=false to
    // migrate:fresh silently made it a no-op on the sqlite test DB (0 tables
    // created, RefreshDatabaseState::$migrated still set true), leaving tests
    // failing with "no such table". Returning [] here lets migrate:fresh run
    // cleanly against the throwaway sqlite DB. Also forces the test DB to the
    // throwaway sqlite file (see .env.testing) instead of production MySQL.
    protected function migrateFreshUsing()
    {
        return [];
    }
}
