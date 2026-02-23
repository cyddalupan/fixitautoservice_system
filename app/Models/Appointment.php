<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'assigned_technician_id',
        'service_advisor_id',
        'appointment_number',
        'appointment_date',
        'appointment_time',
        'appointment_type',
        'appointment_status',
        'priority',
        'service_request',
        'service_types',
        'estimated_duration',
        'estimated_cost',
        'bay_number',
        'bay_status',
        'sms_reminder_sent',
        'email_reminder_sent',
        'reminder_sent_at',
        'confirmation_sent',
        'confirmation_sent_at',
        'follow_up_sent',
        'follow_up_sent_at',
        'is_waitlist',
        'waitlist_position',
        'waitlist_converted_at',
        'no_show_count',
        'last_no_show_at',
        'requires_deposit',
        'deposit_amount',
        'deposit_status',
        'customer_notes',
        'preferred_communication',
        'preferred_contact_time',
        'required_skills',
        'technician_preferences',
        'scheduled_at',
        'confirmed_at',
        'checked_in_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'booking_source',
        'booking_ip',
        'booking_referrer',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'appointment_date' => 'date',
        'service_types' => 'array',
        'estimated_duration' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'preferred_communication' => 'array',
        'required_skills' => 'array',
        'technician_preferences' => 'array',
        'scheduled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'confirmation_sent_at' => 'datetime',
        'follow_up_sent_at' => 'datetime',
        'last_no_show_at' => 'datetime',
        'waitlist_converted_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the appointment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the vehicle for the appointment.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the assigned technician.
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    /**
     * Get the service advisor.
     */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_advisor_id');
    }

    /**
     * Get the work order associated with the appointment.
     */
    public function workOrder(): HasOne
    {
        return $this->hasOne(ServiceRecord::class, 'appointment_id');
    }

    /**
     * Scope a query to only include scheduled appointments.
     */
    public function scopeScheduled($query)
    {
        return $query->where('appointment_status', 'scheduled');
    }

    /**
     * Scope a query to only include confirmed appointments.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('appointment_status', 'confirmed');
    }

    /**
     * Scope a query to only include today's appointments.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', Carbon::today());
    }

    /**
     * Scope a query to only include upcoming appointments.
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereDate('appointment_date', '>=', Carbon::today())
                     ->whereDate('appointment_date', '<=', Carbon::today()->addDays($days));
    }

    /**
     * Scope a query to only include waitlist appointments.
     */
    public function scopeWaitlist($query)
    {
        return $query->where('is_waitlist', true);
    }

    /**
     * Scope a query to only include appointments by technician.
     */
    public function scopeByTechnician($query, $technicianId)
    {
        return $query->where('assigned_technician_id', $technicianId);
    }

    /**
     * Scope a query to only include appointments by bay.
     */
    public function scopeByBay($query, $bayNumber)
    {
        return $query->where('bay_number', $bayNumber);
    }

    /**
     * Check if appointment is overdue (past scheduled time).
     */
    public function isOverdue(): bool
    {
        $appointmentDateTime = Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->appointment_time);
        return $appointmentDateTime->isPast() && in_array($this->appointment_status, ['scheduled', 'confirmed']);
    }

    /**
     * Check if appointment is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->appointment_status === 'in_progress';
    }

    /**
     * Check if appointment requires reminder.
     */
    public function requiresReminder(): bool
    {
        $appointmentDateTime = Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->appointment_time);
        $reminderTime = $appointmentDateTime->subHours(24);
        
        return !$this->sms_reminder_sent && !$this->email_reminder_sent && 
               Carbon::now()->gte($reminderTime) && 
               in_array($this->appointment_status, ['scheduled', 'confirmed']);
    }

    /**
     * Get the full appointment datetime.
     */
    public function getAppointmentDateTimeAttribute(): Carbon
    {
        return Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->appointment_time);
    }

    /**
     * Get the appointment duration in minutes.
     */
    public function getDurationInMinutesAttribute(): ?int
    {
        if (!$this->estimated_duration) {
            return null;
        }
        return (int) ($this->estimated_duration * 60);
    }

    /**
     * Get the appointment status with color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->appointment_status) {
            'scheduled' => 'info',
            'confirmed' => 'primary',
            'checked_in' => 'warning',
            'in_progress' => 'success',
            'completed' => 'success',
            'cancelled' => 'danger',
            'no_show' => 'dark',
            'rescheduled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get the priority with color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'success',
            'normal' => 'info',
            'high' => 'warning',
            'emergency' => 'danger',
            default => 'info',
        };
    }

    /**
     * Generate a unique appointment number.
     */
    public static function generateAppointmentNumber(): string
    {
        $prefix = 'APT';
        $date = date('ymd');
        $lastAppointment = self::where('appointment_number', 'like', $prefix . $date . '%')
            ->orderBy('appointment_number', 'desc')
            ->first();
        
        if ($lastAppointment) {
            $lastNumber = (int) substr($lastAppointment->appointment_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        
        return $prefix . $date . $nextNumber;
    }
}