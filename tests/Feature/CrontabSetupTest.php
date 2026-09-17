<?php

namespace Tests\Feature;

use Tests\TestCase;

class CrontabSetupTest extends TestCase
{
    /**
     * The host crontab must contain the fixit-system Laravel scheduler entry
     * (* * * * * php artisan schedule:run) so queued/scheduled jobs run.
     * The fix is a merge (preserve existing entries + append our line).
     */
    public function test_crontab_contains_fixit_system_schedule_entry(): void
    {
        $crontab = shell_exec('crontab -l 2>/dev/null') ?? '';

        $this->assertStringContainsString('/var/www/fixit-system', $crontab);
        $this->assertStringContainsString('schedule:run', $crontab);
    }

    /**
     * The cron daemon must actually be running so the entry is executed.
     */
    public function test_cron_daemon_is_running(): void
    {
        $active = shell_exec("systemctl is-active cron 2>/dev/null || service cron status 2>/dev/null || (pgrep -x cron >/dev/null && echo 'active')");

        $this->assertStringContainsString('active', (string) $active);
    }
}
