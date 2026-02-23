<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory';

    protected $fillable = [
        'part_number',
        'name',
        'description',
        'category_id',
        'supplier_id',
        'manufacturer',
        'oem_number',
        'upc',
        'location',
        'bin',
        'quantity',
        'minimum_stock',
        'reorder_point',
        'cost_price',
        'retail_price',
        'wholesale_price',
        'core_price',
        'is_taxable',
        'tax_rate',
        'is_active',
        'status',
        'last_purchased',
        'last_sold',
        'total_sold',
        'total_sales',
        'total_cost',
        'profit_margin',
        'turnover_rate',
        'notes',
        'image_url',
        'barcode',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'core_price' => 'decimal:2',
        'is_taxable' => 'boolean',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'total_sales' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'last_purchased' => 'date',
        'last_sold' => 'date',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(InventorySupplier::class, 'supplier_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'inventory_id');
    }

    public function workOrderItems()
    {
        return $this->hasMany(WorkOrderItem::class, 'inventory_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where('quantity', '<=', \DB::raw('reorder_point'));
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    // Methods
    public function getStockStatusAttribute()
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->quantity <= $this->reorder_point) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    public function getStockStatusColorAttribute()
    {
        return match($this->stock_status) {
            'out_of_stock' => 'danger',
            'low_stock' => 'warning',
            'in_stock' => 'success',
            default => 'secondary'
        };
    }

    public function getProfitAttribute()
    {
        return $this->retail_price - $this->cost_price;
    }

    public function getMarkupPercentageAttribute()
    {
        if ($this->cost_price > 0) {
            return (($this->retail_price - $this->cost_price) / $this->cost_price) * 100;
        }
        return 0;
    }

    public function updateStock($quantity, $type = 'sale')
    {
        $oldQuantity = $this->quantity;
        
        if ($type === 'sale') {
            $this->quantity -= $quantity;
            $this->total_sold += $quantity;
            $this->total_sales += ($quantity * $this->retail_price);
            $this->total_cost += ($quantity * $this->cost_price);
            $this->last_sold = now();
        } elseif ($type === 'purchase') {
            $this->quantity += $quantity;
            $this->last_purchased = now();
        } elseif ($type === 'adjustment') {
            $this->quantity = $quantity;
        }

        // Update profit margin
        if ($this->total_sales > 0 && $this->total_cost > 0) {
            $this->profit_margin = (($this->total_sales - $this->total_cost) / $this->total_sales) * 100;
        }

        // Update turnover rate (simplified)
        $this->turnover_rate = $this->total_sold > 0 ? ($this->total_sold / max($oldQuantity, 1)) * 100 : 0;

        // Update status
        $this->status = $this->stock_status;

        $this->save();
    }

    public function needsReorder()
    {
        return $this->quantity <= $this->reorder_point;
    }

    public function getDaysOfSupplyAttribute()
    {
        if ($this->total_sold > 0) {
            $avgDailySales = $this->total_sold / 30; // Assuming 30 days for simplicity
            return $avgDailySales > 0 ? floor($this->quantity / $avgDailySales) : 0;
        }
        return 0;
    }
}