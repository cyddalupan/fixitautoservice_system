<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\TaxRate;
use App\Models\Discount;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'vehicle'])
            ->latest();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('start_date')) {
            $query->where('invoice_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('invoice_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::orderBy('last_name')->get();
        $vehicles = Vehicle::orderBy('make')->get();
        $workOrders = WorkOrder::where('status', 'completed')->orderBy('created_at', 'desc')->get();
        $taxRates = TaxRate::active()->get();
        $discounts = Discount::active()->get();
        $paymentMethods = PaymentMethod::active()->get();

        // Pre-select customer if provided
        $selectedCustomer = $request->filled('customer_id') 
            ? Customer::find($request->customer_id)
            : null;

        // Pre-select vehicle if provided
        $selectedVehicle = $request->filled('vehicle_id')
            ? Vehicle::find($request->vehicle_id)
            : null;

        // Pre-select work order if provided
        $selectedWorkOrder = $request->filled('work_order_id')
            ? WorkOrder::with(['vehicle', 'customer'])->find($request->work_order_id)
            : null;

        return view('invoices.create', compact(
            'customers',
            'vehicles',
            'workOrders',
            'taxRates',
            'discounts',
            'paymentMethods',
            'selectedCustomer',
            'selectedVehicle',
            'selectedWorkOrder'
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
            'work_order_id' => 'nullable|exists:work_orders,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:service,parts,labor,fee',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'discount_id' => 'nullable|exists:discounts,id',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Generate invoice number
            $lastInvoice = Invoice::orderBy('id', 'desc')->first();
            $invoiceNumber = 'INV-' . str_pad(($lastInvoice->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);

            // Calculate subtotal from items
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            // Calculate tax
            $taxAmount = 0;
            if ($request->filled('tax_rate_id')) {
                $taxRate = TaxRate::find($request->tax_rate_id);
                $taxAmount = ($subtotal * $taxRate->rate) / 100;
            }

            // Calculate discount
            $discountAmount = $request->discount_amount ?? 0;
            if ($request->filled('discount_id')) {
                $discount = Discount::find($request->discount_id);
                $discountAmount = $discount->calculateDiscount($subtotal);
            }

            // Calculate total
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'work_order_id' => $validated['work_order_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance_due' => $totalAmount,
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
                'status' => 'draft',
                'payment_status' => 'pending',
            ]);

            // Create invoice items
            foreach ($validated['items'] as $itemData) {
                $invoice->items()->create([
                    'item_type' => $itemData['item_type'],
                    'item_name' => $itemData['item_name'],
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_amount' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'vehicle', 'workOrder', 'items', 'payments.paymentMethod', 'creator']);
        
        $paymentMethods = PaymentMethod::active()->get();
        
        return view('invoices.show', compact('invoice', 'paymentMethods'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $invoice->load(['items']);
        $customers = Customer::orderBy('last_name')->get();
        $vehicles = Vehicle::orderBy('make')->get();
        $workOrders = WorkOrder::where('status', 'completed')->orderBy('created_at', 'desc')->get();
        $taxRates = TaxRate::active()->get();
        $discounts = Discount::active()->get();

        return view('invoices.edit', compact(
            'invoice',
            'customers',
            'vehicles',
            'workOrders',
            'taxRates',
            'discounts'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:service,parts,labor,fee',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'discount_id' => 'nullable|exists:discounts,id',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Delete existing items
            $invoice->items()->delete();

            // Calculate subtotal from items
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            // Calculate tax
            $taxAmount = 0;
            if ($request->filled('tax_rate_id')) {
                $taxRate = TaxRate::find($request->tax_rate_id);
                $taxAmount = ($subtotal * $taxRate->rate) / 100;
            }

            // Calculate discount
            $discountAmount = $request->discount_amount ?? 0;
            if ($request->filled('discount_id')) {
                $discount = Discount::find($request->discount_id);
                $discountAmount = $discount->calculateDiscount($subtotal);
            }

            // Calculate total
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            // Update invoice
            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'work_order_id' => $validated['work_order_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance_due' => $totalAmount - $invoice->amount_paid,
                'notes' => $validated['notes'],
                'updated_by' => auth()->id(),
            ]);

            // Create new invoice items
            foreach ($validated['items'] as $itemData) {
                $invoice->items()->create([
                    'item_type' => $itemData['item_type'],
                    'item_name' => $itemData['item_name'],
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_amount' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Send invoice to customer.
     */
    public function send(Invoice $invoice, Request $request)
    {
        $validated = $request->validate([
            'delivery_method' => 'required|in:email,sms,print,portal',
        ]);

        // Update invoice status
        $invoice->update([
            'status' => 'sent',
            'delivery_method' => $validated['delivery_method'],
            'sent_at' => now(),
        ]);

        // TODO: Implement actual delivery logic
        // - Email: Send invoice PDF via email
        // - SMS: Send payment link via SMS
        // - Print: Mark as ready for printing
        // - Portal: Make available in customer portal

        return back()->with('success', 'Invoice sent successfully.');
    }

    /**
     * Mark invoice as paid.
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

        return back()->with('success', 'Invoice marked as paid.');
    }

    /**
     * Cancel invoice.
     */
    public function cancel(Invoice $invoice, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $invoice->update([
            'status' => 'cancelled',
            'notes' => ($invoice->notes ? $invoice->notes . "\n" : '') . 
                      "Cancelled: " . ($validated['reason'] ?? 'No reason provided'),
        ]);

        return back()->with('success', 'Invoice cancelled successfully.');
    }

    /**
     * Print invoice.
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'vehicle', 'workOrder', 'items', 'creator']);
        
        // TODO: Generate PDF for printing
        return view('invoices.print', compact('invoice'));
    }

    /**
     * Download invoice PDF.
     */
    public function download(Invoice $invoice)
    {
        $invoice->load(['customer', 'vehicle', 'workOrder', 'items', 'creator']);
        
        // TODO: Generate and return PDF file
        // $pdf = PDF::loadView('invoices.pdf', compact('invoice'));
        // return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
        
        return back()->with('info', 'PDF download feature coming soon.');
    }

    /**
     * Get invoice statistics.
     */
    public function statistics()
    {
        $totalInvoices = Invoice::count();
        $totalRevenue = Invoice::sum('total_amount');
        $totalPaid = Invoice::where('payment_status', 'paid')->sum('total_amount');
        $totalPending = Invoice::where('payment_status', 'pending')->sum('balance_due');
        $totalOverdue = Invoice::overdue()->sum('balance_due');
        
        $recentInvoices = Invoice::with('customer')
            ->latest()
            ->take(10)
            ->get();
        
        $topCustomers = Customer::withSum('invoices', 'total_amount')
            ->orderBy('invoices_sum_total_amount', 'desc')
            ->take(5)
            ->get();

        return view('invoices.statistics', compact(
            'totalInvoices',
            'totalRevenue',
            'totalPaid',
            'totalPending',
            'totalOverdue',
            'recentInvoices',
            'topCustomers'
        ));
    }
}
