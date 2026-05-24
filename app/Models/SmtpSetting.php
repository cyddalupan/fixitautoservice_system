<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SmtpSetting extends Model
{
    protected $fillable = [
        'mail_host',
        'mail_port',
        'mail_from_address',
        'mail_from_name',
        'mail_username',
        'mail_password_encrypted',
        'mail_encryption',
        'is_active',
    ];

    protected $casts = [
        'mail_port' => 'integer',
        'is_active' => 'boolean',
    ];

    // Attributes that should not be exposed
    protected $hidden = [
        'mail_password_encrypted',
    ];

    /**
     * Encrypt and set the password.
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['mail_password_encrypted'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt the password for use (only when sending email).
     */
    public function getDecryptedPassword(): ?string
    {
        if (!$this->mail_password_encrypted) {
            return null;
        }
        try {
            return Crypt::decryptString($this->mail_password_encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if a password has been set.
     */
    public function hasPassword(): bool
    {
        return !is_null($this->mail_password_encrypted);
    }

    /**
     * Apply SMTP config to Laravel mail system.
     */
    public function applyMailConfig(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'host' => $this->mail_host,
                'port' => $this->mail_port,
                'encryption' => $this->mail_encryption,
                'username' => $this->mail_username,
                'password' => $this->getDecryptedPassword(),
                'timeout' => 30,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
            ],
            'mail.from' => [
                'address' => $this->mail_from_address,
                'name' => $this->mail_from_name,
            ],
        ]);
    }
}
