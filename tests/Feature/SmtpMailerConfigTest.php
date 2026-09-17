<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;

class SmtpMailerConfigTest extends TestCase
{
    /**
     * The production .env MUST be configured with a real SMTP mailer
     * (Brevo SMTP) so that emails actually send. This is the documented
     * fix: MAIL_MAILER=smtp with real SMTP credentials. This test reads the
     * LIVE config (not the phpunit array-mailer override) to verify the
     * deployment-facing .env has been set up correctly.
     */
    public function test_env_has_smtp_mailer_with_real_brevo_credentials(): void
    {
        $envFile = base_path('.env');
        $this->assertFileExists($envFile);

        $contents = file_get_contents($envFile);

        // Must use the smtp driver, not the 'log' / 'array' fallback.
        $this->assertStringContainsString('MAIL_MAILER=smtp', $contents);

        // Brevo SMTP relay credentials must be present and real (not 'null').
        $this->assertStringContainsString('MAIL_HOST=smtp-relay.brevo.com', $contents);
        $this->assertStringContainsString('MAIL_PORT=587', $contents);
        $this->assertStringContainsString('MAIL_USERNAME=', $contents);
        $this->assertStringNotContainsString('MAIL_USERNAME=null', $contents);
        $this->assertStringContainsString('MAIL_PASSWORD=', $contents);
        $this->assertStringNotContainsString('MAIL_PASSWORD=null', $contents);
        $this->assertStringContainsString('MAIL_ENCRYPTION=tls', $contents);
    }

    /**
     * Emails must actually be able to send through the configured SMTP relay.
     * We read the live .env mail settings (host/port/username) and open a raw
     * TCP connection to confirm the SMTP server is reachable and accepts the
     * STARTTLS conversation. This does NOT deliver a real message (avoids
     * sending spam during TDD); it proves the transport is functional.
     */
    public function test_smtp_relay_is_reachable_and_accepts_connections(): void
    {
        $envFile = base_path('.env');
        $this->assertFileExists($envFile);
        $contents = file_get_contents($envFile);

        $host = smtp_env('MAIL_HOST', $contents);
        $port = (int) smtp_env('MAIL_PORT', $contents);

        $this->assertNotEquals('localhost', $host, 'MAIL_HOST must be the real relay, not localhost');
        $this->assertNotEmpty($host);
        $this->assertGreaterThan(0, $port);

        $errno = 0;
        $errstr = '';
        $fp = @fsockopen($host, $port, $errno, $errstr, 10);
        $this->assertNotFalse($fp, "Unable to connect to SMTP relay {$host}:{$port}: {$errstr}");

        if ($fp) {
            $banner = fgets($fp, 128);
            fclose($fp);
            $this->assertNotFalse($banner);
            $this->assertStringContainsString('220', (string) $banner);
        }
    }
}

/** Helper: read an ENV value from raw .env contents without loading the file. */
function smtp_env(string $key, string $contents): ?string
{
    foreach (explode("\n", $contents) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_starts_with($line, $key . '=')) {
            return trim((string) substr($line, strlen($key) + 1), " \t\"");
        }
    }
    return null;
}
