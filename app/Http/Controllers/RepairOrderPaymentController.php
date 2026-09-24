<?php

namespace App\Http\Controllers;

use App\Models\RepairOrderPayment;
use App\Models\VehicleInspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Payment Verification for a Repair Order.
 *
 * Staff can upload a payment (down payment / full payment) with a proof
 * (receipt / screenshot / PDF) right from the Repair Order page. Each entry
 * starts as "pending" and can be verified (or rejected) by staff, so the
 * system can track whether the repair order is unpaid, partially paid
 * (down payment) or fully paid.
 */
class RepairOrderPaymentController extends Controller
{
    public function store(Request $request, VehicleInspection $inspection)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:down_payment,full_payment',
            'payment_method' => 'nullable|string|max:60',
            'reference_number' => 'nullable|string|max:120',
            'proof' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:8192',
            'notes' => 'nullable|string|max:1000',
        ], [
            'proof.required' => 'Please attach the payment proof (receipt / screenshot / PDF).',
        ]);

        $proofPath = $request->file('proof')->store('repair-order-payments', 'public');

        $inspection->repairOrderPayments()->create([
            'customer_id' => $inspection->customer_id,
            'amount' => $validated['amount'],
            'payment_type' => $validated['payment_type'],
            'payment_method' => $validated['payment_method'] ?? null,
            'reference_number' => $validated['reference_number'] ?? null,
            'proof_path' => $proofPath,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Payment uploaded — waiting for verification.');
    }

    public function verify(Request $request, RepairOrderPayment $payment)
    {
        $payment->update([
            'status' => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', $payment->type_label . ' verified.');
    }

    public function reject(Request $request, RepairOrderPayment $payment)
    {
        $payment->update([
            'status' => 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', $payment->type_label . ' marked as rejected.');
    }

    public function destroy(RepairOrderPayment $payment)
    {
        $payment->delete();

        return back()->with('success', 'Payment record removed.');
    }
}
