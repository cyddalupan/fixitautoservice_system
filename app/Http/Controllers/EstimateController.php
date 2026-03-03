<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estimate;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Inventory;

class EstimateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estimates = Estimate::with(['customer', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('estimates.index', compact('estimates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('is_active', true)->orderBy('last_name')->get();
        $vehicles = Vehicle::where('is_active', true)->orderBy('make')->get();
        $inventoryItems = Inventory::where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();
        
        // Pre-fill data from appointment or inspection
        $appointment = null;
        $inspection = null;
        $prefilledData = [];
        
        if ($request->has('appointment_id')) {
            $appointment = \App\Models\Appointment::with(['customer', 'vehicle'])->find($request->appointment_id);
            if ($appointment) {
                $prefilledData = [
                    'customer_id' => $appointment->customer_id,
                    'vehicle_id' => $appointment->vehicle_id,
                    'mileage' => $appointment->vehicle->current_mileage ?? null,
                    'notes' => $appointment->service_request,
                ];
            }
        }
        
        if ($request->has('inspection_id')) {
            $inspection = \App\Models\VehicleInspection::with(['appointment.customer', 'appointment.vehicle'])->find($request->inspection_id);
            if ($inspection && $inspection->appointment) {
                $prefilledData = [
                    'customer_id' => $inspection->customer_id,
                    'vehicle_id' => $inspection->vehicle_id,
                    'mileage' => $inspection->vehicle->current_mileage ?? null,
                    'notes' => $inspection->customer_concerns . "\n\n" . $inspection->recommended_services,
                ];
            }
        }
        
        // Get the last estimate number for auto-generation
        $lastEstimate = Estimate::orderBy('id', 'desc')->first();
        $lastEstimateNumber = $lastEstimate ? intval(substr($lastEstimate->estimate_number, -4)) : 0;
        
        return view('estimates.create-simple', compact('customers', 'vehicles', 'inventoryItems', 'appointment', 'inspection', 'prefilledData', 'lastEstimateNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'estimate_number' => 'required|unique:estimates,estimate_number',
            'estimate_date' => 'required|date',
            'expiry_date' => 'required|date|after:estimate_date',
            'notes' => 'nullable|string',
            'customer_notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'status' => 'required|in:draft,pending,approved,rejected,expired',
            'appointment_id' => 'nullable|exists:appointments,id',
            'inspection_id' => 'nullable|exists:vehicle_inspections,id',
        ]);

        // Calculate totals from items
        $subtotal = 0;
        $items = [];
        
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                if (!empty($item['item_name']) && !empty($item['quantity']) && !empty($item['unit_price'])) {
                    $itemTotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $itemTotal;
                    
                    $items[] = [
                        'inventory_id' => $item['inventory_id'] ?? null,
                        'item_name' => $item['item_name'],
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $itemTotal,
                    ];
                }
            }
        }
        
        // Calculate tax and total (assuming 0% tax for now)
        $taxRate = 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;
        
        // Add calculated fields to validated data
        $validated['subtotal'] = $subtotal;
        $validated['tax_rate'] = $taxRate;
        $validated['tax_amount'] = $taxAmount;
        $validated['total_amount'] = $totalAmount;
        
        // Use estimate_date for issue_date (they should be the same)
        $validated['issue_date'] = $validated['estimate_date'];
        
        $estimate = Estimate::create($validated);
        
        // Link to appointment if provided
        if ($request->has('appointment_id')) {
            $appointment = \App\Models\Appointment::find($request->appointment_id);
            if ($appointment) {
                $appointment->update(['appointment_status' => 'completed']);
            }
        }
        
        // Link to inspection if provided
        if ($request->has('inspection_id')) {
            $inspection = \App\Models\VehicleInspection::find($request->inspection_id);
            if ($inspection) {
                $inspection->update(['inspection_status' => 'completed']);
            }
        }
        
        // Add items if provided
        foreach ($items as $item) {
            $estimate->items()->create($item);
        }
        
        // Handle different save actions
        $action = $request->input('action', 'save_draft');
        
        if ($action === 'save_send') {
            // Update status to pending (sent to customer)
            $estimate->update(['status' => 'pending']);
            
            // Get email from request or use customer email
            $sendToEmail = $request->input('send_to_email', $estimate->customer->email);
            
            // Here you would typically send an email to the customer
            // For now, we'll just show success message
            
            return redirect()->route('estimates.show', $estimate->id)
                ->with('success', 'Estimate created and sent to ' . $sendToEmail . '!');
        } elseif ($action === 'save_print') {
            // Redirect to print/view page
            return redirect()->route('estimates.show', $estimate->id)
                ->with('success', 'Estimate saved! Click the Print button to print.');
        } else {
            // Default: save as draft
            return redirect()->route('estimates.show', $estimate->id)
                ->with('success', 'Estimate created successfully!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Estimate $estimate)
    {
        $estimate->load(['customer', 'vehicle', 'items.inventory']);
        return view('estimates.show', compact('estimate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Estimate $estimate)
    {
        $customers = Customer::where('is_active', true)->orderBy('last_name')->get();
        $vehicles = Vehicle::where('is_active', true)->orderBy('make')->get();
        $inventoryItems = Inventory::with('category')
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();
        
        $estimate->load('items.inventory');
        
        return view('estimates.edit', compact('estimate', 'customers', 'vehicles', 'inventoryItems'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Estimate $estimate)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'mileage' => 'nullable|integer',
            'labor_hours' => 'nullable|numeric|min:0',
            'labor_rate' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,pending,viewed,accepted,rejected,expired,sent,approved',
        ]);

        $estimate->update($validated);
        
        // Update items
        if ($request->has('items')) {
            $estimate->items()->delete();
            foreach ($request->items as $item) {
                // Save items that have either item_name or inventory_id
                if ((!empty($item['item_name']) || !empty($item['inventory_id'])) && !empty($item['quantity'])) {
                    $itemTotal = $item['quantity'] * ($item['unit_price'] ?? 0);
                    
                    $estimate->items()->create([
                        'inventory_id' => $item['inventory_id'] ?? null,
                        'item_name' => $item['item_name'] ?? 'Unknown Item',
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'] ?? 0,
                        'total_price' => $itemTotal,
                    ]);
                }
            }
        }
        
        return redirect()->route('estimates.show', $estimate->id)
            ->with('success', 'Estimate updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Estimate $estimate)
    {
        $estimate->delete();
        
        return redirect()->route('estimates.index')
            ->with('success', 'Estimate deleted successfully!');
    }

    /**
     * Approve an estimate
     */
    public function approve(Estimate $estimate)
    {
        $estimate->update(['status' => 'approved']);
        
        return redirect()->route('estimates.show', $estimate->id)
            ->with('success', 'Estimate approved! You can now create a work order from this estimate.');
    }

    /**
     * Reject an estimate
     */
    public function reject(Estimate $estimate)
    {
        $estimate->update(['status' => 'rejected']);
        
        return redirect()->route('estimates.show', $estimate->id)
            ->with('success', 'Estimate rejected.');
    }

    /**
     * Update estimate status
     */
    public function updateStatus(Request $request, Estimate $estimate)
    {
        $request->validate([
            'status' => 'required|in:draft,pending,approved,rejected,expired'
        ]);
        
        $estimate->update(['status' => $request->status]);
        
        return redirect()->route('estimates.show', $estimate->id)
            ->with('success', 'Estimate status updated to ' . $request->status);
    }

    /**
     * Convert estimate to work order
     */
    public function convertToWorkOrder(Estimate $estimate)
    {
        if ($estimate->status !== 'approved') {
            return redirect()->route('estimates.show', $estimate->id)
                ->with('error', 'Only approved estimates can be converted to work orders.');
        }
        
        // This would typically create a work order
        // For now, we'll just redirect to work orders with a message
        return redirect()->route('work-orders.create')
            ->with('success', 'Estimate ready for work order creation. Please fill in the work order details.')
            ->with('estimate_id', $estimate->id);
    }

    /**
     * Print estimate
     */
    public function print(Estimate $estimate)
    {
        $estimate->load(['customer', 'vehicle', 'items.inventory']);
        return view('estimates.print', compact('estimate'));
    }

    /**
     * Send estimate to customer
     */
    public function send(Estimate $estimate)
    {
        $estimate->update(['status' => 'pending']);
        
        // Here you would typically send an email to the customer
        // For now, we'll just update the status
        
        return redirect()->route('estimates.show', $estimate->id)
            ->with('success', 'Estimate sent to customer!');
    }
    
    /**
     * Display statistics for estimates.
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
        
        // Get estimates by technician (if assigned)
        // Note: Estimates don't have technician_id, so this is commented out for now
        // $technicianStats = Estimate::with('technician')
        //     ->selectRaw('technician_id, COUNT(*) as estimate_count, SUM(total_amount) as total_value')
        //     ->whereNotNull('technician_id')
        //     ->groupBy('technician_id')
        //     ->orderBy('estimate_count', 'desc')
        //     ->limit(10)
        //     ->get();
        
        $technicianStats = collect(); // Empty collection for now
        
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