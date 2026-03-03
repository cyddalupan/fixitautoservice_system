<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\InventorySupplier;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inventory::with(['category', 'supplier']);
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('part_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('oem_number', 'like', "%{$search}%")
                  ->orWhere('upc', 'like', "%{$search}%");
            });
        }
        
        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by stock status
        if ($request->has('stock_status')) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->lowStock();
                    break;
                case 'out_of_stock':
                    $query->outOfStock();
                    break;
                case 'in_stock':
                    $query->inStock();
                    break;
            }
        }
        
        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }
        
        // Sort
        $sort = $request->get('sort', 'part_number');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);
        
        $inventory = $query->paginate(25);
        $categories = InventoryCategory::active()->get();
        $suppliers = InventorySupplier::active()->get();
        
        // Statistics
        $stats = [
            'total_items' => Inventory::count(),
            'total_value' => Inventory::sum(DB::raw('quantity * cost_price')),
            'low_stock' => Inventory::lowStock()->count(),
            'out_of_stock' => Inventory::outOfStock()->count(),
            'active_items' => Inventory::active()->count(),
        ];
        
        return view('inventory.index', compact('inventory', 'categories', 'suppliers', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = InventoryCategory::active()->get();
        $suppliers = InventorySupplier::active()->get();
        return view('inventory.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_number' => 'required|unique:inventory|max:100',
            'name' => 'required|max:255',
            'description' => 'nullable',
            'category_id' => 'required|exists:inventory_categories,id',
            'supplier_id' => 'required|exists:inventory_suppliers,id',
            'manufacturer' => 'nullable|max:100',
            'oem_number' => 'nullable|max:100',
            'upc' => 'nullable|max:50',
            'location' => 'nullable|max:100',
            'bin' => 'nullable|max:50',
            'quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'core_price' => 'nullable|numeric|min:0',
            'is_taxable' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable',
            'image_url' => 'nullable|url',
            'barcode' => 'nullable|max:100',
        ]);
        
        // Calculate initial values
        $validated['status'] = $validated['quantity'] <= 0 ? 'out_of_stock' : 
                              ($validated['quantity'] <= $validated['reorder_point'] ? 'low_stock' : 'in_stock');
        
        $inventory = Inventory::create($validated);
        
        return redirect()->route('inventory.index')
                         ->with('success', 'Inventory item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        $inventory->load(['category', 'supplier', 'purchaseOrderItems.purchaseOrder', 'workOrderItems.workOrder']);
        
        // Statistics for this item
        $stats = [
            'total_sales' => $inventory->total_sales,
            'total_cost' => $inventory->total_cost,
            'profit' => $inventory->total_sales - $inventory->total_cost,
            'profit_margin' => $inventory->profit_margin,
            'turnover_rate' => $inventory->turnover_rate,
            'days_of_supply' => $inventory->days_of_supply,
        ];
        
        // Recent transactions
        $transactions = collect();
        
        // Add purchase order items
        foreach ($inventory->purchaseOrderItems as $item) {
            $transactions->push([
                'date' => $item->created_at,
                'type' => 'purchase',
                'quantity' => $item->quantity_ordered,
                'reference' => $item->purchaseOrder->po_number,
                'status' => $item->status,
            ]);
        }
        
        // Add work order items (sales)
        foreach ($inventory->workOrderItems as $item) {
            $transactions->push([
                'date' => $item->created_at,
                'type' => 'sale',
                'quantity' => $item->quantity,
                'reference' => $item->workOrder->work_order_number,
                'status' => $item->workOrder->status,
            ]);
        }
        
        // Sort by date
        $transactions = $transactions->sortByDesc('date')->take(20);
        
        return view('inventory.show', compact('inventory', 'stats', 'transactions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventory $inventory)
    {
        $categories = InventoryCategory::active()->get();
        $suppliers = InventorySupplier::active()->get();
        return view('inventory.edit', compact('inventory', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'part_number' => 'required|max:100|unique:inventory,part_number,' . $inventory->id,
            'name' => 'required|max:255',
            'description' => 'nullable',
            'category_id' => 'required|exists:inventory_categories,id',
            'supplier_id' => 'required|exists:inventory_suppliers,id',
            'manufacturer' => 'nullable|max:100',
            'oem_number' => 'nullable|max:100',
            'upc' => 'nullable|max:50',
            'location' => 'nullable|max:100',
            'bin' => 'nullable|max:50',
            'quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'core_price' => 'nullable|numeric|min:0',
            'is_taxable' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable',
            'image_url' => 'nullable|url',
            'barcode' => 'nullable|max:100',
        ]);
        
        // Update status based on quantity
        $validated['status'] = $validated['quantity'] <= 0 ? 'out_of_stock' : 
                              ($validated['quantity'] <= $validated['reorder_point'] ? 'low_stock' : 'in_stock');
        
        $inventory->update($validated);
        
        return redirect()->route('inventory.index')
                         ->with('success', 'Inventory item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        // Check if item has transactions
        if ($inventory->purchaseOrderItems()->count() > 0 || $inventory->workOrderItems()->count() > 0) {
            return redirect()->route('inventory.index')
                             ->with('error', 'Cannot delete inventory item with existing transactions.');
        }
        
        $inventory->delete();
        
        return redirect()->route('inventory.index')
                         ->with('success', 'Inventory item deleted successfully.');
    }
    
    /**
     * Display low stock items.
     */
    public function lowStock()
    {
        $inventory = Inventory::with(['category', 'supplier'])
                             ->lowStock()
                             ->orderBy('quantity')
                             ->paginate(25);
        
        $stats = [
            'total_low_stock' => Inventory::lowStock()->count(),
            'total_out_of_stock' => Inventory::outOfStock()->count(),
            'total_value_at_risk' => Inventory::lowStock()->sum(DB::raw('quantity * cost_price')),
        ];
        
        return view('inventory.low-stock', compact('inventory', 'stats'));
    }
    
    /**
     * Display inventory statistics.
     */
    public function statistics()
    {
        // Overall statistics
        $totalSales = Inventory::sum('total_sales') ?? 0;
        $totalCost = Inventory::sum('total_cost') ?? 0;
        $totalProfit = $totalSales - $totalCost;
        $averageProfitMargin = Inventory::avg('profit_margin') ?? 0;
        
        $stats = [
            'total_items' => Inventory::count(),
            'total_value' => Inventory::sum(DB::raw('quantity * cost_price')) ?? 0,
            'total_sales_value' => $totalSales,
            'total_cost_value' => $totalCost,
            'total_profit' => $totalProfit,
            'average_profit_margin' => $averageProfitMargin,
            'low_stock_count' => Inventory::lowStock()->count(),
            'out_of_stock_count' => Inventory::outOfStock()->count(),
            'active_items' => Inventory::active()->count(),
        ];
        
        // Category statistics - using a simpler approach
        $categoryStats = InventoryCategory::withCount(['inventoryItems as item_count'])
                                         ->get()
                                         ->map(function($category) {
                                             $totalValue = $category->inventoryItems->sum(function($item) {
                                                 return $item->quantity * $item->cost_price;
                                             });
                                             $category->total_value = $totalValue;
                                             return $category;
                                         })
                                         ->filter(function($category) {
                                             return $category->item_count > 0;
                                         })
                                         ->sortByDesc('total_value')
                                         ->values();
        
        // Supplier statistics - using a simpler approach
        $supplierStats = InventorySupplier::withCount(['inventoryItems as item_count'])
                                         ->get()
                                         ->map(function($supplier) {
                                             $totalValue = $supplier->inventoryItems->sum(function($item) {
                                                 return $item->quantity * $item->cost_price;
                                             });
                                             $supplier->total_value = $totalValue;
                                             return $supplier;
                                         })
                                         ->filter(function($supplier) {
                                             return $supplier->item_count > 0;
                                         })
                                         ->sortByDesc('total_value')
                                         ->values();
        
        // Top selling items
        $topSelling = Inventory::orderByDesc('total_sold')
                              ->limit(10)
                              ->get();
        
        // Highest value items
        $highestValue = Inventory::orderByDesc(DB::raw('quantity * cost_price'))
                                ->limit(10)
                                ->get();
        
        // Items needing reorder
        $needReorder = Inventory::where('quantity', '<=', DB::raw('reorder_point'))
                               ->orderBy('quantity')
                               ->limit(20)
                               ->get();
        
        return view('inventory.statistics', compact(
            'stats', 'categoryStats', 'supplierStats', 
            'topSelling', 'highestValue', 'needReorder'
        ));
    }
    
    /**
     * Adjust inventory quantity.
     */
    public function adjustQuantity(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|max:255',
            'notes' => 'nullable',
        ]);
        
        $oldQuantity = $inventory->quantity;
        
        switch ($validated['adjustment_type']) {
            case 'add':
                $newQuantity = $oldQuantity + $validated['quantity'];
                break;
            case 'subtract':
                $newQuantity = max(0, $oldQuantity - $validated['quantity']);
                break;
            case 'set':
                $newQuantity = $validated['quantity'];
                break;
        }
        
        $inventory->updateStock($newQuantity, 'adjustment');
        
        // Log the adjustment (you would create an InventoryAdjustment model for this)
        // InventoryAdjustment::create([
        //     'inventory_id' => $inventory->id,
        //     'user_id' => auth()->id(),
        //     'adjustment_type' => $validated['adjustment_type'],
        //     'old_quantity' => $oldQuantity,
        //     'new_quantity' => $newQuantity,
        //     'quantity_change' => $newQuantity - $oldQuantity,
        //     'reason' => $validated['reason'],
        //     'notes' => $validated['notes'],
        // ]);
        
        return redirect()->route('inventory.show', $inventory)
                         ->with('success', 'Inventory quantity adjusted successfully.');
    }
    
    /**
     * Generate barcode for inventory item.
     */
    public function generateBarcode(Inventory $inventory)
    {
        if (empty($inventory->barcode)) {
            // Generate a simple barcode (in production, use a barcode library)
            $barcode = 'INV-' . str_pad($inventory->id, 6, '0', STR_PAD_LEFT) . '-' . time();
            $inventory->update(['barcode' => $barcode]);
        }
        
        return redirect()->route('inventory.show', $inventory)
                         ->with('success', 'Barcode generated successfully.');
    }
    
    /**
     * Export inventory to CSV.
     */
    public function export()
    {
        $inventory = Inventory::with(['category', 'supplier'])->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory_' . date('Y-m-d') . '.csv"',
        ];
        
        $callback = function() use ($inventory) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Part Number', 'Name', 'Description', 'Category', 'Supplier',
                'Quantity', 'Minimum Stock', 'Reorder Point', 'Cost Price',
                'Retail Price', 'Location', 'Bin', 'Status', 'Last Purchased',
                'Last Sold', 'Total Sold', 'Total Sales', 'Total Cost',
                'Profit Margin', 'Turnover Rate'
            ]);
            
            // Data rows
            foreach ($inventory as $item) {
                fputcsv($file, [
                    $item->part_number,
                    $item->name,
                    $item->description,
                    $item->category->name ?? '',
                    $item->supplier->name ?? '',
                    $item->quantity,
                    $item->minimum_stock,
                    $item->reorder_point,
                    $item->cost_price,
                    $item->retail_price,
                    $item->location,
                    $item->bin,
                    $item->status,
                    $item->last_purchased,
                    $item->last_sold,
                    $item->total_sold,
                    $item->total_sales,
                    $item->total_cost,
                    $item->profit_margin,
                    $item->turnover_rate,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}