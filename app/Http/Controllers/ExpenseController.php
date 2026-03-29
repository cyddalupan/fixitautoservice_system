<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expense::latest();
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }
        
        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } elseif ($request->has('month')) {
            $month = $request->month;
            $query->whereMonth('date', date('m', strtotime($month)))
                  ->whereYear('date', date('Y', strtotime($month)));
        }
        
        $expenses = $query->paginate(20);
        
        // Statistics
        $totalExpenses = Expense::sum('amount');
        $monthlyExpenses = Expense::thisMonth()->sum('amount');
        $pendingExpenses = Expense::pending()->sum('amount');
        
        $categories = [
            'office' => 'Office Supplies',
            'tools' => 'Tools & Equipment',
            'utilities' => 'Utilities',
            'rent' => 'Rent',
            'supplies' => 'Shop Supplies',
            'vehicle' => 'Vehicle Expenses',
            'other' => 'Other',
        ];
        
        return view('expenses.index', compact('expenses', 'totalExpenses', 'monthlyExpenses', 'pendingExpenses', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = [
            'office' => 'Office Supplies',
            'tools' => 'Tools & Equipment',
            'utilities' => 'Utilities',
            'rent' => 'Rent',
            'supplies' => 'Shop Supplies',
            'vehicle' => 'Vehicle Expenses',
            'other' => 'Other',
        ];
        
        $paymentMethods = [
            'cash' => 'Cash',
            'check' => 'Check',
            'credit_card' => 'Credit Card',
            'bank_transfer' => 'Bank Transfer',
            'online' => 'Online Payment',
        ];
        
        return view('expenses.create', compact('categories', 'paymentMethods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|string',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'vendor' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        
        $expenseData = [
            'date' => $validated['date'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'vendor' => $validated['vendor'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'],
            'notes' => $validated['notes'],
            'user_id' => auth()->id(),
            'status' => 'pending',
        ];
        
        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $expenseData['receipt_path'] = $path;
        }
        
        $expense = Expense::create($expenseData);
        
        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully. Expense Number: ' . $expense->expense_number);
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        $categories = [
            'office' => 'Office Supplies',
            'tools' => 'Tools & Equipment',
            'utilities' => 'Utilities',
            'rent' => 'Rent',
            'supplies' => 'Shop Supplies',
            'vehicle' => 'Vehicle Expenses',
            'other' => 'Other',
        ];
        
        $paymentMethods = [
            'cash' => 'Cash',
            'check' => 'Check',
            'credit_card' => 'Credit Card',
            'bank_transfer' => 'Bank Transfer',
            'online' => 'Online Payment',
        ];
        
        return view('expenses.edit', compact('expense', 'categories', 'paymentMethods'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|string',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'vendor' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'status' => 'sometimes|in:pending,approved,rejected,paid',
        ]);
        
        $updateData = [
            'date' => $validated['date'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'vendor' => $validated['vendor'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'],
            'notes' => $validated['notes'],
        ];
        
        // Handle status change
        if (isset($validated['status']) && $validated['status'] === 'approved') {
            $updateData['approved_by'] = auth()->id();
            $updateData['approved_at'] = now();
        }
        
        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }
        
        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            
            $path = $request->file('receipt')->store('receipts', 'public');
            $updateData['receipt_path'] = $path;
        }
        
        $expense->update($updateData);
        
        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        // Delete receipt if exists
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }
        
        $expense->delete();
        
        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
    
    /**
     * Approve an expense
     */
    public function approve(Expense $expense)
    {
        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        
        return redirect()->route('expenses.index')
            ->with('success', 'Expense approved successfully.');
    }
    
    /**
     * Mark expense as paid
     */
    public function markAsPaid(Expense $expense)
    {
        $expense->update([
            'status' => 'paid',
        ]);
        
        return redirect()->route('expenses.index')
            ->with('success', 'Expense marked as paid.');
    }
}
