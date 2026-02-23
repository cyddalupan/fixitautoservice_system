<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PortalUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'email',
        'password',
        'verification_token',
        'email_verified_at',
        'is_active',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'verification_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the customer associated with the portal user.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the portal preferences for the user.
     */
    public function preferences()
    {
        return $this->hasOne(PortalPreference::class, 'portal_user_id');
    }

    /**
     * Get the portal documents for the user.
     */
    public function documents()
    {
        return $this->hasMany(PortalDocument::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the portal messages for the user.
     */
    public function messages()
    {
        return $this->hasMany(PortalMessage::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the service requests for the user.
     */
    public function serviceRequests()
    {
        return $this->hasMany(PortalServiceRequest::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the reviews for the user.
     */
    public function reviews()
    {
        return $this->hasMany(PortalReview::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the loyalty points for the user.
     */
    public function loyaltyPoints()
    {
        return $this->hasMany(PortalLoyaltyPoint::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the appointment views for the user.
     */
    public function appointmentViews()
    {
        return $this->hasMany(PortalAppointmentView::class);
    }

    /**
     * Check if the user has verified their email.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'verification_token' => null,
        ])->save();
    }

    /**
     * Get the user's current loyalty points balance.
     */
    public function getLoyaltyPointsBalanceAttribute(): int
    {
        $latest = $this->loyaltyPoints()->latest()->first();
        return $latest ? $latest->balance_after : 0;
    }

    /**
     * Get the user's unread messages count.
     */
    public function getUnreadMessagesCountAttribute(): int
    {
        return $this->messages()->where('is_read', false)->count();
    }

    /**
     * Get the user's pending service requests count.
     */
    public function getPendingServiceRequestsCountAttribute(): int
    {
        return $this->serviceRequests()->where('status', 'pending')->count();
    }

    /**
     * Get the user's shared documents count.
     */
    public function getSharedDocumentsCountAttribute(): int
    {
        return $this->documents()->where('is_shared', true)->count();
    }
}