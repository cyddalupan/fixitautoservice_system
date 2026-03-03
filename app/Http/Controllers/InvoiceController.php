<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\WorkOrder;
use App\Models\Estimate;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with(['customer', 'workOrder'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $workOrders = WorkOrder::where('status', 'completed')
            ->whereDoesntHave('invoice')
            ->orderBy('created_at', 'desc')
            ->get();
        $estimates = Estimate::where('status', 'approved')
            ->whereDoesntHave('workOrder.invoice')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('invoices.create', compact('customers', 'workOrders', 'estimates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'estimate_id' => 'nullable|exists:estimates,id',
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'balance_due' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'status' => 'required|in:draft,sent,partial,paid,overdue,cancelled',
        ]);

        $invoice = Invoice::create($validated);
        
        // Add items if provided
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                if (!empty($item['description']) && !empty($item['quantity'])) {
                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'taxable' => $item['taxable'] ?? false,
                    ]);
                }
            }
        }
        
        // Update work order status if linked
        if ($invoice->workOrder) {
            $invoice->workOrder->update(['invoice_status' => 'invoiced']);
        }
        
        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'workOrder', 'items', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $workOrders = WorkOrder::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $invoice->load(['items', 'payments']);
        
        return view('invoices.edit', compact('invoice', 'customers', 'workOrders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'invoice_number' => 'required|unique:invoices,invoice_number,' . $invoice->id,
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'balance_due' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'status' => 'required|in:draft,sent,partial,paid,overdue,cancelled',
        ]);

        $invoice->update($validated);
        
        // Update items
        if ($request->has('items')) {
            $invoice->items()->delete();
            foreach ($request->items as $item) {
                if (!empty($item['description']) && !empty($item['quantity'])) {
                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'taxable' => $item['taxable'] ?? false,
                    ]);
                }
            }
        }
        
        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // Update work order status if linked
        if ($invoice->workOrder) {
            $invoice->workOrder->update(['invoice_status' => null]);
        }
        
        $invoice->delete();
        
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }

    /**
     * Send invoice to customer
     */
    public function send(Invoice $invoice)
    {
        $invoice->update(['status' => 'sent']);
        
        // Here you would typically send an email to the customer
        // For now, we'll just update the status
        
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
            'amount_paid' => $invoice->total_amount,
            'balance_due' => 0,
        ]);
        
        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice marked as paid!');
    }

    /**
     * Print invoice
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'workOrder', 'items']);
        return view('invoices.print', compact('invoice'));
    }

    /**
     * Generate PDF invoice
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'workOrder', 'items']);
        
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