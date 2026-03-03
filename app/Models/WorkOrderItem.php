<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'item_type',
        'description',
        'part_number',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'discount_percent',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'final_amount',
        'is_estimate',
        'is_warranty',
        'is_insurance',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'is_estimate' => 'boolean',
        'is_warranty' => 'boolean',
        'is_insurance' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Relationships
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Accessors
     */
    public function getItemTypeLabelAttribute(): string
    {
        return match($this->item_type) {
            'labor' => 'Labor',
            'part' => 'Part',
            'sublet' => 'Sublet',
            'fee' => 'Fee',
            'tax' => 'Tax',
            'discount' => 'Discount',
            default => ucfirst($this->item_type),
        };
    }

    public function getItemTypeColorAttribute(): string
    {
        return match($this->item_type) {
            'labor' => 'primary',
            'part' => 'info',
            'sublet' => 'warning',
            'fee' => 'secondary',
            'tax' => 'success',
            'discount' => 'danger',
            default => 'secondary',
        };
    }

    public function getFormattedUnitCostAttribute(): string
    {
        return '₱' . number_format($this->unit_cost, 2);
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return '₱' . number_format($this->total_cost, 2);
    }

    public function getFormattedFinalAmountAttribute(): string
    {
        return '₱' . number_format($this->final_amount, 2);
    }

    /**
     * Methods
     */
    public function calculateTotals(): void
    {
        $this->total_cost = $this->quantity * $this->unit_cost;
        $this->discount_amount = $this->total_cost * ($this->discount_percent / 100);
        $this->tax_amount = ($this->total_cost - $this->discount_amount) * ($this->tax_rate / 100);
        $this->final_amount = $this->total_cost - $this->discount_amount + $this->tax_amount;
    }

    public static function createFromTemplate(array $template, int $workOrderId, bool $isEstimate = true): self
    {
        $item = new self([
            'work_order_id' => $workOrderId,
            'item_type' => $template['item_type'] ?? 'part',
            'description' => $template['description'],
            'part_number' => $template['part_number'] ?? null,
            'quantity' => $template['quantity'] ?? 1,
            'unit' => $template['unit'] ?? 'each',
            'unit_cost' => $template['unit_cost'] ?? 0,
            'is_estimate' => $isEstimate,
            'is_warranty' => $template['is_warranty'] ?? false,
            'is_insurance' => $template['is_insurance'] ?? false,
            'notes' => $template['notes'] ?? null,
        ]);

        $item->calculateTotals();
        $item->save();

        return $item;
    }
}