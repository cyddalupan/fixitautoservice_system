<?php

namespace App\Http\Controllers;

use App\Models\InventorySupplier;
use Illuminate\Http\Request;

class InventorySupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InventorySupplier::with(['inventoryItems', 'purchaseOrders']);
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%");
            });
        }
        
        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }
        
        // Filter by preferred status
        if ($request->has('is_preferred')) {
            $query->where('is_preferred', $request->is_preferred === 'true');
        }
        
        // Filter by balance
        if ($request->has('balance')) {
            if ($request->balance === 'with_balance') {
                $query->where('current_balance', '>', 0);
            } elseif ($request->balance === 'no_balance') {
                $query->where('current_balance', '<=', 0);
            }
        }
        
        // Sort
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);
        
        $suppliers = $query->paginate(25);
        
        // Statistics
        $stats = [
            'total_suppliers' => InventorySupplier::count(),
            'active_suppliers' => InventorySupplier::active()->count(),
            'preferred_suppliers' => InventorySupplier::preferred()->count(),
            'suppliers_with_balance' => InventorySupplier::withBalance()->count(),
            'total_credit_limit' => InventorySupplier::sum('credit_limit'),
            'total_current_balance' => InventorySupplier::sum('current_balance'),
        ];
        
        return view('inventory.suppliers.index', compact('suppliers', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventory.suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inventory_suppliers,name',
            'code' => 'nullable|string|max:50|unique:inventory_suppliers,code',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'current_balance' => 'nullable|numeric|min:0',
            'tax_id' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'shipping_method' => 'nullable|string|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_preferred' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);
        
        // Set default values
        $validated['current_balance'] = $validated['current_balance'] ?? 0;
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['shipping_cost'] = $validated['shipping_cost'] ?? 0;
        $validated['lead_time_days'] = $validated['lead_time_days'] ?? 7;
        $validated['discount_percentage'] = $validated['discount_percentage'] ?? 0;
        $validated['is_preferred'] = $validated['is_preferred'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;
        
        $supplier = InventorySupplier::create($validated);
        
        return redirect()->route('inventory.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventorySupplier $supplier)
    {
        $supplier->load(['inventoryItems' => function($query) {
            $query->with(['category'])->orderBy('name');
        }, 'purchaseOrders' => function($query) {
            $query->orderBy('order_date', 'desc')->limit(10);
        }]);
        
        return view('inventory.suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventorySupplier $supplier)
    {
        return view('inventory.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventorySupplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inventory_suppliers,name,' . $supplier->id,
            'code' => 'nullable|string|max:50|unique:inventory_suppliers,code,' . $supplier->id,
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'current_balance' => 'nullable|numeric|min:0',
            'tax_id' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'shipping_method' => 'nullable|string|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_preferred' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);
        
        $supplier->update($validated);
        
        return redirect()->route('inventory.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventorySupplier $supplier)
    {
        // Check if supplier has inventory items
        if ($supplier->inventoryItems()->count() > 0) {
            return redirect()->route('inventory.suppliers.index')
                ->with('error', 'Cannot delete supplier that has inventory items. Please reassign items first.');
        }
        
        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->count() > 0) {
            return redirect()->route('inventory.suppliers.index')
                ->with('error', 'Cannot delete supplier that has purchase orders. Please delete or reassign purchase orders first.');
        }
        
        $supplier->delete();
        
        return redirect()->route('inventory.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
    
    /**
     * Update supplier balance
     */
    public function updateBalance(Request $request, InventorySupplier $supplier)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'type' => 'required|in:increase,decrease,set',
            'notes' => 'nullable|string',
        ]);
        
        $supplier->updateBalance($validated['amount'], $validated['type']);
        
        // Log the balance update
        activity()
            ->performedOn($supplier)
            ->withProperties([
                'amount' => $validated['amount'],
                'type' => $validated['type'],
                'old_balance' => $supplier->getOriginal('current_balance'),
                'new_balance' => $supplier->current_balance,
                'notes' => $validated['notes'] ?? null,
            ])
            ->log('balance_updated');
        
        return redirect()->route('inventory.suppliers.show', $supplier)
            ->with('success', 'Supplier balance updated successfully.');
    }
    
    /**
     * Get suppliers for API/select options
     */
    public function getSuppliers(Request $request)
    {
        $query = InventorySupplier::active();
        
        if ($request->has('preferred')) {
            $query->preferred();
        }
        
        $suppliers = $query->orderBy('name')->get();
        
        return response()->json($suppliers);
    }
}