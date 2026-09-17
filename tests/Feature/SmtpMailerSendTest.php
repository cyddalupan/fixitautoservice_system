<?php

namespace Tests\Feature;

use Tests\TestCase;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;

class SmtpMailerSendTest extends TestCase
{
    /**
     * Actually transmits a test message through the REAL Brevo SMTP relay,
     * using the live .env SMTP credentials (host/port/user/pass). This proves
     * emails really send end-to-end, not just that the port is reachable.
     * The message is delivered to the account owner's inbox (cydmdalupan@gmail.com)
     * with an explicit subject so it is recognizable as an automated TDD check.
     *
     * NOTE: Uses the SMTP transport directly (bypassing phpunit's MAIL_MAILER=array
     * override) because the point is to verify the deployed network path.
     */
    public function test_real_email_sends_through_brevo_smtp(): void
    {
        $envFile = base_path('.env');
        $contents = file_get_contents($envFile);

        $host = smtp_send_env('MAIL_HOST', $contents) ?: 'smtp-relay.brevo.com';
        $port = (int) (smtp_send_env('MAIL_PORT', $contents) ?: 587);
        $user = smtp_send_env('MAIL_USERNAME', $contents) ?: '';
        $pass = smtp_send_env('MAIL_PASSWORD', $contents) ?: '';

        $this->assertStringNotContainsString('null', $user, 'SMTP username must be a real credential');
        $this->assertNotEquals('', $user);

        $transport = new EsmtpTransport($host, $port, false);
        $transport->setUsername($user);
        $transport->setPassword($pass);

        $email = (new Email())
            ->from('noreply@app.fixitautoservices.com')
            ->to('cydmdalupan@gmail.com')
            ->subject('[FixIt TDD] SMTP send verification ' . date('c'))
            ->text('This is an automated verification that the FixIt Laravel app can send email through the configured Brevo SMTP relay. Sent at ' . date('c'));

        $sent = null;
        try {
            // send() returns a Symfony\Component\Mailer\SentMessage on success.
            $sent = $transport->send($email);
        } catch (\Throwable $e) {
            $this->fail('SMTP send failed: ' . get_class($e) . ': ' . $e->getMessage());
        }

        $this->assertNotNull($sent, 'Expected a SentMessage to be returned from a successful SMTP send.');
        $this->assertInstanceOf(\Symfony\Component\Mailer\SentMessage::class, $sent);
    }
}

function smtp_send_env(string $key, string $contents): ?string
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
