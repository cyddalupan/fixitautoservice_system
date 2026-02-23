<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'inventory_id',
        'part_number',
        'description',
        'quantity_ordered',
        'quantity_received',
        'quantity_backordered',
        'unit_cost',
        'total_cost',
        'discount_percentage',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'status',
        'expected_delivery_date',
        'actual_delivery_date',
        'notes',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopePartiallyReceived($query)
    {
        return $query->where('status', 'partially_received');
    }

    public function scopeBackordered($query)
    {
        return $query->where('status', 'backordered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_delivery_date', '<', now())
                    ->whereIn('status', ['pending', 'partially_received', 'backordered']);
    }

    // Methods
    public function calculateTotals()
    {
        $this->total_cost = $this->quantity_ordered * $this->unit_cost;
        $this->discount_amount = $this->total_cost * ($this->discount_percentage / 100);
        $this->tax_amount = ($this->total_cost - $this->discount_amount) * ($this->tax_rate / 100);
        
        $this->save();
    }

    public function getQuantityRemainingAttribute()
    {
        return $this->quantity_ordered - $this->quantity_received;
    }

    public function getIsCompleteAttribute()
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    public function getIsOverdueAttribute()
    {
        return $this->expected_delivery_date && 
               $this->expected_delivery_date < now() && 
               in_array($this->status, ['pending', 'partially_received', 'backordered']);
    }

    public function getDaysOverdueAttribute()
    {
        if ($this->is_overdue) {
            return now()->diffInDays($this->expected_delivery_date);
        }
        return 0;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'partially_received' => 'info',
            'received' => 'success',
            'backordered' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    public function receive($quantity)
    {
        $this->quantity_received += $quantity;
        
        if ($this->quantity_received >= $this->quantity_ordered) {
            $this->status = 'received';
            $this->actual_delivery_date = now();
        } else {
            $this->status = 'partially_received';
        }
        
        $this->save();
        
        // Update inventory
        if ($this->inventory) {
            $this->inventory->updateStock($quantity, 'purchase');
        }
    }

    public function markAsBackordered($quantity = null)
    {
        $this->status = 'backordered';
        $this->quantity_backordered = $quantity ?? $this->quantity_ordered - $this->quantity_received;
        $this->save();
    }
}