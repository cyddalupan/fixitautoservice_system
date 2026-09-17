<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Estimate;
use App\Models\Vehicle;
use App\Models\TaxRate;
use App\Models\Discount;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with(['customer', 'jobOrder'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        // Load all vehicles initially - we'll filter them with JavaScript
        $vehicles = Vehicle::orderBy('year', 'desc')->get();
        
        // Get work order ID from request if provided
        $selectedJobOrderId = $request->get('job_order_id');
        $selectedJobOrder = null;
        
        // Load work orders
        $jobOrders = JobOrder::where('job_order_status', 'completed')
            ->whereDoesntHave('invoice')  // Only show work orders without invoices
            ->orderBy('created_at', 'desc')
            ->get();
        
        // If a specific work order ID is provided, load it
        if ($selectedJobOrderId) {
            $selectedJobOrder = JobOrder::with(['customer', 'vehicle', 'jobOrderItems'])
                ->where('id', $selectedJobOrderId)
                ->where('job_order_status', 'completed')
                ->whereDoesntHave('invoice')
                ->first();
            
            if ($selectedJobOrder) {
                // Pre-select the customer and vehicle
                $preSelectedCustomer = $selectedJobOrder->customer;
                $preSelectedVehicle = $selectedJobOrder->vehicle;
            }
        }
        
        $estimates = Estimate::where('status', 'approved')
            ->whereDoesntHave('jobOrder.invoice')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get tax rates
        $taxRates = TaxRate::where('is_active', true)->orderBy('rate')->get();
        
        // Get discounts
        $discounts = Discount::where('is_active', true)->orderBy('name')->get();
        
        // Initialize selected variables as null
        $selectedCustomer = $preSelectedCustomer ?? null;
        $selectedVehicle = $preSelectedVehicle ?? null;
        $selectedJobOrder = $selectedJobOrder ?? null;
        
        return view('invoices.create', compact(
            'customers', 
            'vehicles',
            'taxRates',
            'discounts',
            'jobOrders', 
            'estimates',
            'selectedCustomer',
            'selectedVehicle',
            'selectedJobOrder'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'job_order_id' => 'nullable|exists:job_orders,id',
            'estimate_id' => 'nullable|exists:estimates,id',
            'invoice_number' => 'nullable|unique:invoices,invoice_number',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after:invoice_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'discount_id' => 'nullable|exists:discounts,id',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:service,parts,labor,fee,other',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Compute line totals from items
        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }
        $subtotal = round($subtotal, 2);

        // Tax from tax rate record
        $taxPercent = 0;
        if (!empty($validated['tax_rate_id'])) {
            $taxPercent = (float) TaxRate::find($validated['tax_rate_id'])?->rate ?? 0;
        }
        $taxAmount = round($subtotal * $taxPercent / 100, 2);

        // Discount from discount record
        $discountAmount = 0;
        if (!empty($validated['discount_id'])) {
            $discount = Discount::find($validated['discount_id']);
            if ($discount) {
                $discountAmount = $discount->type === 'percentage'
                    ? round($subtotal * (float) $discount->value / 100, 2)
                    : round((float) $discount->value, 2);
            }
        }

        $totalAmount = round($subtotal + $taxAmount - $discountAmount, 2);

        $invoice = Invoice::create([
            'customer_id' => $validated['customer_id'],
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'job_order_id' => $validated['job_order_id'] ?? null,
            'estimate_id' => $validated['estimate_id'] ?? null,
            'invoice_number' => $validated['invoice_number'] ?? $this->generateInvoiceNumber(),
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'subtotal' => $subtotal,
            'tax_rate' => $taxPercent,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'amount_paid' => 0,
            'balance_due' => $totalAmount,
            'status' => 'draft',
            'payment_status' => 'pending',
        ]);
        
        // Add items if provided
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'item_type' => $item['item_type'] ?? 'service',
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => round($item['quantity'] * $item['unit_price'], 2),
                    'taxable' => $item['taxable'] ?? false,
                ]);
            }
        }
        
        // Update work order status if linked
        if ($invoice->jobOrder) {
            $invoice->jobOrder->update(['invoice_status' => 'invoiced']);
        }
        
        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully!');
    }

    /**
     * Generate a unique invoice number.
     */
    protected function generateInvoiceNumber(): string
    {
        $number = 'INV-' . now()->format('Y') . '-' . str_pad((string) (Invoice::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT);
        while (Invoice::withTrashed()->where('invoice_number', $number)->exists()) {
            $number = 'INV-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        }
        return $number;
    }
    
    /**
     * Get vehicles by customer ID (AJAX endpoint)
     */
    public function getVehiclesByCustomer(Request $request, $customerId)
    {
        $vehicles = Vehicle::where('customer_id', $customerId)
            ->orderBy('year', 'desc')
            ->get();
        
        return response()->json($vehicles);
    }
    
    /**
     * Get work orders by customer ID (AJAX endpoint)
     */
    public function getJobOrdersByCustomer(Request $request, $customerId)
    {
        $jobOrders = JobOrder::where('customer_id', $customerId)
            ->where('job_order_status', 'completed')
            ->with('customer') // Include customer relationship for display
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($jobOrders);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        // Mark as viewed if not yet viewed
        if ($invoice->viewed_at === null) {
            $invoice->update(['viewed_at' => now()]);
        }
        
        $invoice->load(['customer', 'jobOrder', 'items', 'payments', 'serviceProgress']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        // Mark as viewed if not yet viewed
        if ($invoice->viewed_at === null) {
            $invoice->update(['viewed_at' => now()]);
        }
        
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $jobOrders = JobOrder::where('job_order_status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $invoice->load(['items', 'payments']);
        
        return view('invoices.edit', compact('invoice', 'customers', 'jobOrders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'job_order_id' => 'nullable|exists:job_orders,id',
            'estimate_id' => 'nullable|exists:estimates,id',
            'invoice_number' => 'nullable|unique:invoices,invoice_number,' . $invoice->id,
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after:invoice_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'discount_id' => 'nullable|exists:discounts,id',
            'items' => 'nullable|array',
            'items.*.item_type' => 'required|in:service,parts,labor,fee,other',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Recompute totals from items when provided
        if ($request->has('items')) {
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }
            $subtotal = round($subtotal, 2);

            $taxPercent = (float) $invoice->tax_rate;
            if (!empty($validated['tax_rate_id'])) {
                $taxPercent = (float) TaxRate::find($validated['tax_rate_id'])?->rate ?? $taxPercent;
            }
            $taxAmount = round($subtotal * $taxPercent / 100, 2);

            $discountAmount = 0;
            if (!empty($validated['discount_id'])) {
                $discount = Discount::find($validated['discount_id']);
                if ($discount) {
                    $discountAmount = $discount->type === 'percentage'
                        ? round($subtotal * (float) $discount->value / 100, 2)
                        : round((float) $discount->value, 2);
                }
            }

            $totalAmount = round($subtotal + $taxAmount - $discountAmount, 2);

            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'vehicle_id' => $validated['vehicle_id'] ?? $invoice->vehicle_id,
                'job_order_id' => $validated['job_order_id'] ?? $invoice->job_order_id,
                'invoice_number' => $validated['invoice_number'] ?? $invoice->invoice_number,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? $invoice->due_date,
                'notes' => $validated['notes'] ?? $invoice->notes,
                'terms' => $validated['terms'] ?? $invoice->terms,
                'subtotal' => $subtotal,
                'tax_rate' => $taxPercent,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance_due' => $totalAmount - $invoice->amount_paid,
            ]);

            // Replace items
            $invoice->items()->delete();
            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'item_type' => $item['item_type'] ?? 'service',
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => round($item['quantity'] * $item['unit_price'], 2),
                    'taxable' => $item['taxable'] ?? false,
                ]);
            }
        } else {
            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? $invoice->due_date,
                'notes' => $validated['notes'] ?? $invoice->notes,
                'terms' => $validated['terms'] ?? $invoice->terms,
            ]);
        }
        
        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.index')
                ->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->items()->delete();
        $invoice->forceDelete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted.');
    }

    /**
     * Send invoice to customer
     */
    public function send(Request $request, Invoice $invoice)
    {
        $request->validate([
            'delivery_method' => 'nullable|in:email,sms,print,portal',
        ]);

        $invoice->update([
            'status' => 'sent',
            'delivery_method' => $request->input('delivery_method', 'email'),
            'sent_at' => now(),
        ]);
        
        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice sent to customer!');
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice)
    {
        $invoice->update([
            'status' => 'paid',
            'payment_status' => 'paid',
            'amount_paid' => $invoice->total_amount,
            'balance_due' => 0,
            'paid_date' => now(),
        ]);
        
        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice marked as paid!');
    }

    /**
     * Cancel invoice
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $invoice->update([
            'status' => 'cancelled',
            'notes' => $request->input('reason') ? ($invoice->notes . "\nCancelled: " . $request->input('reason')) : $invoice->notes,
        ]);

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice cancelled.');
    }

    /**
     * Print invoice
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'jobOrder', 'items']);
        return view('invoices.print', compact('invoice'));
    }

    /**
     * Generate PDF invoice
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'jobOrder', 'items']);
        
        // In a real application, you would generate a PDF here
        // For now, we'll redirect to the print view
        return redirect()->route('invoices.print', $invoice->id);
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,credit_card,bank_transfer,gcash,paymaya',
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Create payment
        $payment = $invoice->payments()->create($validated);
        
        // Update invoice amounts
        $newAmountPaid = $invoice->amount_paid + $validated['amount'];
        $newBalanceDue = max(0, $invoice->total_amount - $newAmountPaid);
        
        $status = 'partial';
        if ($newBalanceDue <= 0) {
            $status = 'paid';
        } elseif ($invoice->due_date < now()) {
            $status = 'overdue';
        }
        
        $invoice->update([
            'amount_paid' => $newAmountPaid,
            'balance_due' => $newBalanceDue,
            'status' => $status,
        ]);
        
        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Payment recorded successfully!');
    }

    /**
     * Get invoice statistics
     */
    public function statistics()
    {
        $totalInvoices = Invoice::count();
        $totalAmount = Invoice::sum('total_amount');
        $totalPaid = Invoice::sum('amount_paid');
        $totalDue = Invoice::sum('balance_due');
        
        $statusCounts = Invoice::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        
        $overdueInvoices = Invoice::where('status', 'overdue')
            ->orWhere(function($query) {
                $query->where('status', 'sent')
                    ->where('due_date', '<', now());
            })
            ->count();
        
        return view('invoices.statistics', compact(
            'totalInvoices',
            'totalAmount',
            'totalPaid',
            'totalDue',
            'statusCounts',
            'overdueInvoices'
        ));
    }
}