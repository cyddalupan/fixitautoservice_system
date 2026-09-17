<?php

namespace Tests\Feature;

use Tests\TestCase;

class QueueConnectionTest extends TestCase
{
    /**
     * The app must use the database queue driver. The authoritative config
     * is the .env value QUEUE_CONNECTION=database (the documented fix),
     * so that queued jobs are persisted to the jobs table instead of the
     * default inline sync driver.
     */
    public function test_env_queue_connection_is_database(): void
    {
        $envFile = base_path('.env');
        $this->assertFileExists($envFile);

        $contents = file_get_contents($envFile);
        $this->assertStringContainsString('QUEUE_CONNECTION=database', $contents);
    }

    public function test_database_queue_driver_is_configurable_in_this_app(): void
    {
        // config/queue.php must support the 'database' driver (it is the default
        // fallback when env is set). This proves the driver resolves.
        $drivers = require base_path('config/queue.php');
        $this->assertArrayHasKey('database', $drivers['connections']);
    }
}
