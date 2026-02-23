<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventorySupplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_suppliers';

    protected $fillable = [
        'name',
        'code',
        'contact_name',
        'contact_email',
        'contact_phone',
        'website',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'payment_terms',
        'credit_limit',
        'current_balance',
        'tax_id',
        'account_number',
        'shipping_method',
        'shipping_cost',
        'lead_time_days',
        'discount_percentage',
        'is_preferred',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_preferred' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function inventoryItems()
    {
        return $this->hasMany(Inventory::class, 'supplier_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
    }

    public function scopeWithBalance($query)
    {
        return $query->where('current_balance', '>', 0);
    }

    // Methods
    public function getTotalInventoryValueAttribute()
    {
        return $this->inventoryItems->sum(function($item) {
            return $item->quantity * $item->cost_price;
        });
    }

    public function getTotalPurchaseOrdersAttribute()
    {
        return $this->purchaseOrders->count();
    }

    public function getTotalPurchaseValueAttribute()
    {
        return $this->purchaseOrders->sum('total');
    }

    public function getAverageLeadTimeAttribute()
    {
        $orders = $this->purchaseOrders()->whereNotNull('actual_delivery_date')->get();
        
        if ($orders->count() > 0) {
            $totalDays = 0;
            foreach ($orders as $order) {
                $days = $order->order_date->diffInDays($order->actual_delivery_date);
                $totalDays += $days;
            }
            return round($totalDays / $orders->count(), 1);
        }
        
        return $this->lead_time_days;
    }

    public function getOnTimeDeliveryRateAttribute()
    {
        $orders = $this->purchaseOrders()->whereNotNull('actual_delivery_date')->get();
        
        if ($orders->count() > 0) {
            $onTime = 0;
            foreach ($orders as $order) {
                if ($order->actual_delivery_date <= $order->expected_delivery_date) {
                    $onTime++;
                }
            }
            return round(($onTime / $orders->count()) * 100, 1);
        }
        
        return 0;
    }

    public function updateBalance($amount, $type = 'increase')
    {
        if ($type === 'increase') {
            $this->current_balance += $amount;
        } elseif ($type === 'decrease') {
            $this->current_balance -= $amount;
        } elseif ($type === 'set') {
            $this->current_balance = $amount;
        }
        
        $this->save();
    }

    public function getCreditAvailableAttribute()
    {
        return max(0, $this->credit_limit - $this->current_balance);
    }

    public function getCreditUtilizationAttribute()
    {
        if ($this->credit_limit > 0) {
            return round(($this->current_balance / $this->credit_limit) * 100, 1);
        }
        return 0;
    }
}