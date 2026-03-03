<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'work_order_id',
        'estimate_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'notes',
        'terms',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the invoice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the work order that owns the invoice.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the estimate that owns the invoice.
     */
    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    /**
     * Get the appointment that owns the invoice.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the items for the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the payments for the invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope a query to only include invoices with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include invoices that are overdue.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('balance_due', '>', 0)
            ->where('status', '!=', 'paid');
    }

    /**
     * Scope a query to only include invoices that are due soon.
     */
    public function scopeDueSoon($query, $days = 7)
    {
        return $query->whereBetween('due_date', [now(), now()->addDays($days)])
            ->where('balance_due', '>', 0)
            ->where('status', '!=', 'paid');
    }

    /**
     * Check if the invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date < now() && $this->balance_due > 0 && $this->status !== 'paid';
    }

    /**
     * Check if the invoice is fully paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->balance_due <= 0;
    }

    /**
     * Get the formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    /**
     * Get the formatted balance due.
     */
    public function getFormattedBalanceDueAttribute(): string
    {
        return '₱' . number_format($this->balance_due, 2);
    }

    /**
     * Get the formatted amount paid.
     */
    public function getFormattedAmountPaidAttribute(): string
    {
        return '₱' . number_format($this->amount_paid, 2);
    }

    /**
     * Get the days until due.
     */
    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            'draft' => 'bg-secondary',
            'sent' => 'bg-info',
            'partial' => 'bg-warning',
            'paid' => 'bg-success',
            'overdue' => 'bg-danger',
            'cancelled' => 'bg-dark',
        ];

        return $classes[$this->status] ?? 'bg-secondary';
    }

    /**
     * Get the payment percentage.
     */
    public function getPaymentPercentageAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return ($this->amount_paid / $this->total_amount) * 100;
    }
}