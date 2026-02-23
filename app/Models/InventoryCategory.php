<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'color',
        'icon',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(InventoryCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(InventoryCategory::class, 'parent_id');
    }

    public function inventoryItems()
    {
        return $this->hasMany(Inventory::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithChildren($query)
    {
        return $query->with('children');
    }

    // Methods
    public function getTotalInventoryValueAttribute()
    {
        return $this->inventoryItems->sum(function($item) {
            return $item->quantity * $item->cost_price;
        });
    }

    public function getTotalInventoryCountAttribute()
    {
        return $this->inventoryItems->count();
    }

    public function getLowStockCountAttribute()
    {
        return $this->inventoryItems->where('quantity', '<=', \DB::raw('reorder_point'))->count();
    }

    public function getOutOfStockCountAttribute()
    {
        return $this->inventoryItems->where('quantity', '<=', 0)->count();
    }

    public function getTreePathAttribute()
    {
        $path = [];
        $category = $this;
        
        while ($category) {
            $path[] = $category->name;
            $category = $category->parent;
        }
        
        return implode(' > ', array_reverse($path));
    }

    public function getAllChildrenIdsAttribute()
    {
        $ids = [$this->id];
        
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->allChildrenIds);
        }
        
        return $ids;
    }
}