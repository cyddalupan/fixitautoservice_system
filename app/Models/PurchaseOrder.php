<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'created_by',
        'approved_by',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'shipping_method',
        'shipping_cost',
        'tax_rate',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'subtotal',
        'total',
        'payment_terms',
        'payment_status',
        'amount_paid',
        'payment_date',
        'notes',
        'internal_notes',
        'tracking_number',
        'carrier',
        'is_rush',
        'is_backorder',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'shipping_cost' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
        'is_rush' => 'boolean',
        'is_backorder' => 'boolean',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(InventorySupplier::class, 'supplier_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopePartiallyReceived($query)
    {
        return $query->where('status', 'partially_received');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_delivery_date', '<', now())
                    ->whereIn('status', ['ordered', 'partially_received']);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Methods
    public function calculateTotals()
    {
        $subtotal = $this->items->sum('total_cost');
        $discountAmount = $subtotal * ($this->discount_percentage / 100);
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * ($this->tax_rate / 100);
        
        $this->subtotal = $subtotal;
        $this->discount_amount = $discountAmount;
        $this->tax_amount = $taxAmount;
        $this->total = $subtotal - $discountAmount + $taxAmount + $this->shipping_cost;
        
        $this->save();
    }

    public function getProgressPercentageAttribute()
    {
        $totalItems = $this->items->count();
        if ($totalItems === 0) return 0;
        
        $receivedItems = $this->items->where('status', 'received')->count();
        return round(($receivedItems / $totalItems) * 100, 1);
    }

    public function getIsOverdueAttribute()
    {
        return $this->expected_delivery_date && 
               $this->expected_delivery_date < now() && 
               in_array($this->status, ['ordered', 'partially_received']);
    }

    public function getDaysOverdueAttribute()
    {
        if ($this->is_overdue) {
            return now()->diffInDays($this->expected_delivery_date);
        }
        return 0;
    }

    public function getBalanceDueAttribute()
    {
        return $this->total - $this->amount_paid;
    }

    public function approve($userId)
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->save();
    }

    public function markAsOrdered()
    {
        $this->status = 'ordered';
        $this->save();
    }

    public function receiveItem($itemId, $quantity)
    {
        $item = $this->items()->find($itemId);
        
        if ($item) {
            $item->quantity_received += $quantity;
            
            if ($item->quantity_received >= $item->quantity_ordered) {
                $item->status = 'received';
                $item->actual_delivery_date = now();
            } else {
                $item->status = 'partially_received';
            }
            
            $item->save();
            
            // Update inventory
            $inventory = $item->inventory;
            $inventory->updateStock($quantity, 'purchase');
            
            // Update PO status
            $this->updateStatus();
        }
    }

    public function updateStatus()
    {
        $totalItems = $this->items->count();
        $receivedItems = $this->items->where('status', 'received')->count();
        $partiallyReceivedItems = $this->items->where('status', 'partially_received')->count();
        
        if ($receivedItems === $totalItems) {
            $this->status = 'received';
            $this->actual_delivery_date = now();
        } elseif ($receivedItems > 0 || $partiallyReceivedItems > 0) {
            $this->status = 'partially_received';
        } elseif ($this->status === 'approved') {
            $this->status = 'ordered';
        }
        
        $this->save();
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'pending_approval' => 'warning',
            'approved' => 'info',
            'ordered' => 'primary',
            'partially_received' => 'warning',
            'received' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getPaymentStatusColorAttribute()
    {
        return match($this->payment_status) {
            'unpaid' => 'danger',
            'partial' => 'warning',
            'paid' => 'success',
            default => 'secondary'
        };
    }
}