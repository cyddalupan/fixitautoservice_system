<?php

namespace App\Http\Controllers;

use App\Models\PartsLookup;
use App\Models\PartsOrder;
use App\Models\PartsReturn;
use App\Models\CoreReturn;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\InventorySupplier;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartsProcurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $partsOrders = PartsOrder::with(['vendor', 'workOrder', 'customer', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('parts-procurement.index', compact('partsOrders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $vehicles = Vehicle::with('customer')->orderBy('make')->get();
        $workOrders = WorkOrder::whereIn('status', ['pending', 'in_progress', 'waiting_parts'])
            ->with(['customer', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->get();
        $vendors = InventorySupplier::where('is_active', true)->orderBy('name')->get();
        $inventoryItems = Inventory::where('quantity', '>', 0)->orderBy('name')->get();

        return view('parts-procurement.create', compact('customers', 'vehicles', 'workOrders', 'vendors', 'inventoryItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:inventory_suppliers,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'customer_id' => 'nullable|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'shipping_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'nullable|exists:inventory,id',
            'items.*.part_name' => 'required|string|max:255',
            'items.*.part_number' => 'nullable|string|max:100',
            'items.*.oem_number' => 'nullable|string|max:100',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.core_charge' => 'nullable|numeric|min:0',
            'items.*.core_return_required' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = 0;
            $coreCharge = 0;

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemTotal;
                $coreCharge += ($item['core_charge'] ?? 0) * $item['quantity'];
            }

            // Create parts order
            $partsOrder = PartsOrder::create([
                'vendor_id' => $validated['vendor_id'],
                'work_order_id' => $validated['work_order_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'status' => 'draft',
                'subtotal' => $subtotal,
                'shipping' => 0, // Would be calculated based on shipping method
                'tax' => 0, // Would be calculated based on tax rules
                'total' => $subtotal,
                'core_charge' => $coreCharge,
                'shipping_method' => $validated['shipping_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                $partsOrder->items()->create([
                    'inventory_item_id' => $item['inventory_item_id'] ?? null,
                    'part_name' => $item['part_name'],
                    'part_number' => $item['part_number'] ?? null,
                    'oem_number' => $item['oem_number'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'core_charge' => $item['core_charge'] ?? 0,
                    'core_return_required' => $item['core_return_required'] ?? false,
                    'status' => 'ordered',
                ]);
            }

            DB::commit();

            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('success', 'Parts order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create parts order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PartsOrder $partsOrder)
    {
        $partsOrder->load(['vendor', 'workOrder', 'customer', 'vehicle', 'items', 'returns', 'coreReturns', 'createdBy']);
        
        return view('parts-procurement.show', compact('partsOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PartsOrder $partsOrder)
    {
        if (!$partsOrder->is_editable) {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'This order cannot be edited because it is already ' . $partsOrder->status . '.');
        }

        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $vehicles = Vehicle::with('customer')->orderBy('make')->get();
        $workOrders = WorkOrder::whereIn('status', ['pending', 'in_progress', 'waiting_parts'])
            ->with(['customer', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->get();
        $vendors = InventorySupplier::where('is_active', true)->orderBy('name')->get();
        $inventoryItems = Inventory::where('quantity', '>', 0)->orderBy('name')->get();

        return view('parts-procurement.edit', compact('partsOrder', 'customers', 'vehicles', 'workOrders', 'vendors', 'inventoryItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PartsOrder $partsOrder)
    {
        if (!$partsOrder->is_editable) {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'This order cannot be edited because it is already ' . $partsOrder->status . '.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:inventory_suppliers,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'customer_id' => 'nullable|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'shipping_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:parts_order_items,id',
            'items.*.inventory_item_id' => 'nullable|exists:inventory,id',
            'items.*.part_name' => 'required|string|max:255',
            'items.*.part_number' => 'nullable|string|max:100',
            'items.*.oem_number' => 'nullable|string|max:100',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.core_charge' => 'nullable|numeric|min:0',
            'items.*.core_return_required' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = 0;
            $coreCharge = 0;

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemTotal;
                $coreCharge += ($item['core_charge'] ?? 0) * $item['quantity'];
            }

            // Update parts order
            $partsOrder->update([
                'vendor_id' => $validated['vendor_id'],
                'work_order_id' => $validated['work_order_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'core_charge' => $coreCharge,
                'shipping_method' => $validated['shipping_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update or create order items
            $existingItemIds = $partsOrder->items->pluck('id')->toArray();
            $updatedItemIds = [];

            foreach ($validated['items'] as $item) {
                if (isset($item['id']) && in_array($item['id'], $existingItemIds)) {
                    // Update existing item
                    $orderItem = $partsOrder->items()->find($item['id']);
                    $orderItem->update([
                        'inventory_item_id' => $item['inventory_item_id'] ?? null,
                        'part_name' => $item['part_name'],
                        'part_number' => $item['part_number'] ?? null,
                        'oem_number' => $item['oem_number'] ?? null,
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'core_charge' => $item['core_charge'] ?? 0,
                        'core_return_required' => $item['core_return_required'] ?? false,
                    ]);
                    $updatedItemIds[] = $item['id'];
                } else {
                    // Create new item
                    $partsOrder->items()->create([
                        'inventory_item_id' => $item['inventory_item_id'] ?? null,
                        'part_name' => $item['part_name'],
                        'part_number' => $item['part_number'] ?? null,
                        'oem_number' => $item['oem_number'] ?? null,
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'core_charge' => $item['core_charge'] ?? 0,
                        'core_return_required' => $item['core_return_required'] ?? false,
                        'status' => 'ordered',
                    ]);
                }
            }

            // Delete items that were removed
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                $partsOrder->items()->whereIn('id', $itemsToDelete)->delete();
            }

            DB::commit();

            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('success', 'Parts order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update parts order: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartsOrder $partsOrder)
    {
        if (!$partsOrder->is_cancellable) {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'This order cannot be cancelled because it is already ' . $partsOrder->status . '.');
        }

        DB::beginTransaction();

        try {
            $partsOrder->update(['status' => 'cancelled']);
            DB::commit();

            return redirect()->route('parts-procurement.index')
                ->with('success', 'Parts order cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel parts order: ' . $e->getMessage());
        }
    }

    /**
     * Submit order for approval.
     */
    public function submit(PartsOrder $partsOrder)
    {
        if ($partsOrder->status !== 'draft') {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'Only draft orders can be submitted for approval.');
        }

        $partsOrder->update(['status' => 'pending']);

        return redirect()->route('parts-procurement.show', $partsOrder)
            ->with('success', 'Parts order submitted for approval.');
    }

    /**
     * Approve order.
     */
    public function approve(PartsOrder $partsOrder)
    {
        if ($partsOrder->status !== 'pending') {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'Only pending orders can be approved.');
        }

        $partsOrder->update([
            'status' => 'ordered',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('parts-procurement.show', $partsOrder)
            ->with('success', 'Parts order approved and marked as ordered.');
    }

    /**
     * Mark order as shipped.
     */
    public function ship(PartsOrder $partsOrder, Request $request)
    {
        if ($partsOrder->status !== 'ordered') {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'Only ordered orders can be marked as shipped.');
        }

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100',
            'carrier' => 'required|string|max:100',
            'estimated_delivery_date' => 'required|date',
        ]);

        $partsOrder->update([
            'status' => 'shipped',
            'tracking_number' => $validated['tracking_number'],
            'carrier' => $validated['carrier'],
            'estimated_delivery_date' => $validated['estimated_delivery_date'],
        ]);

        return redirect()->route('parts-procurement.show', $partsOrder)
            ->with('success', 'Parts order marked as shipped.');
    }

    /**
     * Mark order as delivered.
     */
    public function deliver(PartsOrder $partsOrder)
    {
        if ($partsOrder->status !== 'shipped') {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'Only shipped orders can be marked as delivered.');
        }

        $partsOrder->update([
            'status' => 'delivered',
            'actual_delivery_date' => now(),
        ]);

        // Update inventory if items are linked to inventory
        foreach ($partsOrder->items as $item) {
            if ($item->inventory_item_id) {
                $inventoryItem = Inventory::find($item->inventory_item_id);
                if ($inventoryItem) {
                    $inventoryItem->increment('quantity', $item->quantity);
                }
            }
        }

        return redirect()->route('parts-procurement.show', $partsOrder)
            ->with('success', 'Parts order marked as delivered and inventory updated.');
    }

    /**
     * Parts lookup functionality.
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'vin' => 'nullable|string|max:17',
            'make' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'part_category' => 'nullable|string|max:100',
            'part_name' => 'required|string|max:255',
        ]);

        // In a real implementation, this would call external APIs for parts lookup
        // For now, we'll simulate with some sample data
        
        $partsLookup = PartsLookup::create([
            'vin' => $validated['vin'] ?? null,
            'make' => $validated['make'] ?? null,
            'model' => $validated['model'] ?? null,
            'year' => $validated['year'] ?? null,
            'part_category' => $validated['part_category'] ?? null,
            'part_name' => $validated['part_name'],
            'created_by' => auth()->id(),
        ]);

        // Simulate API search results
        $searchResults = [
            [
                'vendor' => 'AutoParts Direct',
                'part_number' => 'APD-' . rand(1000, 9999),
                'price' => rand(50, 200) + (rand(0, 99) / 100),
                'shipping' => rand(5, 20),
                'estimated_delivery' => '2-3 business days',
                'in_stock' => true,
            ],
            [
                'vendor' => 'Parts Unlimited',
                'part_number' => 'PU-' . rand(1000, 9999),
                'price' => rand(50, 200) + (rand(0, 99) / 100),
                'shipping' => rand(5, 20),
                'estimated_delivery' => '2-3 business days',
                'in_stock' => true,
            ],
            [
                'vendor' => 'OEM Parts Source',
                'part_number' => 'OEM-' . rand(1000, 9999),
                'price' => rand(100, 300) + (rand(0, 99) / 100),
                'shipping' => rand(10, 30),
                'estimated_delivery' => '3-5 business days',
                'in_stock' => true,
            ],
        ];

        $partsLookup->update(['search_results' => $searchResults]);

        return view('parts-procurement.lookup-results', compact('partsLookup', 'searchResults'));
    }

    /**
     * Show parts returns index.
     */
    public function returnsIndex()
    {
        $partsReturns = PartsReturn::with(['partsOrder', 'vendor', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('parts-procurement.returns-index', compact('partsReturns'));
    }

    /**
     * Show core returns index.
     */
    public function coreReturnsIndex()
    {
        $coreReturns = CoreReturn::with(['partsOrder', 'vendor', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('parts-procurement.core-returns-index', compact('coreReturns'));
    }

    /**
     * Create a new return.
     */
    public function createReturn(PartsOrder $partsOrder)
    {
        if ($partsOrder->status !== 'delivered') {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'Only delivered orders can have returns.');
        }

        $partsOrder->load('items');

        return view('parts-procurement.create-return', compact('partsOrder'));
    }

    /**
     * Store a new return.
     */
    public function storeReturn(Request $request, PartsOrder $partsOrder)
    {
        if ($partsOrder->status !== 'delivered') {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'Only delivered orders can have returns.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.parts_order_item_id' => 'required|exists:parts_order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|string|in:new,used,damaged,defective',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Create parts return
            $partsReturn = PartsReturn::create([
                'parts_order_id' => $partsOrder->id,
                'vendor_id' => $partsOrder->vendor_id,
                'reason' => $validated['reason'],
                'description' => $validated['description'] ?? null,
                'status' => 'requested',
                'created_by' => auth()->id(),
            ]);

            // Create return items
            $totalRefund = 0;

            foreach ($validated['items'] as $item) {
                $orderItem = $partsOrder->items()->find($item['parts_order_item_id']);
                
                if ($orderItem->quantity < $item['quantity']) {
                    throw new \Exception('Return quantity cannot exceed ordered quantity.');
                }

                $refundAmount = $orderItem->unit_price * $item['quantity'];
                $restockingFee = $item['condition'] === 'new' ? 0 : $refundAmount * 0.15; // 15% restocking fee for non-new items

                $partsReturn->items()->create([
                    'parts_order_item_id' => $item['parts_order_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $orderItem->unit_price,
                    'total_price' => $refundAmount,
                    'refund_amount' => $refundAmount,
                    'restocking_fee' => $restockingFee,
                    'condition' => $item['condition'],
                    'notes' => $item['notes'] ?? null,
                ]);

                $totalRefund += $refundAmount - $restockingFee;
            }

            $partsReturn->update(['refund_amount' => $totalRefund]);

            DB::commit();

            return redirect()->route('parts-procurement.returns.show', $partsReturn)
                ->with('success', 'Return request created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create return request: ' . $e->getMessage());
        }
    }

    /**
     * Show a return.
     */
    public function showReturn(PartsReturn $partsReturn)
    {
        $partsReturn->load(['partsOrder', 'vendor', 'items.partsOrderItem', 'createdBy', 'approvedBy']);

        return view('parts-procurement.show-return', compact('partsReturn'));
    }

    /**
     * Create a new core return.
     */
    public function createCoreReturn(PartsOrder $partsOrder)
    {
        $coreItems = $partsOrder->items()->where('core_return_required', true)->where('core_returned', false)->get();

        if ($coreItems->isEmpty()) {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'No core returns required for this order.');
        }

        return view('parts-procurement.create-core-return', compact('partsOrder', 'coreItems'));
    }

    /**
     * Store a new core return.
     */
    public function storeCoreReturn(Request $request, PartsOrder $partsOrder)
    {
        $validated = $request->validate([
            'core_type' => 'required|string|max:100',
            'core_part_number' => 'nullable|string|max:100',
            'condition' => 'required|string|in:rebuildable,damaged,missing_parts',
            'description' => 'nullable|string',
            'return_due_date' => 'required|date',
            'shipping_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $coreCharge = $partsOrder->items()
            ->where('core_return_required', true)
            ->where('core_returned', false)
            ->sum('core_charge');

        if ($coreCharge <= 0) {
            return redirect()->route('parts-procurement.show', $partsOrder)
                ->with('error', 'No core charge available for return.');
        }

        $coreReturn = CoreReturn::create([
            'parts_order_id' => $partsOrder->id,
            'vendor_id' => $partsOrder->vendor_id,
            'core_type' => $validated['core_type'],
            'core_part_number' => $validated['core_part_number'] ?? null,
            'condition' => $validated['condition'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
            'core_charge' => $coreCharge,
            'expected_refund' => $coreCharge * 0.8, // Typically 80% refund for rebuildable cores
            'return_due_date' => $validated['return_due_date'],
            'shipping_method' => $validated['shipping_method'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('parts-procurement.core-returns.show', $coreReturn)
            ->with('success', 'Core return created successfully.');
    }

    /**
     * Show a core return.
     */
    public function showCoreReturn(CoreReturn $coreReturn)
    {
        $coreReturn->load(['partsOrder', 'vendor', 'createdBy']);

        return view('parts-procurement.show-core-return', compact('coreReturn'));
    }
}