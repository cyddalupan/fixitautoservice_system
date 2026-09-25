<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateItemGroup;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\JobOrder;
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
        $query = Estimate::with([
                'customer', 'vehicle', 'items',
                // Needed so the Amount column can read the linked Repair Order's
                // findings (parts + labor, excluding "Not Pursued").
                'inspection.inspectionFindings', 'inspection.findingGroups',
                // Payment badge reads the linked Repair Order's payment records.
                'inspection.repairOrderPayments',
            ])
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
        // NOTE: estimates.estimate_number has a DB UNIQUE index while the model
        // soft-deletes. Count WITH trashed so the suggested number never collides
        // with a deleted quotation (which would make every save bounce back).
        $lastNum = Estimate::withTrashed()
            ->where('estimate_number', 'like', 'EST-' . now()->format('Ymd') . '-%')
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

        // ---- Prefill from a Repair Order ("Create Repair Quotation" button) ----
        // When launched from a Repair Order we know exactly which inspection to
        // quote, so use ITS customer/vehicle/findings instead of the "latest" guess.
        $prefillInspection = null;
        if ($request->filled('inspection_id')) {
            $prefillInspection = \App\Models\VehicleInspection::with(['inspectionFindings.group'])
                ->find($request->inspection_id);
            if ($prefillInspection) {
                if (!$selectedCustomer && $prefillInspection->customer_id) {
                    $selectedCustomer = Customer::find($prefillInspection->customer_id);
                    if ($selectedCustomer) {
                        $customerVehicles = Vehicle::where('customer_id', $selectedCustomer->id)->get();
                        $customerHistory = Estimate::where('customer_id', $selectedCustomer->id)
                            ->whereNull('deleted_at')->orderBy('created_at', 'desc')->limit(5)->get();
                    }
                }
                if (!$selectedVehicle && $prefillInspection->vehicle_id) {
                    $selectedVehicle = Vehicle::find($prefillInspection->vehicle_id);
                }
                $inspectionFindings = $prefillInspection->inspectionFindings;

                // Re-quoting an RO that was promoted from an APPROVED quotation: only
                // the during-repair findings are new. `new_only=1` (the "New Repair
                // Quotation" button) keeps just those — the already-approved lines stay
                // on the original quotation and must not be re-quoted.
                if ($request->boolean('new_only')) {
                    $inspectionFindings = $inspectionFindings
                        ->filter(fn ($f) => ! $f->is_declined && ! $prefillInspection->findingIsLocked($f))
                        ->values();
                }
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
        
        // Check for active transactions on the selected vehicle
        $activeTransaction = null;
        if ($selectedVehicle) {
            $activeTransaction = \App\Services\ActiveTransactionService::checkActiveTransaction($selectedVehicle->id);
            // When launched from a Repair Order, that RO's own service chain
            // (inspection/appointment/estimate) is the SOURCE being quoted — not a
            // duplicate. Suppress the warning modal for it, but still show it for a
            // genuinely downstream conflict (an active Work Order).
            if ($prefillInspection && $activeTransaction && ($activeTransaction['stage_key'] ?? null) !== 'job_order') {
                $activeTransaction = null;
            }
        }

        // Prefill safety: the chosen vehicle MUST exist as an <option> in the
        // dropdown, otherwise the <select> posts an empty vehicle_id and the form
        // bounces back (302, "nothing saved"). This happens when the RO's vehicle
        // is not listed under the RO's customer (mismatched customer/vehicle) — the
        // customer's vehicle list comes back empty. Always append the selected one.
        if ($selectedVehicle && ! $customerVehicles->contains('id', $selectedVehicle->id)) {
            $customerVehicles = $customerVehicles->push($selectedVehicle);
        }

        return view('estimates.create', compact(
            'customers', 'advisors', 'lastNum', 'inventoryItems', 'inventoryItemsJson',
            'selectedCustomer', 'selectedVehicle', 'customerVehicles', 'customerHistory',
            'inspectionFindings', 'quotationData', 'activeTransaction', 'prefillInspection'
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
            // Ignore soft-deleted rows here (the DB unique index still applies at
            // insert time; store() guarantees a free number before inserting).
            'estimate_number' => ['nullable', 'string', 'max:50',
                \Illuminate\Validation\Rule::unique('estimates', 'estimate_number')->whereNull('deleted_at')],
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'service_type' => 'nullable|array',
            'service_type.*' => 'string|in:' . implode(',', \App\Models\ServiceType::keys()),
            'mileage' => 'nullable|numeric|min:0',
            'service_advisor_id' => 'nullable|exists:users,id',
            'discount_type' => 'nullable|string|max:20',
            'discount_value' => 'nullable|numeric|min:0',
            'deposit_required' => 'nullable|numeric|min:0',
            'items_json' => 'nullable|json',
            'inspection_id' => 'nullable|integer|exists:vehicle_inspections,id',
        ]);

        // Detect "launched from Repair Order": converting that RO's own findings
        // into a quotation. In that case the RO/Appointment chain IS the source we
        // are quoting, so the duplicate-active-transaction guards must not block it.
        $launchedFromInspection = $request->filled('inspection_id')
            && $request->filled('vehicle_id')
            && \App\Models\VehicleInspection::where('id', $request->inspection_id)
                ->where('vehicle_id', $request->vehicle_id)
                ->exists();

        // Check for duplicate active transaction (skip if override_duplicate is set)
        if (!$launchedFromInspection && (!$request->filled('override_duplicate') || $request->override_duplicate !== '1')) {
            if ($request->filled('vehicle_id')) {
                $activeTransaction = \App\Services\ActiveTransactionService::checkActiveTransaction($request->vehicle_id);
                if ($activeTransaction) {
                    return back()->withErrors(['duplicate' => 'This vehicle already has an active ' . $activeTransaction['stage'] . ' (' . $activeTransaction['reference_number'] . ').'])->withInput();
                }
            }
        }

        DB::beginTransaction();
        try {
            // Guarantee a free estimate number. estimates.estimate_number has a DB
            // UNIQUE index; because the model soft-deletes, a DELETED quotation still
            // reserves its number. If the submitted/suggested number is taken by any
            // row (incl. trashed), bump to the next free one so the insert never
            // fails and the form never silently bounces back.
            $num = $validated['estimate_number'] ?? null;
            if (empty($num) || Estimate::withTrashed()->where('estimate_number', $num)->exists()) {
                $seq = Estimate::withTrashed()
                    ->where('estimate_number', 'like', 'EST-' . now()->format('Ymd') . '-%')
                    ->count();
                do {
                    $seq++;
                    $num = 'EST-' . now()->format('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
                } while (Estimate::withTrashed()->where('estimate_number', $num)->exists());
            }
            $validated['estimate_number'] = $num;

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

            // Check for duplicate vehicle in active transactions
            $vehicleId = $validated['vehicle_id'];
            
            $archivedInspectionIds = \App\Models\Archive::where('archivable_type', 'App\\Models\\VehicleInspection')
                ->pluck('archivable_id')->toArray();
            $archivedAppointmentIds = [];
            if (!empty($archivedInspectionIds)) {
                $archivedAppointmentIds = \App\Models\VehicleInspection::withTrashed()->whereIn('id', $archivedInspectionIds)
                    ->where('vehicle_id', $vehicleId)
                    ->whereNotNull('appointment_id')
                    ->pluck('appointment_id')->toArray();
            }

            if (!$launchedFromInspection) {
            $existingAppointment = Appointment::where('vehicle_id', $vehicleId)
                ->whereIn('appointment_status', ['scheduled', 'checked_in', 'in_progress'])
                ->whereNull('deleted_at')
                ->where(function($q) use ($archivedAppointmentIds) {
                    if (!empty($archivedAppointmentIds)) {
                        $q->whereNotIn('id', $archivedAppointmentIds);
                    }
                })
                ->first();
            
            if ($existingAppointment) {
                DB::rollBack();
                return back()->withErrors([
                    'vehicle_id' => 'This vehicle already has an active Appointment (' . $existingAppointment->appointment_number . ').'
                ])->withInput();
            }
            }
            
            $existingJobOrder = JobOrder::where('vehicle_id', $vehicleId)
                ->whereIn('job_order_status', ['pending', 'repairing', 'waiting_parts'])
                ->whereNull('deleted_at')
                ->first();
            
            if ($existingJobOrder) {
                DB::rollBack();
                return back()->withErrors([
                    'vehicle_id' => 'This vehicle already has an active Work Order (' . ($existingJobOrder->job_order_number ?? '#' . $existingJobOrder->id) . ').'
                ])->withInput();
            }
            
            $existingEstimate = Estimate::where('vehicle_id', $vehicleId)
                ->whereIn('status', ['draft', 'pending', 'sent'])
                ->whereNull('deleted_at');
            
            if (!$launchedFromInspection && $existingEstimate->exists()) {
                DB::rollBack();
                return back()->withErrors([
                    'vehicle_id' => 'This vehicle already has an active Estimate (' . ($existingEstimate->first()->estimate_number ?? '#' . $existingEstimate->first()->id) . ').'
                ])->withInput();
            }
            
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
                'inspection_id' => $validated['inspection_id'] ?? null,
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
                    'total_price' => $item['line_total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)),
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => 'percentage',
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $item['subtotal'] ?? 0,
                    'sort_order' => $item['sort_order'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('estimates.show', $estimate)
                ->with('success', 'Repair Quotation #' . $estimate->estimate_number . ' created successfully.');

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
        $estimate->load(['customer', 'vehicle', 'items', 'itemGroups.items', 'user', 'serviceAdvisor', 'jobOrder']);

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
        // Count WITH trashed (soft-deleted rows keep their number in the DB UNIQUE index).
        $lastNum = Estimate::withTrashed()
            ->where('estimate_number', 'like', 'EST-' . now()->format('Ymd') . '-%')
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
        $customerHistory = Estimate::where('customer_id', $estimate->customer_id)
            ->where('id', '!=', $estimate->id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $selectedCustomer = $estimate->customer;
        $selectedVehicle = $estimate->vehicle;

        // Repair Quotation Edit page = the Findings editor of the linked Repair Order.
        // Customer / Vehicle / service items are intentionally NOT editable here.
        $inspection = null;
        if ($estimate->inspection_id) {
            $inspection = \App\Models\VehicleInspection::with([
                'inspectionFindings.group',
                'findingGroups.findings',
                'repairOrderPayments.uploadedBy',
                'repairOrderPayments.verifiedBy',
            ])->find($estimate->inspection_id);
        }

        return view('estimates.edit', compact(
            'estimate', 'customers', 'advisors', 'lastNum', 'inventoryItems', 'inventoryItemsJson',
            'selectedCustomer', 'selectedVehicle', 'customerVehicles', 'customerHistory', 'inspection'
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
            'service_type.*' => 'string|in:' . implode(',', \App\Models\ServiceType::keys()),
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

            // Preserve group links + customer decisions across item re-save.
            // Snapshot by sort_order (stable ordering used by the form).
            $linkSnapshot = $estimate->items()->get()
                ->mapWithKeys(function ($i) {
                    return [(string) $i->sort_order => [
                        'group_id' => $i->group_id,
                        'item_status' => $i->item_status,
                    ]];
                });

            // Delete old items and recreate
            $estimate->items()->delete();
            foreach ($items as $item) {
                $sortOrder = $item['sort_order'] ?? 0;
                $saved = $estimate->items()->create([
                    'estimate_id' => $estimate->id,
                    'item_name' => $item['description'] ?? '',
                    'description' => $item['description'] ?? '',
                    'category' => $item['category'] ?? 'parts',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'total_price' => $item['line_total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)),
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => 'percentage',
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $item['subtotal'] ?? 0,
                    'sort_order' => $sortOrder,
                ]);

                // Re-apply prior grouping / decision when the row position is unchanged
                $prev = $linkSnapshot->get((string) $sortOrder);
                if ($prev && ($prev['group_id'] || ($prev['item_status'] && $prev['item_status'] !== 'quoted'))) {
                    $saved->update([
                        'group_id' => $prev['group_id'],
                        'item_status' => $prev['item_status'] ?? 'quoted',
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('estimates.show', $estimate)
                ->with('success', 'Repair Quotation #' . $estimate->estimate_number . ' updated successfully.');

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
            ->with('success', 'Repair Quotation #' . $estimate->estimate_number . ' archived.');
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
                ->with('success', 'Repair Quotation #' . $estimate->estimate_number . ' sent to customer.');
        }

        return back()->with('error', 'Estimate must be in Draft status to send.');
    }

    /**
     * Approve estimate.
     */
    /**
     * Inline status change from the Repair Quotations list (dropdown).
     */
    public function updateStatus(Request $request, Estimate $estimate)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(Estimate::STATUSES)),
        ]);

        $status = $validated['status'];
        $data = ['status' => $status];

        // Keep sent_at in sync the same way the rest of the flow does.
        if ($status === 'sent' && !$estimate->sent_at) {
            $data['sent_at'] = now();
        }
        if ($status === 'approved' && !$estimate->approved_at) {
            $data['approved_at'] = now();
            $data['approved_by'] = auth()->id();
        }

        $estimate->update($data);

        $label = Estimate::STATUSES[$status] ?? ucfirst($status);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status'  => $status,
                'label'   => $label,
            ]);
        }

        return back()->with('success', 'Repair Quotation #' . $estimate->estimate_number . ' status updated to ' . $label . '.');
    }

    public function approve(Request $request, Estimate $estimate)
    {
        if (in_array($estimate->status, ['sent', 'viewed'])) {
            $estimate->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            return redirect()->route('estimates.show', $estimate)
                ->with('success', 'Repair Quotation #' . $estimate->estimate_number . ' approved.');
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
                ->with('success', 'Repair Quotation #' . $estimate->estimate_number . ' rejected.');
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
    public function convertToJobOrder(Estimate $estimate)
    {
        if ($estimate->status !== 'approved') {
            return back()->with('error', 'Only approved estimates can be converted.');
        }

        DB::beginTransaction();
        try {
            $wo = \App\Models\JobOrder::create([
                'customer_id' => $estimate->customer_id,
                'vehicle_id' => $estimate->vehicle_id,
                'estimate_id' => $estimate->id,
                'status' => 'pending',
                'notes' => 'Converted from Estimate #' . $estimate->estimate_number,
                'user_id' => auth()->id(),
            ]);

            $estimate->update(['status' => 'converted']);

            DB::commit();
            return redirect()->route('job-orders.show', $wo)
                ->with('success', 'Estimate converted to Work Order #' . $wo->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * Promote a Repair Quotation into a Repair Order.
     *
     * The quotation is the pre-approval stage; once the customer proceeds the
     * job moves back into the workshop as a Repair Order.
     *
     * Since 2026-09-25 (per Andrew) *every* promotion from a quotation issues a
     * BRAND-NEW Repair Order — the RO the quotation was originally created from
     * is never re-activated/re-opened, whether it is finished or still open.
     * The quotation's customer/vehicle + items are copied across as findings and
     * the quotation is re-linked to the new RO. Any payments already recorded on
     * the old RO's ledger (keyed by the RO) are moved to the new RO so the
     * customer's balance follows the job.
     */
    public function convertToRepairOrder(Request $request, Estimate $estimate)
    {
        if (in_array($estimate->status, ['converted_to_repair_order', 'converted_to_job_order', 'converted'], true)) {
            return back()->with('error', 'This quotation has already been converted.');
        }
        if ($estimate->status === 'rejected') {
            return back()->with('error', 'A rejected quotation cannot be moved to a Repair Order.');
        }

        $validated = $request->validate([
            'repair_status' => 'nullable|string|in:' . implode(',', array_keys(VehicleInspection::REPAIR_STATUSES)),
        ]);
        $repairStatus = $validated['repair_status'] ?? 'received';

        // The old RO the quotation was created from (if any). It is never
        // re-opened — kept only as the source of the quotation's lines + ledger.
        // A linked quotation is priced from that RO's findings, not from
        // estimate_items, so that is what we guard and copy.
        $linkedInspection = $estimate->inspection_id
            ? VehicleInspection::with(['inspectionFindings.group', 'findingGroups'])->find($estimate->inspection_id)
            : null;

        // Only THIS quotation's own findings travel to the new RO. A quotation
        // created during a repair (after the source RO was locked) owns just the
        // findings discovered then — the original approved ones stay on the
        // existing Repair Order.
        $quotedFindings = $estimate->quotedFindings();

        // Prices must be complete before the job becomes a Repair Order: once
        // promoted, these amounts are the fixed figures for the RO. Block when a
        // pursued line is still missing its parts price and/or labor.
        if ($linkedInspection) {
            $gaps = $linkedInspection->pricingGaps();
            if (! empty($gaps)) {
                $lines = collect($gaps)
                    ->map(fn ($g) => $g['label'] . ' — missing: ' . implode(' + ', $g['missing']) . ' price')
                    ->implode('; ');
                return back()->withErrors([
                    'pricing' => 'Hindi pa ma-proceed sa Repair Order — kulang ang mga presyo ng parts/labor: ' . $lines . '.',
                ])->withInput();
            }
        } else {
            $estimate->loadMissing(['items.group']);
            $itemGaps = $estimate->items->filter(fn ($i) => (float) $i->unit_price <= 0);
            if ($itemGaps->isNotEmpty()) {
                $lines = $itemGaps->map(fn ($i) => ($i->item_name ?: ('Item #' . $i->id)))->implode('; ');
                return back()->withErrors([
                    'pricing' => 'Hindi pa ma-proceed sa Repair Order — kulang ang parts price ng: ' . $lines . '.',
                ])->withInput();
            }
        }

        // The RO's own intake lines (Job Description + Parts/Supplies) — this is
        // what `repair_total` and the printed slip read, so the promoted RO must
        // carry them or it would show ₱0 (and a bogus ₱0 balance).
        [$jdItems, $partsItems] = $this->repairLinesFromQuotation($estimate, $linkedInspection, $quotedFindings);

        DB::beginTransaction();
        try {
            // Always a NEW Repair Order — never re-activate the old one.
            $inspection = VehicleInspection::create([
                'reference_number' => VehicleInspection::generateReferenceNumber(),
                'appointment_id' => $estimate->appointment_id,
                'customer_id' => $estimate->customer_id,
                'vehicle_id' => $estimate->vehicle_id,
                'service_advisor_id' => $estimate->service_advisor_id,
                'service_type' => $estimate->service_type,
                'inspection_type' => 'pre_service',
                'inspection_status' => 'in_progress',
                'repair_status' => $repairStatus,
                'source' => 'quotation',
                // Fixed from the quotation — lock the findings board.
                'findings_locked_at' => now(),
                'inspection_name' => 'Repair Order for ' . ($estimate->customer->full_name ?? 'Customer'),
                'customer_concerns' => $estimate->getRawOriginal('customer_notes') ?: $estimate->notes,
                'inspection_started_at' => now(),
                'date_received' => now()->toDateString(),
                // The RO's own intake lines (Job Description labor + Parts) so the
                // Repair Order price/balance/slip match the approved quotation.
                'job_description_items' => $jdItems,
                'parts_items' => $partsItems,
                // Fresh intake: the odometer is re-read on arrival (the client is
                // coming back with a higher reading), so start it at zero.
                'vehicle_mileage' => 0,
                'created_by' => auth()->id(),
                'requires_customer_approval' => 1,
                'customer_approved' => 1,
                'customer_approved_at' => now(),
            ]);

            // Carry the quotation's lines across as findings so the new Repair
            // Order shows the same parts/labour the customer approved.
            if ($linkedInspection) {
                // Linked quotation — copy only the findings this quotation owns
                // (its scoped set) and their groups, remapping group ids onto
                // the new RO.
                $groupMap = [];
                foreach ($linkedInspection->findingGroups as $g) {
                    if (! $quotedFindings->contains('group_id', $g->id)) {
                        continue;
                    }
                    $newGroup = \App\Models\InspectionFindingGroup::create([
                        'inspection_id' => $inspection->id,
                        'name' => $g->name,
                        'auto_name' => $g->auto_name,
                        'labor_cost' => $g->labor_cost,
                        'sort_order' => $g->sort_order,
                    ]);
                    $groupMap[$g->id] = $newGroup->id;
                }

                $sort = 0;
                foreach ($quotedFindings as $f) {
                    \App\Models\InspectionFinding::create([
                        'inspection_id' => $inspection->id,
                        'group_id' => $f->group_id ? ($groupMap[$f->group_id] ?? null) : null,
                        'category' => $f->category,
                        'issue_title' => $f->issue_title ?: ($f->part_name ?: 'Quotation item'),
                        'part_name' => $f->part_name,
                        'detailed_notes' => $f->detailed_notes,
                        'remarks' => $f->remarks,
                        'severity' => $f->severity,
                        'quantity' => $f->quantity ?: 1,
                        'unit_price' => $f->unit_price ?: 0,
                        'recommended_action' => $f->recommended_action,
                        'estimated_urgency' => $f->estimated_urgency,
                        'estimated_cost' => $f->estimated_cost,
                        'photo_path' => $f->photo_path,
                        'sort_order' => $f->sort_order ?? $sort,
                        'is_quotation_added' => 1,
                        'is_linked_to_estimate' => 1,
                        'is_declined' => (bool) $f->is_declined,
                    ]);
                    $sort++;
                }
            } else {
                // Standalone quotation — build the lines from its own items.
                $sort = 0;
                foreach ($estimate->items as $item) {
                    \App\Models\InspectionFinding::create([
                        'inspection_id' => $inspection->id,
                        'category' => in_array($item->category, ['parts', 'materials']) ? 'Parts' : 'Labor',
                        'issue_title' => $item->item_name ?: ($item->description ?: 'Quotation item'),
                        'part_name' => $item->item_name,
                        'detailed_notes' => $item->description,
                        'quantity' => $item->quantity ?: 1,
                        'unit_price' => $item->unit_price ?: 0,
                        'sort_order' => $sort++,
                        'is_quotation_added' => 1,
                        'is_linked_to_estimate' => 1,
                    ]);
                }
            }

            // Hand the payment ledger over to the new RO so the customer's
            // balance follows the job. The old RO itself is left otherwise intact.
            if ($linkedInspection && $linkedInspection->id !== $inspection->id) {
                \App\Models\RepairOrderPayment::where('vehicle_inspection_id', $linkedInspection->id)
                    ->update(['vehicle_inspection_id' => $inspection->id]);
            }

            $estimate->inspection_id = $inspection->id;

            $estimate->status = 'converted_to_repair_order';
            $estimate->approved_at = $estimate->approved_at ?: now();
            $estimate->approved_by = $estimate->approved_by ?: auth()->id();
            $estimate->save();

            DB::commit();

            return redirect()->route('inspections.show', $inspection)
                ->with('success', 'Quotation ' . $estimate->estimate_number . ' moved to Repair Order ' . $inspection->reference_label . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Estimate -> Repair Order conversion failed', [
                'estimate_id' => $estimate->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Conversion failed: ' . $e->getMessage());
        }
    }

    /**
     * Recalculate an estimate's stored totals from its items + group labour.
     * Called after any item/group mutation. With no groups the result is
     * identical to the original item-only computation.
     */
    private function recomputeTotals(Estimate $estimate): void
    {
        $estimate->load(['items', 'itemGroups']);
        $subtotal = 0; $partsTotal = 0; $laborTotal = 0; $discountAmount = 0; $taxTotal = 0;
        foreach ($estimate->items as $item) {
            $qty = (float) $item->quantity;
            $price = (float) $item->unit_price;
            $lineTotal = $qty * $price;
            $discPct = (float) $item->discount;
            $taxPct = (float) $item->tax_rate;
            $lineDiscount = $discPct > 0 ? $lineTotal * (min($discPct, 100) / 100) : 0;
            $afterDisc = $lineTotal - $lineDiscount;
            $lineTax = $taxPct > 0 ? $afterDisc * ($taxPct / 100) : 0;
            $lineSubtotal = $afterDisc + $lineTax;
            $subtotal += $afterDisc;
            $discountAmount += $lineDiscount;
            $taxTotal += $lineTax;
            if (in_array($item->category, ['parts', 'materials'])) { $partsTotal += $lineSubtotal; }
            else { $laborTotal += $lineSubtotal; }
        }
        // Shared labour (one price per group)
        $groupLabor = (float) $estimate->itemGroups->sum('labor_cost');
        $laborTotal += $groupLabor;
        $subtotal += $groupLabor;

        $discType = $estimate->discount_type;
        $discVal = (float) $estimate->discount_value;
        $globalDiscount = 0;
        if ($discType === 'percentage' && $discVal > 0) { $globalDiscount = $subtotal * (min($discVal, 100) / 100); }
        elseif ($discType === 'fixed' && $discVal > 0) { $globalDiscount = min($discVal, $subtotal); }

        $totalDiscount = $discountAmount + $globalDiscount;
        $grandTotal = $subtotal - $globalDiscount + $taxTotal;

        $estimate->update([
            'subtotal' => $subtotal,
            'parts_total' => $partsTotal,
            'labor_total' => $laborTotal,
            'discount_amount' => $totalDiscount,
            'tax_total' => $taxTotal,
            'total_amount' => $grandTotal,
            'balance_remaining' => max(0, $grandTotal - (float) $estimate->deposit_required),
        ]);
    }

    /**
     * Build the Job Description + Parts/Supplies rows for the Repair Order that
     * a quotation is promoted into. A linked quotation is priced from its RO's
     * findings + shared labor groups; a standalone quotation from its own items.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    protected function repairLinesFromQuotation(Estimate $estimate, ?VehicleInspection $linked, ?\Illuminate\Support\Collection $scopedFindings = null): array
    {
        $jd = [];
        $parts = [];

        if ($linked) {
            // Only the findings this quotation owns are priced onto the new RO.
            return VehicleInspection::buildRepairLinesFromFindings(
                $scopedFindings ?? $linked->inspectionFindings,
                $linked->findingGroups
            );
        }

        $estimate->loadMissing('items');
        foreach ($estimate->items as $it) {
            $qty = (float) ($it->quantity ?: 1);
            $unit = (float) ($it->unit_price ?? 0);
            if (in_array($it->category, ['parts', 'materials'], true)) {
                $parts[] = [
                    'description' => $it->item_name ?: 'Part',
                    'qty' => $qty,
                    'unit_price' => $unit,
                    'cost' => $qty * $unit,
                ];
            } else {
                $jd[] = [
                    'description' => $it->item_name ?: ($it->description ?: 'Labor'),
                    'mh' => null,
                    'unit_price' => $unit,
                    'labor_cost' => $qty * $unit,
                ];
            }
        }

        return [$jd, $parts];
    }

    /**
     * Create an item group (shared labour) on a Repair Quotation.
     */
    public function storeGroup(Request $request, Estimate $estimate)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:120',
            'labor_cost' => 'nullable|numeric|min:0',
        ]);
        $group = $estimate->itemGroups()->create([
            'name' => $data['name'] ?? 'Group',
            'labor_cost' => $data['labor_cost'] ?? 0,
            'sort_order' => ((int) $estimate->itemGroups()->max('sort_order')) + 1,
        ]);
        $this->recomputeTotals($estimate->fresh());
        return response()->json(['success' => true, 'group' => $group->fresh()]);
    }

    /**
     * Update an item group's name and/or shared labour price.
     */
    public function updateGroup(Request $request, EstimateItemGroup $group)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:120',
            'labor_cost' => 'nullable|numeric|min:0',
        ]);
        $upd = [];
        if ($request->has('name')) { $upd['name'] = $data['name'] ?? 'Group'; }
        if ($request->has('labor_cost')) { $upd['labor_cost'] = $data['labor_cost'] ?? 0; }
        if ($upd) { $group->update($upd); }
        $this->recomputeTotals($group->estimate->fresh());
        return response()->json(['success' => true, 'group' => $group->fresh()]);
    }

    /**
     * Delete a group (its items are kept, just un-grouped).
     */
    public function destroyGroup(EstimateItemGroup $group)
    {
        $estimate = $group->estimate;
        $group->items()->update(['group_id' => null]);
        $group->delete();
        $this->recomputeTotals($estimate->fresh());
        return response()->json(['success' => true]);
    }

    /**
     * Move an item into (or out of) a group.
     */
    public function assignItemGroup(Request $request, EstimateItem $item)
    {
        $data = $request->validate(['group_id' => 'nullable|exists:estimate_item_groups,id']);
        $gid = $data['group_id'] ?? null;
        if ($gid) {
            // guard: group must belong to the same estimate
            $ok = EstimateItemGroup::where('id', $gid)->where('estimate_id', $item->estimate_id)->exists();
            if (!$ok) { return response()->json(['success' => false, 'message' => 'Group does not belong to this quotation.'], 422); }
        }
        $item->update(['group_id' => $gid]);
        $this->recomputeTotals($item->estimate->fresh());
        return response()->json(['success' => true, 'item' => $item->fresh()]);
    }

    /**
     * Set a single line's customer decision (partial acceptance).
     */
    public function updateItemStatus(Request $request, EstimateItem $item)
    {
        $data = $request->validate(['item_status' => 'required|in:quoted,accepted,rejected,deferred']);
        $item->update(['item_status' => $data['item_status']]);
        return response()->json(['success' => true, 'item' => $item->fresh()]);
    }

    /**
     * Re-send (new revision) — bumps the version and re-stamps sent_at.
     */
    public function resend(Estimate $estimate)
    {
        $estimate->update([
            'status' => 'sent',
            'sent_at' => now(),
            'version' => ((int) $estimate->version) + 1,
        ]);
        return back()->with('success', 'Repair Quotation re-sent (v' . $estimate->version . ').');
    }

    /**
     * Print an estimate.
     */
    public function print(Estimate $estimate)
    {
        $estimate->load(['customer', 'vehicle', 'items']);
        return view('estimates.show', compact('estimate'));
    }

    /**
     * Supplier Quotation — a plain, copy-paste friendly list of the quotation's
     * parts grouped by their repair category (from the linked Repair Order's
     * findings), numbered inside each category. Meant to be copied straight into
     * a chat/Messenger message to a parts supplier.
     */
    public function supplierQuotation(Estimate $estimate)
    {
        $estimate->load(['customer', 'vehicle', 'items', 'inspection.inspectionFindings']);

        $ordered = []; // CATEGORY => ['label' => CATEGORY, 'items' => [part, part, ...]]

        // The Repair Quotation is priced live off the linked Repair Order's
        // findings, so the supplier list must read those same findings — NOT the
        // stored `estimate_items` snapshot (which is frozen at creation time and
        // would miss parts added to the RO afterwards).
        $findings = $estimate->quotedFindings()
            ->filter(fn ($f) => ! $f->is_declined)
            ->values();

        if ($findings->isNotEmpty()) {
            foreach ($findings as $f) {
                $name = trim((string) ($f->issue_title ?: $f->part_name));
                if ($name === '') {
                    continue;
                }
                $category = trim((string) $f->category) ?: 'OTHERS';
                $key = strtoupper($category);
                if (!isset($ordered[$key])) {
                    $ordered[$key] = ['label' => $key, 'items' => []];
                }
                // Parts only — findings carry the remark (LEAK/DAMAGE) in a
                // separate column, so `issue_title` is already clean.
                $ordered[$key]['items'][] = $name;
            }
        } else {
            // Quotation not linked to a Repair Order — fall back to the stored
            // line items, grouped by their own category enum.
            foreach ($estimate->items->sortBy('sort_order') as $item) {
                $name = trim((string) ($item->item_name ?: $item->description));
                if ($name === '') {
                    continue;
                }
                $name = trim(preg_replace('/\s*\(.*\)\s*$/', '', $name)) ?: $name;
                $key = strtoupper(ucfirst($item->category ?: 'Others'));
                if (!isset($ordered[$key])) {
                    $ordered[$key] = ['label' => $key, 'items' => []];
                }
                $ordered[$key]['items'][] = $name;
            }
        }

        return view('estimates.supplier-quotation', compact('estimate', 'ordered'));
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

    /**
     * Estimates statistics page (restored from git history — method was lost
     * in a botched refactor but the route + view still exist).
     */
    public function statistics()
    {
        // Get overall statistics
        $totalEstimates = Estimate::count();
        $totalValue = Estimate::sum('total_amount') ?? 0;
        $avgValue = $totalEstimates > 0 ? $totalValue / $totalEstimates : 0;

        // Get status breakdown
        $statusBreakdown = Estimate::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Get monthly statistics (last 6 months)
        $monthlyStats = Estimate::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as count,
                SUM(total_amount) as total
            ')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get top customers by estimate count
        $topCustomers = Estimate::with('customer')
            ->selectRaw('customer_id, COUNT(*) as estimate_count, SUM(total_amount) as total_value')
            ->groupBy('customer_id')
            ->orderBy('estimate_count', 'desc')
            ->limit(10)
            ->get();

        // Estimates don't have technician_id, so this stays empty for now
        $technicianStats = collect();

        return view('estimates.statistics', compact(
            'totalEstimates',
            'totalValue',
            'avgValue',
            'statusBreakdown',
            'monthlyStats',
            'topCustomers',
            'technicianStats'
        ));
    }
}
