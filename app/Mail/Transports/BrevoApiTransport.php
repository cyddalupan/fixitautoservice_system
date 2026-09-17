<?php

namespace App\Mail\Transports;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\RawMessage;

/**
 * Brevo REST API mail transport.
 *
 * Mirrors the logic of the public website contact form
 * (/var/www/fixit-static/contact-send.php): build a JSON payload and POST it
 * to https://api.brevo.com/v3/smtp/email with the `api-key` header. This is
 * the proven-working path on this server (SMTP relay was silently failing).
 */
class BrevoApiTransport implements TransportInterface
{
    public function __construct(
        private string $apiKey,
        private string $senderEmail,
        private string $senderName,
    ) {
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        $email = MessageConverter::toEmail($message);

        $to = $this->addressesToPayload($email->getTo());
        if (empty($to) && $envelope) {
            $to = array_map(
                fn (Address $a) => ['email' => $a->getAddress(), 'name' => $a->getName()],
                $envelope->getRecipients()
            );
        }

        $payload = [
            'sender' => [
                'email' => $this->senderEmail,
                'name' => $this->senderName,
            ],
            'to' => $to,
            'subject' => (string) $email->getSubject(),
        ];

        if ($email->getHtmlBody()) {
            $payload['htmlContent'] = $email->getHtmlBody();
        }
        if ($email->getTextBody()) {
            $payload['textContent'] = $email->getTextBody();
        }
        if (empty($payload['htmlContent']) && isset($payload['textContent'])) {
            $payload['htmlContent'] = nl2br(e($payload['textContent']));
        }

        if ($cc = $email->getCc()) {
            $payload['cc'] = $this->addressesToPayload($cc);
        }
        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = $this->addressesToPayload($bcc);
        }
        if ($replyTo = $email->getReplyTo()) {
            $payload['replyTo'] = $this->addressesToPayload($replyTo);
        }

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['api-key: ' . $this->apiKey, 'Content-Type: application/json'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201 && $httpCode !== 200) {
            throw new \RuntimeException(
                'Brevo API error: HTTP ' . $httpCode . ' - ' . substr((string) $response, 0, 500)
            );
        }

        return new SentMessage($message, $envelope ?? Envelope::create($message));
    }

    public function __toString(): string
    {
        return 'brevo-api';
    }

    private function addressesToPayload(array $addresses): array
    {
        return array_map(function ($a) {
            $email = $a instanceof Address ? $a->getAddress() : (string) $a;
            $name = $a instanceof Address ? ($a->getName() ?? '') : '';
            // Brevo requires a non-empty name in "to" (contact form always sends one).
            if ($name === '') {
                $name = ucfirst(strstr($email, '@', true) ?: $email);
            }
            return ['email' => $email, 'name' => $name];
        }, $addresses);
    }
}
