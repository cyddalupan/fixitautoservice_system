<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaborRate;
use App\Models\PartsMarkup;
use App\Models\InventoryCategory;
use App\Models\InventorySupplier;

class PricingController extends Controller
{
    /**
     * Display pricing dashboard
     */
    public function index()
    {
        $laborRates = LaborRate::active()->orderBy('sort_order')->get();
        $partsMarkups = PartsMarkup::active()->orderBy('priority')->get();
        $categories = InventoryCategory::all();
        $suppliers = InventorySupplier::all();
        
        return view('pricing.index', compact('laborRates', 'partsMarkups', 'categories', 'suppliers'));
    }

    /**
     * Display labor rates management
     */
    public function laborRates()
    {
        $laborRates = LaborRate::orderBy('sort_order')->get();
        return view('pricing.labor-rates', compact('laborRates'));
    }

    /**
     * Store a new labor rate
     */
    public function storeLaborRate(Request $request)
    {
        $validated = $request->validate([
            'rate_name' => 'required|string|max:255',
            'rate_code' => 'required|string|max:50|unique:labor_rates,rate_code',
            'description' => 'nullable|string',
            'hourly_rate' => 'required|numeric|min:0',
            'minimum_charge' => 'nullable|numeric|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
            'applicable_categories' => 'nullable|array',
            'applicable_technicians' => 'nullable|array',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        // If setting as default, unset other defaults
        if ($request->is_default) {
            LaborRate::where('is_default', true)->update(['is_default' => false]);
        }

        LaborRate::create($validated);

        return redirect()->route('pricing.labor-rates')
            ->with('success', 'Labor rate created successfully.');
    }

    /**
     * Update a labor rate
     */
    public function updateLaborRate(Request $request, LaborRate $laborRate)
    {
        $validated = $request->validate([
            'rate_name' => 'required|string|max:255',
            'rate_code' => 'required|string|max:50|unique:labor_rates,rate_code,' . $laborRate->id,
            'description' => 'nullable|string',
            'hourly_rate' => 'required|numeric|min:0',
            'minimum_charge' => 'nullable|numeric|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
            'applicable_categories' => 'nullable|array',
            'applicable_technicians' => 'nullable|array',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        // If setting as default, unset other defaults
        if ($request->is_default && !$laborRate->is_default) {
            LaborRate::where('is_default', true)->update(['is_default' => false]);
        }

        $laborRate->update($validated);

        return redirect()->route('pricing.labor-rates')
            ->with('success', 'Labor rate updated successfully.');
    }

    /**
     * Delete a labor rate
     */
    public function destroyLaborRate(LaborRate $laborRate)
    {
        $laborRate->delete();

        return redirect()->route('pricing.labor-rates')
            ->with('success', 'Labor rate deleted successfully.');
    }

    /**
     * Display parts markup management
     */
    public function partsMarkup()
    {
        $partsMarkups = PartsMarkup::with(['category', 'supplier'])->orderBy('priority')->get();
        $categories = InventoryCategory::all();
        $suppliers = InventorySupplier::all();
        
        return view('pricing.parts-markup', compact('partsMarkups', 'categories', 'suppliers'));
    }

    /**
     * Store a new parts markup rule
     */
    public function storePartsMarkup(Request $request)
    {
        $validated = $request->validate([
            'markup_name' => 'required|string|max:255',
            'markup_type' => 'required|in:percentage,fixed,tiered',
            'markup_value' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'supplier_id' => 'nullable|exists:inventory_suppliers,id',
            'minimum_cost' => 'nullable|numeric|min:0',
            'maximum_cost' => 'nullable|numeric|min:0|gt:minimum_cost',
            'minimum_retail' => 'nullable|numeric|min:0',
            'maximum_retail' => 'nullable|numeric|min:0|gt:minimum_retail',
            'apply_to_all_categories' => 'boolean',
            'apply_to_all_suppliers' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        PartsMarkup::create($validated);

        return redirect()->route('pricing.parts-markup')
            ->with('success', 'Parts markup rule created successfully.');
    }

    /**
     * Update a parts markup rule
     */
    public function updatePartsMarkup(Request $request, PartsMarkup $partsMarkup)
    {
        $validated = $request->validate([
            'markup_name' => 'required|string|max:255',
            'markup_type' => 'required|in:percentage,fixed,tiered',
            'markup_value' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'supplier_id' => 'nullable|exists:inventory_suppliers,id',
            'minimum_cost' => 'nullable|numeric|min:0',
            'maximum_cost' => 'nullable|numeric|min:0|gt:minimum_cost',
            'minimum_retail' => 'nullable|numeric|min:0',
            'maximum_retail' => 'nullable|numeric|min:0|gt:minimum_retail',
            'apply_to_all_categories' => 'boolean',
            'apply_to_all_suppliers' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'nullable|integer',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
        ]);

        $partsMarkup->update($validated);

        return redirect()->route('pricing.parts-markup')
            ->with('success', 'Parts markup rule updated successfully.');
    }

    /**
     * Delete a parts markup rule
     */
    public function destroyPartsMarkup(PartsMarkup $partsMarkup)
    {
        $partsMarkup->delete();

        return redirect()->route('pricing.parts-markup')
            ->with('success', 'Parts markup rule deleted successfully.');
    }

    /**
     * Calculate retail price for a part
     */
    public function calculateRetailPrice(Request $request)
    {
        $validated = $request->validate([
            'cost_price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:inventory_categories,id',
            'supplier_id' => 'nullable|exists:inventory_suppliers,id',
        ]);

        $cost = $validated['cost_price'];
        $categoryId = $validated['category_id'] ?? null;
        $supplierId = $validated['supplier_id'] ?? null;

        // Get applicable markup rules
        $markupRules = PartsMarkup::active()
            ->effectiveOn()
            ->orderBy('priority', 'desc')
            ->get();

        $applicableRules = [];
        $retailPrice = $cost; // Default to cost if no rules apply

        foreach ($markupRules as $rule) {
            // Check if rule applies to this category/supplier
            $applies = false;
            
            if ($rule->apply_to_all_categories && $rule->apply_to_all_suppliers) {
                $applies = true;
            } elseif ($rule->apply_to_all_categories && $rule->supplier_id == $supplierId) {
                $applies = true;
            } elseif ($rule->apply_to_all_suppliers && $rule->category_id == $categoryId) {
                $applies = true;
            } elseif ($rule->category_id == $categoryId && $rule->supplier_id == $supplierId) {
                $applies = true;
            } elseif ($rule->category_id == $categoryId && !$rule->supplier_id) {
                $applies = true;
            } elseif ($rule->supplier_id == $supplierId && !$rule->category_id) {
                $applies = true;
            }

            if ($applies) {
                $calculatedPrice = $rule->calculateRetailPrice($cost);
                if ($calculatedPrice !== null) {
                    $applicableRules[] = [
                        'rule' => $rule,
                        'calculated_price' => $calculatedPrice,
                    ];
                    $retailPrice = $calculatedPrice; // Use the last applicable rule
                }
            }
        }

        return response()->json([
            'cost_price' => $cost,
            'retail_price' => $retailPrice,
            'markup_amount' => $retailPrice - $cost,
            'markup_percentage' => $cost > 0 ? (($retailPrice - $cost) / $cost) * 100 : 0,
            'applicable_rules' => $applicableRules,
        ]);
    }

    /**
     * Calculate labor cost
     */
    public function calculateLaborCost(Request $request)
    {
        $validated = $request->validate([
            'hours' => 'required|numeric|min:0',
            'rate_code' => 'nullable|exists:labor_rates,rate_code',
        ]);

        $hours = $validated['hours'];
        $rateCode = $validated['rate_code'] ?? null;

        if ($rateCode) {
            $laborRate = LaborRate::where('rate_code', $rateCode)->active()->first();
        } else {
            $laborRate = LaborRate::default()->active()->first();
        }

        if (!$laborRate) {
            return response()->json([
                'error' => 'No applicable labor rate found',
            ], 404);
        }

        $cost = $laborRate->calculateCost($hours);

        return response()->json([
            'hours' => $hours,
            'rate_name' => $laborRate->rate_name,
            'hourly_rate' => $laborRate->hourly_rate,
            'minimum_charge' => $laborRate->minimum_charge,
            'total_cost' => $cost,
            'labor_rate' => $laborRate,
        ]);
    }
}
