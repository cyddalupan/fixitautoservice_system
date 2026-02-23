<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_number',
        'invoice_id',
        'customer_id',
        'work_order_id',
        'payment_date',
        'amount',
        'payment_method_id',
        'payment_method_name',
        'status',
        'notes',
        'received_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
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
     * Get the work order associated with the payment.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the payment method associated with the payment.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the user who received the payment.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Scope a query to only include payments with a specific status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include payments for a specific customer.
     */
    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope a query to only include payments for a specific invoice.
     */
    public function scopeByInvoice($query, int $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    /**
     * Scope a query to only include payments within a date range.
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Get the formatted payment number.
     */
    public function getFormattedPaymentNumberAttribute(): string
    {
        return 'PAY-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₱' . number_format($this->amount, 2);
    }

    /**
     * Get the formatted payment date.
     */
    public function getFormattedPaymentDateAttribute(): string
    {
        return $this->payment_date->format('M d, Y');
    }

    /**
     * Get the icon class for the payment status.
     */
    public function getStatusIconClassAttribute(): string
    {
        return match($this->status) {
            'completed' => 'fas fa-check-circle text-success',
            'pending' => 'fas fa-clock text-warning',
            'failed' => 'fas fa-times-circle text-danger',
            'refunded' => 'fas fa-undo text-info',
            default => 'fas fa-question-circle text-secondary',
        };
    }

    /**
     * Get the badge class for the payment status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'completed' => 'badge bg-success',
            'pending' => 'badge bg-warning',
            'failed' => 'badge bg-danger',
            'refunded' => 'badge bg-info',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Check if the payment is completed.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the payment is pending.
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark the payment as completed.
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->save();
    }

    /**
     * Mark the payment as failed.
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->status = 'failed';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Failed: " . $reason;
        }
        $this->save();
    }

    /**
     * Refund the payment.
     */
    public function refund(string $reason = null): void
    {
        $this->status = 'refunded';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Refunded: " . $reason;
        }
        $this->save();
    }
}
