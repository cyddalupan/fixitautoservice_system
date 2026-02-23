<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_number',
        'invoice_type',
        'customer_id',
        'vehicle_id',
        'work_order_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'status',
        'payment_status',
        'notes',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    /**
     * Get the customer that owns the invoice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the vehicle associated with the invoice.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the work order associated with the invoice.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the user who created the invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the invoice items for the invoice.
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
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include invoices with a specific payment status.
     */
    public function scopeByPaymentStatus($query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Scope a query to only include invoices for a specific customer.
     */
    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope a query to only include overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled');
    }

    /**
     * Check if the invoice is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->payment_status !== 'paid' && 
               $this->status !== 'cancelled';
    }

    /**
     * Check if the invoice is fully paid.
     */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->payment_status === 'paid' || $this->balance_due <= 0;
    }

    /**
     * Get the formatted invoice number.
     */
    public function getFormattedInvoiceNumberAttribute(): string
    {
        return 'INV-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
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
     * Add a payment to the invoice.
     */
    public function addPayment(float $amount, int $paymentMethodId, string $notes = null): Payment
    {
        $payment = new Payment([
            'payment_number' => 'PAY-' . str_pad(Payment::count() + 1, 6, '0', STR_PAD_LEFT),
            'customer_id' => $this->customer_id,
            'payment_date' => now(),
            'amount' => $amount,
            'payment_method_id' => $paymentMethodId,
            'payment_method_name' => PaymentMethod::find($paymentMethodId)->name,
            'status' => 'completed',
            'notes' => $notes,
            'received_by' => auth()->id(),
        ]);

        $this->payments()->save($payment);
        
        // Update invoice payment status
        $this->amount_paid += $amount;
        $this->balance_due = max(0, $this->total_amount - $this->amount_paid);
        
        if ($this->balance_due <= 0) {
            $this->payment_status = 'paid';
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = 'partial';
        }
        
        $this->save();

        return $payment;
    }

    /**
     * Add an item to the invoice.
     */
    public function addItem(array $itemData): InvoiceItem
    {
        $item = new InvoiceItem($itemData);
        $this->items()->save($item);
        
        // Recalculate invoice totals
        $this->recalculateTotals();
        
        return $item;
    }

    /**
     * Recalculate invoice totals based on items.
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum('total_amount');
        
        $this->subtotal = $subtotal;
        $this->total_amount = $subtotal + $this->tax_amount - $this->discount_amount;
        $this->balance_due = max(0, $this->total_amount - $this->amount_paid);
        
        $this->save();
    }
}
