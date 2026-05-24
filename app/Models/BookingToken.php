<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BookingToken extends Model
{
    protected $table = 'booking_tokens';

    protected $fillable = [
        'customer_id',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Generate a secure, unique magic link token for a customer.
     */
    public static function generateForCustomer(int $customerId, int $hoursValid = 48): self
    {
        return static::create([
            'customer_id' => $customerId,
            'token' => Str::random(64),
            'expires_at' => now()->addHours($hoursValid),
        ]);
    }

    /**
     * Find a valid (unused, non-expired) token.
     */
    public static function findValid(string $token): ?self
    {
        return static::where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Mark token as used.
     */
    public function markAsUsed(): bool
    {
        return $this->update(['used_at' => now()]);
    }
}
