<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Customer;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payment::with(['invoice.customer'])
            ->orderBy('payment_date', 'desc')
            ->paginate(20);
        
        return view('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $invoices = Invoice::where('status', '!=', 'paid')
            ->where('balance_due', '>', 0)
            ->with('customer')
            ->orderBy('due_date')
            ->get();
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        
        return view('payments.create', compact('invoices', 'customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required_without:customer_id|exists:invoices,id',
            'customer_id' => 'required_without:invoice_id|exists:customers,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,credit_card,bank_transfer,gcash,paymaya',
            'amount' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed,refunded',
        ]);

        $payment = Payment::create($validated);
        
        // If payment is linked to an invoice, update invoice amounts
        if ($payment->invoice_id) {
            $invoice = Invoice::find($payment->invoice_id);
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
        }
        
        return redirect()->route('payments.show', $payment->id)
            ->with('success', 'Payment recorded successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load(['invoice.customer', 'customer']);
        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        $invoices = Invoice::where('status', '!=', 'paid')
            ->where('balance_due', '>', 0)
            ->with('customer')
            ->orderBy('due_date')
            ->get();
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        
        return view('payments.edit', compact('payment', 'invoices', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,credit_card,bank_transfer,gcash,paymaya',
            'amount' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed,refunded',
        ]);

        // Store old values for invoice adjustment
        $oldInvoiceId = $payment->invoice_id;
        $oldAmount = $payment->amount;
        
        $payment->update($validated);
        
        // If payment was linked to an invoice, reverse the old amount
        if ($oldInvoiceId) {
            $oldInvoice = Invoice::find($oldInvoiceId);
            $newAmountPaid = max(0, $oldInvoice->amount_paid - $oldAmount);
            $newBalanceDue = $oldInvoice->total_amount - $newAmountPaid;
            
            $status = 'partial';
            if ($newBalanceDue <= 0) {
                $status = 'paid';
            } elseif ($oldInvoice->due_date < now()) {
                $status = 'overdue';
            }
            
            $oldInvoice->update([
                'amount_paid' => $newAmountPaid,
                'balance_due' => $newBalanceDue,
                'status' => $status,
            ]);
        }
        
        // If payment is now linked to an invoice, apply the new amount
        if ($payment->invoice_id) {
            $invoice = Invoice::find($payment->invoice_id);
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
        }
        
        return redirect()->route('payments.show', $payment->id)
            ->with('success', 'Payment updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        // If payment is linked to an invoice, reverse the amount
        if ($payment->invoice_id) {
            $invoice = Invoice::find($payment->invoice_id);
            $newAmountPaid = max(0, $invoice->amount_paid - $payment->amount);
            $newBalanceDue = $invoice->total_amount - $newAmountPaid;
            
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
        }
        
        $payment->delete();
        
        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully!');
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(Payment $payment)
    {
        $payment->update(['status' => 'completed']);
        
        return redirect()->route('payments.show', $payment->id)
            ->with('success', 'Payment marked as completed!');
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(Payment $payment)
    {
        $payment->update(['status' => 'failed']);
        
        // If payment was linked to an invoice, reverse the amount
        if ($payment->invoice_id) {
            $invoice = Invoice::find($payment->invoice_id);
            $newAmountPaid = max(0, $invoice->amount_paid - $payment->amount);
            $newBalanceDue = $invoice->total_amount - $newAmountPaid;
            
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
        }
        
        return redirect()->route('payments.show', $payment->id)
            ->with('success', 'Payment marked as failed! Amount reversed from invoice.');
    }

    /**
     * Refund payment
     */
    public function refund(Payment $payment)
    {
        $payment->update(['status' => 'refunded']);
        
        // If payment was linked to an invoice, reverse the amount
        if ($payment->invoice_id) {
            $invoice = Invoice::find($payment->invoice_id);
            $newAmountPaid = max(0, $invoice->amount_paid - $payment->amount);
            $newBalanceDue = $invoice->total_amount - $newAmountPaid;
            
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
        }
        
        return redirect()->route('payments.show', $payment->id)
            ->with('success', 'Payment refunded! Amount reversed from invoice.');
    }

    /**
     * Print payment receipt
     */
    public function print(Payment $payment)
    {
        $payment->load(['invoice.customer', 'customer']);
        return view('payments.print', compact('payment'));
    }

    /**
     * Get payment statistics
     */
    public function statistics()
    {
        $totalPayments = Payment::count();
        $totalAmount = Payment::sum('amount');
        
        $paymentMethods = Payment::selectRaw('payment_method, count(*) as count, sum(amount) as total')
            ->groupBy('payment_method')
            ->get();
        
        $statusCounts = Payment::selectRaw('status, count(*) as count, sum(amount) as total')
            ->groupBy('status')
            ->get();
        
        // Recent payments (last 30 days)
        $recentPayments = Payment::where('payment_date', '>=', now()->subDays(30))
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();
        
        // Daily payment totals for last 7 days
        $dailyTotals = Payment::where('payment_date', '>=', now()->subDays(7))
            ->selectRaw('DATE(payment_date) as date, sum(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('payments.statistics', compact(
            'totalPayments',
            'totalAmount',
            'paymentMethods',
            'statusCounts',
            'recentPayments',
            'dailyTotals'
        ));
    }

    /**
     * Get payments by date range
     */
    public function byDateRange(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $payments = Payment::whereBetween('payment_date', [$validated['start_date'], $validated['end_date']])
            ->with(['invoice.customer', 'customer'])
            ->orderBy('payment_date', 'desc')
            ->get();
        
        $totalAmount = $payments->sum('amount');
        
        return view('payments.date-range', compact('payments', 'totalAmount', 'validated'));
    }
}