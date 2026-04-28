<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Inventory;
use App\Models\VehicleInspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EstimateController extends Controller
{
    /**
     * Display a listing of estimates.
     */
    public function index(Request $request)
    {
        $query = Estimate::with(['customer', 'vehicle', 'items'])
            ->whereNull('deleted_at');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('estimate_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', function($cq) use ($s) {
                      $cq->where('first_name', 'like', "%{$s}%")
                         ->orWhere('last_name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $estimates = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('estimates.index', compact('estimates'));
    }

    /**
     * Show the form for creating a new estimate.
     */
    public function create(Request $request)
    {
        $customers = Customer::orderBy('first_name')->get();
        $advisors = User::whereIn('role', ['admin', 'staff', 'service_advisor'])->get();
        $lastNum = Estimate::where('estimate_number', 'like', 'EST-' . now()->format('Ymd') . '-%')
            ->count();
        $inventoryItems = \App\Models\Inventory::with("category")->whereNull("deleted_at")->get();
        $inventoryItemsJson = $inventoryItems->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'part_number' => $item->part_number,
                'description' => $item->description,
                'retail_price' => floatval($item->retail_price),
                'quantity' => intval($item->quantity),
                'manufacturer' => $item->manufacturer ?? '',
            ];
        })->values();

        $selectedCustomer = null;
        $selectedVehicle = null;
        $customerVehicles = collect();
        $customerHistory = collect();

        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);
            if ($selectedCustomer) {
                $customerVehicles = Vehicle::where('customer_id', $selectedCustomer->id)->get();
                $customerHistory = Estimate::where('customer_id', $selectedCustomer->id)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                // If vehicle_id is also passed
                if ($request->filled('vehicle_id')) {
                    $selectedVehicle = Vehicle::find($request->vehicle_id);
                } elseif ($customerVehicles->count() > 0) {
                    $selectedVehicle = $customerVehicles->first();
                }
            }
        }

        // Get inspection findings if a customer is selected
        $inspectionFindings = collect();
        if ($selectedCustomer) {
            $latestInspection = \App\Models\VehicleInspection::where('customer_id', $selectedCustomer->id)
                ->whereNotNull('inspection_status')
                ->orderBy('created_at', 'desc')
                ->first();
            if ($latestInspection) {
                $latestInspection->load(['inspectionFindings.technician']);
                $inspectionFindings = $latestInspection->inspectionFindings;
            }
        }

        // Load quotation data for auto-fill
        $quotationData = null;
        if ($request->filled('quotation_id')) {
            $quotation = \App\Models\Quotation::find($request->quotation_id);
            if ($quotation && $quotation->customer_id == ($selectedCustomer->id ?? null)) {
                $quotationData = $quotation;
            }
        } elseif ($selectedCustomer) {
            $latestQuotation = $selectedCustomer->quotations()->latest()->first();
            if ($latestQuotation) {
                $quotationData = $latestQuotation;
            }
        }

        return view('estimates.create', compact(
            'customers', 'advisors', 'lastNum', 'inventoryItems', 'inventoryItemsJson',
            'selectedCustomer', 'selectedVehicle', 'customerVehicles', 'customerHistory',
            'inspectionFindings', 'quotationData'
        ));
    }

    /**
     * Store a newly created estimate.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'estimate_number' => 'nullable|string|max:50|unique:estimates,estimate_number',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'service_type' => 'nullable|array',
            'service_type.*' => 'string|in:' . implode(',', array_keys(config('service-types.list'))),
            'mileage' => 'nullable|numeric|min:0',
            'service_advisor_id' => 'nullable|exists:users,id',
            'discount_type' => 'nullable|string|max:20',
            'discount_value' => 'nullable|numeric|min:0',
            'deposit_required' => 'nullable|numeric|min:0',
            'items_json' => 'nullable|json',
        ]);

        DB::beginTransaction();
        try {
            // Generate estimate number if not provided
            if (empty($validated['estimate_number'])) {
                $count = Estimate::where('estimate_number', 'like', 'EST-' . now()->format('Ymd') . '-%')->count();
                $validated['estimate_number'] = 'EST-' . now()->format('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            }

            // Compute totals from items
            $subtotal = 0;
            $partsTotal = 0;
            $laborTotal = 0;
            $discountAmount = 0;
            $taxTotal = 0;
            $items = [];

            if ($request->filled('items_json')) {
                $items = json_decode($request->items_json, true) ?? [];
            } elseif ($request->has('items')) {
                // Fallback to individual item fields
                $raw = $request->input('items', []);
                foreach ($raw as $idx => $item) {
                    $items[] = [
                        'description' => $item['desc'] ?? '',
                        'category' => $item['cat'] ?? 'parts',
                        'quantity' => floatval($item['qty'] ?? 1),
                        'unit_price' => floatval($item['price'] ?? 0),
                        'discount' => floatval($item['disc'] ?? 0),
                        'tax_rate' => floatval($item['tax'] ?? 0),
                        'sort_order' => $idx,
                    ];
                }
            }

            foreach ($items as &$item) {
                $qty = floatval($item['quantity'] ?? 1);
                $price = floatval($item['unit_price'] ?? 0);
                $lineTotal = $qty * $price;
                $discPct = floatval($item['discount'] ?? 0);
                $taxPct = floatval($item['tax_rate'] ?? 0);

                $lineDiscount = $discPct > 0 ? $lineTotal * (min($discPct, 100) / 100) : 0;
                $afterDisc = $lineTotal - $lineDiscount;
                $lineTax = $taxPct > 0 ? $afterDisc * ($taxPct / 100) : 0;
                $lineSubtotal = $afterDisc + $lineTax;

                $item['line_total'] = $lineTotal;
                $item['line_discount'] = $lineDiscount;
                $item['line_tax'] = $lineTax;
                $item['subtotal'] = $lineSubtotal;

                $subtotal += $afterDisc;
                $discountAmount += $lineDiscount;
                $taxTotal += $lineTax;

                if (in_array($item['category'] ?? '', ['parts', 'materials'])) {
                    $partsTotal += $lineSubtotal;
                } else {
                    $laborTotal += $lineSubtotal;
                }
            }
            unset($item);

            // Global discount
            $discType = $request->discount_type;
            $discVal = floatval($request->discount_value ?? 0);
            $globalDiscount = 0;
            if ($discType === 'percentage' && $discVal > 0) {
                $globalDiscount = $subtotal * (min($discVal, 100) / 100);
            } elseif ($discType === 'fixed' && $discVal > 0) {
                $globalDiscount = min($discVal, $subtotal);
            }

            $totalDiscount = $discountAmount + $globalDiscount;
            $grandTotal = $subtotal - $globalDiscount + $taxTotal;
            $deposit = floatval($request->deposit_required ?? 0);
            $balance = max(0, $grandTotal - $deposit);
            $status = $validated['status'] ?? 'draft';

            // If status is pending, set sent_at
            $sentAt = null;
            if ($status === 'pending') {
                $sentAt = now();
            }

            $estimate = Estimate::create([
                'customer_id' => $validated['customer_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'estimate_number' => $validated['estimate_number'],
                'status' => $status,
                'issue_date' => $validated['issue_date'] ?? now(),
                'expiry_date' => $validated['expiry_date'] ?? now()->addDays(14),
                'subtotal' => $subtotal,
                'discount_type' => $discType,
                'discount_value' => $discVal,
                'discount_amount' => $totalDiscount,
                'tax_total' => $taxTotal,
                'total_amount' => $grandTotal,
                'parts_total' => $partsTotal,
                'labor_total' => $laborTotal,
                'deposit_required' => $deposit,
                'balance_remaining' => $balance,
                'notes' => $validated['notes'] ?? null,
                'internal_notes' => $validated['internal_notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'mileage' => $validated['mileage'] ?? null,
                'service_advisor_id' => $validated['service_advisor_id'] ?? null,
                'sent_at' => $sentAt,
                'user_id' => auth()->id(),
            ]);

            // Create estimate items
            foreach ($items as $item) {
                $estimate->items()->create([
                    'estimate_id' => $estimate->id,
                    'item_name' => $item['description'] ?? '',
                    'description' => $item['description'] ?? '',
                    'category' => $item['category'] ?? 'parts',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => 'percentage',
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $item['subtotal'] ?? 0,
                    'sort_order' => $item['sort_order'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('estimates.show', $estimate)
                ->with('success', 'Estimate #' . $estimate->estimate_number . ' created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Estimate creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create estimate: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified estimate.
     */
    public function show(Estimate $estimate)
    {
        $estimate->load(['customer', 'vehicle', 'items', 'user', 'serviceAdvisor', 'workOrder']);

        // Mark as viewed if not yet viewed (keep existing status)
        if ($estimate->viewed_at === null) {
            $estimate->update(['viewed_at' => now()]);
        }

        return view('estimates.show', compact('estimate'));
    }

    /**
     * Show the form for editing the specified estimate.
     */
    public function edit(Estimate $estimate)
    {
        // Mark as viewed if not yet viewed
        if ($estimate->viewed_at === null) {
            $estimate->update(['viewed_at' => now()]);
        }
        
        $estimate->load(['customer', 'vehicle', 'items']);
        $customers = Customer::orderBy('first_name')->get();
        $advisors = User::whereIn('role', ['admin', 'staff', 'service_advisor'])->get();
        $customerVehicles = Vehicle::where('customer_id', $estimate->customer_id)->get();
        $lastNum = Estimate::where('estimate_number', 'like', 'EST-' . now()->format('Ymd') . '-%')->count();
        $inventoryItems = \App\Models\Inventory::with("category")->whereNull("deleted_at")->get();
        $customerHistory = Estimate::where('customer_id', $estimate->customer_id)
            ->where('id', '!=', $estimate->id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $selectedCustomer = $estimate->customer;
        $selectedVehicle = $estimate->vehicle;

        return view('estimates.create', compact(
            'estimate', 'customers', 'advisors', 'lastNum', 'inventoryItems', 'inventoryItemsJson',
            'selectedCustomer', 'selectedVehicle', 'customerVehicles', 'customerHistory'
        ));
    }

    /**
     * Update the specified estimate.
     */
    public function update(Request $request, Estimate $estimate)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'estimate_number' => 'nullable|string|max:50|unique:estimates,estimate_number,' . $estimate->id,
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'service_type' => 'nullable|array',
            'service_type.*' => 'string|in:' . implode(',', array_keys(config('service-types.list'))),
            'mileage' => 'nullable|numeric|min:0',
            'service_advisor_id' => 'nullable|exists:users,id',
            'discount_type' => 'nullable|string|max:20',
            'discount_value' => 'nullable|numeric|min:0',
            'deposit_required' => 'nullable|numeric|min:0',
            'items_json' => 'nullable|json',
        ]);

        DB::beginTransaction();
        try {
            // Compute totals (same logic as store)
            $subtotal = 0;
            $partsTotal = 0;
            $laborTotal = 0;
            $discountAmount = 0;
            $taxTotal = 0;
            $items = [];

            if ($request->filled('items_json')) {
                $items = json_decode($request->items_json, true) ?? [];
            } elseif ($request->has('items')) {
                $raw = $request->input('items', []);
                foreach ($raw as $idx => $item) {
                    $items[] = [
                        'description' => $item['desc'] ?? '',
                        'category' => $item['cat'] ?? 'parts',
                        'quantity' => floatval($item['qty'] ?? 1),
                        'unit_price' => floatval($item['price'] ?? 0),
                        'discount' => floatval($item['disc'] ?? 0),
                        'tax_rate' => floatval($item['tax'] ?? 0),
                        'sort_order' => $idx,
                    ];
                }
            }

            foreach ($items as &$item) {
                $qty = floatval($item['quantity'] ?? 1);
                $price = floatval($item['unit_price'] ?? 0);
                $lineTotal = $qty * $price;
                $discPct = floatval($item['discount'] ?? 0);
                $taxPct = floatval($item['tax_rate'] ?? 0);

                $lineDiscount = $discPct > 0 ? $lineTotal * (min($discPct, 100) / 100) : 0;
                $afterDisc = $lineTotal - $lineDiscount;
                $lineTax = $taxPct > 0 ? $afterDisc * ($taxPct / 100) : 0;
                $lineSubtotal = $afterDisc + $lineTax;

                $item['line_total'] = $lineTotal;
                $item['line_discount'] = $lineDiscount;
                $item['line_tax'] = $lineTax;
                $item['subtotal'] = $lineSubtotal;

                $subtotal += $afterDisc;
                $discountAmount += $lineDiscount;
                $taxTotal += $lineTax;

                if (in_array($item['category'] ?? '', ['parts', 'materials'])) {
                    $partsTotal += $lineSubtotal;
                } else {
                    $laborTotal += $lineSubtotal;
                }
            }
            unset($item);

            $discType = $request->discount_type;
            $discVal = floatval($request->discount_value ?? 0);
            $globalDiscount = 0;
            if ($discType === 'percentage' && $discVal > 0) {
                $globalDiscount = $subtotal * (min($discVal, 100) / 100);
            } elseif ($discType === 'fixed' && $discVal > 0) {
                $globalDiscount = min($discVal, $subtotal);
            }

            $totalDiscount = $discountAmount + $globalDiscount;
            $grandTotal = $subtotal - $globalDiscount + $taxTotal;
            $deposit = floatval($request->deposit_required ?? 0);
            $balance = max(0, $grandTotal - $deposit);

            $estimate->update([
                'customer_id' => $validated['customer_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'estimate_number' => $validated['estimate_number'] ?? $estimate->estimate_number,
                'issue_date' => $validated['issue_date'] ?? $estimate->issue_date,
                'expiry_date' => $validated['expiry_date'] ?? $estimate->expiry_date,
                'subtotal' => $subtotal,
                'discount_type' => $discType,
                'discount_value' => $discVal,
                'discount_amount' => $totalDiscount,
                'tax_total' => $taxTotal,
                'total_amount' => $grandTotal,
                'parts_total' => $partsTotal,
                'labor_total' => $laborTotal,
                'deposit_required' => $deposit,
                'balance_remaining' => $balance,
                'notes' => $validated['notes'] ?? null,
                'internal_notes' => $validated['internal_notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'mileage' => $validated['mileage'] ?? null,
                'service_advisor_id' => $validated['service_advisor_id'] ?? null,
            ]);

            // Update status if provided and it's a transition
            if ($request->filled('status')) {
                $estimate->update(['status' => $request->status]);
            }

            // Delete old items and recreate
            $estimate->items()->delete();
            foreach ($items as $item) {
                $estimate->items()->create([
                    'estimate_id' => $estimate->id,
                    'item_name' => $item['description'] ?? '',
                    'description' => $item['description'] ?? '',
                    'category' => $item['category'] ?? 'parts',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => 'percentage',
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $item['subtotal'] ?? 0,
                    'sort_order' => $item['sort_order'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('estimates.show', $estimate)
                ->with('success', 'Estimate #' . $estimate->estimate_number . ' updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Estimate update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update estimate: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified estimate (soft delete).
     */
    public function destroy(Estimate $estimate)
    {
        $estimate->delete();
        return redirect()->route('estimates.index')
            ->with('success', 'Estimate #' . $estimate->estimate_number . ' archived.');
    }

    /**
     * Send estimate to customer.
     */
    public function sendEstimate(Estimate $estimate)
    {
        if ($estimate->status === 'draft') {
            $estimate->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // TODO: Send email/SMS notification to customer
            // Mail::to($estimate->customer->email)->send(new EstimateMail($estimate));

            return redirect()->route('estimates.show', $estimate)
                ->with('success', 'Estimate #' . $estimate->estimate_number . ' sent to customer.');
        }

        return back()->with('error', 'Estimate must be in Draft status to send.');
    }

    /**
     * Approve estimate.
     */
    public function approve(Request $request, Estimate $estimate)
    {
        if (in_array($estimate->status, ['sent', 'viewed'])) {
            $estimate->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            return redirect()->route('estimates.show', $estimate)
                ->with('success', 'Estimate #' . $estimate->estimate_number . ' approved.');
        }

        return back()->with('error', 'Estimate cannot be approved from current status.');
    }

    /**
     * Reject estimate.
     */
    public function reject(Request $request, Estimate $estimate)
    {
        if (in_array($estimate->status, ['sent', 'viewed'])) {
            $estimate->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $request->input('reason'),
            ]);

            return redirect()->route('estimates.show', $estimate)
                ->with('success', 'Estimate #' . $estimate->estimate_number . ' rejected.');
        }

        return back()->with('error', 'Estimate cannot be rejected from current status.');
    }

    /**
     * Duplicate an estimate.
     */
    public function duplicate(Estimate $estimate)
    {
        $estimate->load('items');
        DB::beginTransaction();
        try {
            $count = Estimate::where('estimate_number', 'like', 'EST-' . now()->format('Ymd') . '-%')->count();
            $newNum = 'EST-' . now()->format('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $newEstimate = $estimate->replicate();
            $newEstimate->estimate_number = $newNum;
            $newEstimate->status = 'draft';
            $newEstimate->sent_at = null;
            $newEstimate->viewed_at = null;
            $newEstimate->approved_at = null;
            $newEstimate->rejected_at = null;
            $newEstimate->approved_by = null;
            $newEstimate->save();

            foreach ($estimate->items as $item) {
                $newItem = $item->replicate();
                $newItem->estimate_id = $newEstimate->id;
                $newItem->save();
            }

            DB::commit();
            return redirect()->route('estimates.show', $newEstimate)
                ->with('success', 'Estimate duplicated as #' . $newNum);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to duplicate estimate.');
        }
    }

    /**
     * Convert approved estimate to work order.
     */
    public function convertToWorkOrder(Estimate $estimate)
    {
        if ($estimate->status !== 'approved') {
            return back()->with('error', 'Only approved estimates can be converted.');
        }

        DB::beginTransaction();
        try {
            $wo = \App\Models\WorkOrder::create([
                'customer_id' => $estimate->customer_id,
                'vehicle_id' => $estimate->vehicle_id,
                'estimate_id' => $estimate->id,
                'status' => 'pending',
                'notes' => 'Converted from Estimate #' . $estimate->estimate_number,
                'user_id' => auth()->id(),
            ]);

            $estimate->update(['status' => 'converted']);

            DB::commit();
            return redirect()->route('work-orders.show', $wo)
                ->with('success', 'Estimate converted to Work Order #' . $wo->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * Get vehicles for a customer (AJAX).
     */
    public function customerVehicles(Request $request)
    {
        $customerId = $request->input('customer_id');
        $vehicles = Vehicle::where('customer_id', $customerId)->get();
        return response()->json($vehicles);
    }
}
