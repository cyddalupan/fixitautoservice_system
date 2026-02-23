<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartsRequestItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'parts_request_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parts_request_id',
        'part_number',
        'description',
        'quantity',
        'unit_price',
        'inventory_id',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * Get the parts request for this item.
     */
    public function partsRequest()
    {
        return $this->belongsTo(PartsRequest::class, 'parts_request_id');
    }

    /**
     * Get the inventory item for this part.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    /**
     * Calculate total price for this item.
     */
    public function calculateTotalPrice(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Update total price.
     */
    public function updateTotalPrice(): bool
    {
        return $this->update([
            'total_price' => $this->calculateTotalPrice(),
        ]);
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'requested' => 'warning',
            'approved' => 'primary',
            'ordered' => 'info',
            'received' => 'success',
            'installed' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get the formatted status.
     */
    public function getFormattedStatusAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Check if this item is in stock.
     */
    public function isInStock(): bool
    {
        if (!$this->inventory_id) {
            return false;
        }

        $inventory = Inventory::find($this->inventory_id);
        return $inventory && $inventory->quantity_on_hand >= $this->quantity;
    }

    /**
     * Get stock availability.
     */
    public function getStockAvailabilityAttribute(): string
    {
        if (!$this->inventory_id) {
            return 'Not in inventory';
        }

        $inventory = Inventory::find($this->inventory_id);
        if (!$inventory) {
            return 'Inventory not found';
        }

        if ($inventory->quantity_on_hand >= $this->quantity) {
            return 'In stock';
        } elseif ($inventory->quantity_on_hand > 0) {
            return 'Partial stock (' . $inventory->quantity_on_hand . ' available)';
        } else {
            return 'Out of stock';
        }
    }
}