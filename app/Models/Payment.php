<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'customer_id',
        'payment_date',
        'payment_method',
        'amount',
        'reference_number',
        'notes',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the invoice that owns the payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the customer that owns the payment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the appointment through the invoice.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'service_id', 'service_id');
    }

    /**
     * Get the estimate through the invoice.
     */
    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class, 'service_id', 'service_id');
    }

    /**
     * Get the work order through the invoice.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'service_id', 'service_id');
    }

    /**
     * Scope a query to only include payments with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include payments with a specific method.
     */
    public function scopePaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope a query to only include payments within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Get the formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₱' . number_format($this->amount, 2);
    }

    /**
     * Get the payment method badge class.
     */
    public function getPaymentMethodBadgeClassAttribute(): string
    {
        $classes = [
            'cash' => 'bg-success',
            'check' => 'bg-info',
            'credit_card' => 'bg-primary',
            'bank_transfer' => 'bg-warning',
            'gcash' => 'bg-success',
            'paymaya' => 'bg-primary',
        ];

        return $classes[$this->payment_method] ?? 'bg-secondary';
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            'pending' => 'bg-warning',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'refunded' => 'bg-info',
        ];

        return $classes[$this->status] ?? 'bg-secondary';
    }

    /**
     * Get the payment method display name.
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        $methods = [
            'cash' => 'Cash',
            'check' => 'Check',
            'credit_card' => 'Credit Card',
            'bank_transfer' => 'Bank Transfer',
            'gcash' => 'GCash',
            'paymaya' => 'PayMaya',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }
}