<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'customer', 'paymentMethod', 'receiver'])
            ->latest();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('start_date')) {
            $query->where('payment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('payment_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('invoice', function($q) use ($search) {
                      $q->where('invoice_number', 'LIKE', "%{$search}%");
                  });
            });
        }

        $payments = $query->paginate(20);

        $totalAmount = $payments->sum('amount');
        $paymentMethods = PaymentMethod::active()->get();

        return view('payments.index', compact('payments', 'totalAmount', 'paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::orderBy('last_name')->get();
        $invoices = Invoice::where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->with('customer')
            ->orderBy('invoice_date', 'desc')
            ->get();
        $paymentMethods = PaymentMethod::active()->get();

        // Pre-select invoice if provided
        $selectedInvoice = $request->filled('invoice_id')
            ? Invoice::with('customer')->find($request->invoice_id)
            : null;

        // Pre-select customer if provided
        $selectedCustomer = $request->filled('customer_id')
            ? Customer::find($request->customer_id)
            : null;

        return view('payments.create', compact(
            'customers',
            'invoices',
            'paymentMethods',
            'selectedInvoice',
            'selectedCustomer'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'customer_id' => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Generate payment number
            $lastPayment = Payment::orderBy('id', 'desc')->first();
            $paymentNumber = 'PAY-' . str_pad(($lastPayment->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);

            // Get payment method details
            $paymentMethod = PaymentMethod::find($validated['payment_method_id']);

            // Create payment
            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $validated['invoice_id'],
                'customer_id' => $validated['customer_id'],
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method_id' => $validated['payment_method_id'],
                'payment_method_name' => $paymentMethod->name,
                'status' => 'completed',
                'notes' => $validated['notes'],
                'received_by' => auth()->id(),
            ]);

            // Update invoice if payment is linked to an invoice
            if ($validated['invoice_id']) {
                $invoice = Invoice::find($validated['invoice_id']);
                $invoice->addPayment($validated['amount'], $validated['payment_method_id'], $validated['notes']);
            }

            DB::commit();

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load(['invoice.customer', 'customer', 'paymentMethod', 'receiver']);
        
        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                ->with('error', 'Only pending payments can be edited.');
        }

        $customers = Customer::orderBy('last_name')->get();
        $invoices = Invoice::where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->with('customer')
            ->orderBy('invoice_date', 'desc')
            ->get();
        $paymentMethods = PaymentMethod::active()->get();

        return view('payments.edit', compact(
            'payment',
            'customers',
            'invoices',
            'paymentMethods'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                ->with('error', 'Only pending payments can be edited.');
        }

        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'customer_id' => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Get payment method details
            $paymentMethod = PaymentMethod::find($validated['payment_method_id']);

            // Update payment
            $payment->update([
                'invoice_id' => $validated['invoice_id'],
                'customer_id' => $validated['customer_id'],
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method_id' => $validated['payment_method_id'],
                'payment_method_name' => $paymentMethod->name,
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                ->with('error', 'Only pending payments can be deleted.');
        }

        DB::beginTransaction();

        try {
            // If payment is linked to an invoice, reverse the payment
            if ($payment->invoice_id) {
                $invoice = Invoice::find($payment->invoice_id);
                $invoice->amount_paid -= $payment->amount;
                $invoice->balance_due += $payment->amount;
                
                if ($invoice->amount_paid <= 0) {
                    $invoice->payment_status = 'pending';
                } elseif ($invoice->amount_paid < $invoice->total_amount) {
                    $invoice->payment_status = 'partial';
                }
                
                $invoice->save();
            }

            $payment->delete();

            DB::commit();

            return redirect()->route('payments.index')
                ->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be marked as completed.');
        }

        DB::beginTransaction();

        try {
            $payment->markAsCompleted();

            // Update invoice if payment is linked to an invoice
            if ($payment->invoice_id) {
                $invoice = Invoice::find($payment->invoice_id);
                $invoice->addPayment($payment->amount, $payment->payment_method_id, $payment->notes);
            }

            DB::commit();

            return back()->with('success', 'Payment marked as completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark payment as completed: ' . $e->getMessage());
        }
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(Payment $payment, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending payments can be marked as failed.');
        }

        $payment->markAsFailed($validated['reason']);

        return back()->with('success', 'Payment marked as failed.');
    }

    /**
     * Refund payment.
     */
    public function refund(Payment $payment, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($payment->status !== 'completed') {
            return back()->with('error', 'Only completed payments can be refunded.');
        }

        DB::beginTransaction();

        try {
            $payment->refund($validated['reason']);

            // If payment is linked to an invoice, reverse the payment
            if ($payment->invoice_id) {
                $invoice = Invoice::find($payment->invoice_id);
                $invoice->amount_paid -= $payment->amount;
                $invoice->balance_due += $payment->amount;
                
                if ($invoice->amount_paid <= 0) {
                    $invoice->payment_status = 'pending';
                    $invoice->status = 'sent';
                } elseif ($invoice->amount_paid < $invoice->total_amount) {
                    $invoice->payment_status = 'partial';
                }
                
                $invoice->save();
            }

            DB::commit();

            return back()->with('success', 'Payment refunded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to refund payment: ' . $e->getMessage());
        }
    }

    /**
     * Get payment statistics.
     */
    public function statistics(Request $request)
    {
        $query = Payment::where('status', 'completed');

        if ($request->filled('start_date')) {
            $query->where('payment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('payment_date', '<=', $request->end_date);
        }

        $totalPayments = $query->count();
        $totalAmount = $query->sum('amount');
        
        $paymentsByMethod = Payment::where('status', 'completed')
            ->select('payment_method_name', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method_name')
            ->orderBy('total', 'desc')
            ->get();

        $dailyPayments = Payment::where('status', 'completed')
            ->select(DB::raw('DATE(payment_date) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        $recentPayments = Payment::with(['invoice', 'customer', 'paymentMethod'])
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get();

        return view('payments.statistics', compact(
            'totalPayments',
            'totalAmount',
            'paymentsByMethod',
            'dailyPayments',
            'recentPayments'
        ));
    }

    /**
     * Process bulk payments.
     */
    public function bulkProcess(Request $request)
    {
        $validated = $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.id' => 'required|exists:payments,id',
            'action' => 'required|in:complete,fail',
            'reason' => 'nullable|string|max:500',
        ]);

        $completed = 0;
        $failed = 0;

        DB::beginTransaction();

        try {
            foreach ($validated['payments'] as $paymentData) {
                $payment = Payment::find($paymentData['id']);

                if ($payment->status !== 'pending') {
                    $failed++;
                    continue;
                }

                if ($validated['action'] === 'complete') {
                    $payment->markAsCompleted();
                    
                    // Update invoice if payment is linked to an invoice
                    if ($payment->invoice_id) {
                        $invoice = Invoice::find($payment->invoice_id);
                        $invoice->addPayment($payment->amount, $payment->payment_method_id, $payment->notes);
                    }
                    
                    $completed++;
                } else {
                    $payment->markAsFailed($validated['reason'] ?? 'Bulk processing - marked as failed');
                    $failed++;
                }
            }

            DB::commit();

            $message = "Processed {$completed} payments successfully.";
            if ($failed > 0) {
                $message .= " {$failed} payments could not be processed (not in pending status).";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process payments: ' . $e->getMessage());
        }
    }
}
