<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'item_type',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'total_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the service record associated with the item.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceRecord::class);
    }

    /**
     * Get the inventory item associated with the item.
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'part_id');
    }

    /**
     * Get the formatted unit price.
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return '₱' . number_format($this->unit_price, 2);
    }

    /**
     * Get the formatted total amount.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    /**
     * Get the icon class for the item type.
     */
    public function getIconClassAttribute(): string
    {
        return match($this->item_type) {
            'service' => 'fas fa-tools',
            'parts' => 'fas fa-cog',
            'labor' => 'fas fa-user-cog',
            'fee' => 'fas fa-file-invoice-dollar',
            default => 'fas fa-box',
        };
    }

    /**
     * Get the color class for the item type.
     */
    public function getColorClassAttribute(): string
    {
        return match($this->item_type) {
            'service' => 'primary',
            'parts' => 'success',
            'labor' => 'warning',
            'fee' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Calculate the total amount based on quantity and unit price.
     */
    public function calculateTotal(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Update the total amount based on current quantity and unit price.
     */
    public function updateTotal(): void
    {
        $this->total_amount = $this->calculateTotal();
        $this->save();
    }
}
