<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\User;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deductions = Deduction::with(['employee', 'payrollPeriod', 'creator'])
            ->latest()
            ->paginate(20);
            
        $totalDeductions = Deduction::count();
        $pendingDeductions = Deduction::pending()->count();
        $totalAmount = Deduction::approved()->sum('amount');
        
        return view('hr-payroll.deductions.index', compact(
            'deductions',
            'totalDeductions',
            'pendingDeductions',
            'totalAmount'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = User::whereIn('role', ['technician', 'admin'])->get();
        $payrollPeriods = PayrollPeriod::where('status', '!=', 'paid')->get();
        $deductionTypes = Deduction::getDeductionTypes();
        
        return view('hr-payroll.deductions.create', compact(
            'employees',
            'payrollPeriods',
            'deductionTypes'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'deduction_type' => 'required|in:SSS,Tax,Cash Advance,Late,Other',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payroll_period_id' => 'nullable|exists:payroll_periods,id',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $deduction = Deduction::create([
            ...$validated,
            'created_by' => Auth::id(),
            'status' => 'pending'
        ]);
        
        return redirect()->route('hr-payroll.deductions.index')
            ->with('success', 'Deduction created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Deduction $deduction)
    {
        $deduction->load(['employee', 'payrollPeriod', 'creator']);
        return view('hr-payroll.deductions.show', compact('deduction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Deduction $deduction)
    {
        $employees = User::whereIn('role', ['technician', 'admin'])->get();
        $payrollPeriods = PayrollPeriod::where('status', '!=', 'paid')->get();
        $deductionTypes = Deduction::getDeductionTypes();
        $statusOptions = Deduction::getStatusOptions();
        
        return view('hr-payroll.deductions.edit', compact(
            'deduction',
            'employees',
            'payrollPeriods',
            'deductionTypes',
            'statusOptions'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Deduction $deduction)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'deduction_type' => 'required|in:SSS,Tax,Cash Advance,Late,Other',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payroll_period_id' => 'nullable|exists:payroll_periods,id',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,approved,rejected,applied',
        ]);
        
        $deduction->update($validated);
        
        return redirect()->route('hr-payroll.deductions.index')
            ->with('success', 'Deduction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deduction $deduction)
    {
        $deduction->delete();
        
        return redirect()->route('hr-payroll.deductions.index')
            ->with('success', 'Deduction deleted successfully.');
    }
    
    /**
     * Approve a deduction.
     */
    public function approve(Deduction $deduction)
    {
        $deduction->update(['status' => 'approved']);
        
        return redirect()->route('hr-payroll.deductions.index')
            ->with('success', 'Deduction approved successfully.');
    }
    
    /**
     * Reject a deduction.
     */
    public function reject(Deduction $deduction)
    {
        $deduction->update(['status' => 'rejected']);
        
        return redirect()->route('hr-payroll.deductions.index')
            ->with('success', 'Deduction rejected successfully.');
    }
    
    /**
     * Show the form for bulk creating deductions.
     */
    public function bulkCreate()
    {
        $employees = User::whereIn('role', ['technician', 'admin'])->get();
        $payrollPeriods = PayrollPeriod::where('status', '!=', 'paid')->get();
        $deductionTypes = Deduction::getDeductionTypes();
        
        return view('hr-payroll.deductions.bulk-create', compact(
            'employees',
            'payrollPeriods',
            'deductionTypes'
        ));
    }
    
    /**
     * Store bulk deductions in storage.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:users,id',
            'deduction_type' => 'required|in:SSS,Tax,Cash Advance,Late,Other',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payroll_period_id' => 'nullable|exists:payroll_periods,id',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $createdCount = 0;
        
        foreach ($validated['employee_ids'] as $employeeId) {
            Deduction::create([
                'employee_id' => $employeeId,
                'deduction_type' => $validated['deduction_type'],
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'payroll_period_id' => $validated['payroll_period_id'],
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
                'status' => 'pending'
            ]);
            
            $createdCount++;
        }
        
        return redirect()->route('hr-payroll.deductions.index')
            ->with('success', "Successfully created {$createdCount} deduction(s).");
    }
}
