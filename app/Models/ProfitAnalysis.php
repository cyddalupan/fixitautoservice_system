<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfitAnalysis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_id',
        'invoice_id',
        'analysis_date',
        'total_revenue',
        'total_cost',
        'gross_profit',
        'gross_profit_margin',
        'labor_revenue',
        'labor_cost',
        'labor_profit',
        'labor_profit_margin',
        'parts_revenue',
        'parts_cost',
        'parts_profit',
        'parts_profit_margin',
        'other_revenue',
        'other_cost',
        'other_profit',
        'overhead_allocation',
        'net_profit',
        'net_profit_margin',
        'cost_breakdown',
        'revenue_breakdown',
        'notes',
        'is_finalized',
        'finalized_at',
    ];

    protected $casts = [
        'analysis_date' => 'date',
        'total_revenue' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'gross_profit_margin' => 'decimal:2',
        'labor_revenue' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'labor_profit' => 'decimal:2',
        'labor_profit_margin' => 'decimal:2',
        'parts_revenue' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'parts_profit' => 'decimal:2',
        'parts_profit_margin' => 'decimal:2',
        'other_revenue' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'other_profit' => 'decimal:2',
        'overhead_allocation' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'net_profit_margin' => 'decimal:2',
        'cost_breakdown' => 'array',
        'revenue_breakdown' => 'array',
        'is_finalized' => 'boolean',
        'finalized_at' => 'datetime',
    ];

    /**
     * Relationship with work order
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Relationship with invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get finalized analyses
     */
    public function scopeFinalized($query)
    {
        return $query->where('is_finalized', true);
    }

    /**
     * Get analyses for a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('analysis_date', [$startDate, $endDate]);
    }

    /**
     * Calculate profit margin percentage
     */
    public static function calculateMargin($revenue, $cost)
    {
        if ($revenue == 0) {
            return 0;
        }
        
        return (($revenue - $cost) / $revenue) * 100;
    }

    /**
     * Generate analysis from work order
     */
    public static function generateFromWorkOrder(WorkOrder $workOrder)
    {
        $analysis = new self();
        $analysis->work_order_id = $workOrder->id;
        $analysis->invoice_id = $workOrder->invoice_id ?? null;
        $analysis->analysis_date = now();
        
        // Calculate revenue and costs
        $analysis->labor_revenue = $workOrder->actual_labor_cost ?? $workOrder->estimated_labor_cost ?? 0;
        $analysis->parts_revenue = $workOrder->actual_parts_cost ?? $workOrder->estimated_parts_cost ?? 0;
        $analysis->other_revenue = 0; // Could be fees, shipping, etc.
        
        $analysis->total_revenue = $analysis->labor_revenue + $analysis->parts_revenue + $analysis->other_revenue;
        
        // For now, using revenue as cost (simplified)
        // In real system, would calculate actual costs from inventory and payroll
        $analysis->labor_cost = $analysis->labor_revenue * 0.6; // 40% labor profit margin
        $analysis->parts_cost = $analysis->parts_revenue * 0.7; // 30% parts profit margin
        $analysis->other_cost = 0;
        
        $analysis->total_cost = $analysis->labor_cost + $analysis->parts_cost + $analysis->other_cost;
        
        // Calculate profits
        $analysis->labor_profit = $analysis->labor_revenue - $analysis->labor_cost;
        $analysis->parts_profit = $analysis->parts_revenue - $analysis->parts_cost;
        $analysis->other_profit = $analysis->other_revenue - $analysis->other_cost;
        $analysis->gross_profit = $analysis->total_revenue - $analysis->total_cost;
        
        // Calculate margins
        $analysis->labor_profit_margin = self::calculateMargin($analysis->labor_revenue, $analysis->labor_cost);
        $analysis->parts_profit_margin = self::calculateMargin($analysis->parts_revenue, $analysis->parts_cost);
        $analysis->gross_profit_margin = self::calculateMargin($analysis->total_revenue, $analysis->total_cost);
        
        // Net profit (after overhead)
        $analysis->overhead_allocation = $analysis->total_revenue * 0.15; // 15% overhead
        $analysis->net_profit = $analysis->gross_profit - $analysis->overhead_allocation;
        $analysis->net_profit_margin = self::calculateMargin($analysis->total_revenue, $analysis->total_cost + $analysis->overhead_allocation);
        
        return $analysis;
    }
}
