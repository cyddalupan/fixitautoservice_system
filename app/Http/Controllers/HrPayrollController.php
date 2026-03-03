<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmployeeHrDetail;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\TimeAttendance;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\TaxSetting;
use App\Models\DeductionSetting;
use App\Models\EmployeeDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HrPayrollController extends Controller
{
    /**
     * Display HR Payroll dashboard.
     */
    public function dashboard()
    {
        $stats = $this->getDashboardStats();
        $recentPayrolls = PayrollPeriod::latest()->take(5)->get();
        $upcomingPayrolls = PayrollPeriod::upcoming()->get();
        $pendingLeaveRequests = LeaveRequest::where('status', 'pending')->count();
        $pendingTimeApprovals = TimeAttendance::pendingApproval()->count();

        return view('hr-payroll.dashboard', compact(
            'stats', 
            'recentPayrolls', 
            'upcomingPayrolls',
            'pendingLeaveRequests',
            'pendingTimeApprovals'
        ));
    }

    /**
     * Get dashboard statistics.
     */
    private function getDashboardStats(): array
    {
        $totalEmployees = User::where('role', 'technician')->orWhere('role', 'admin')->count();
        $activeEmployees = EmployeeHrDetail::active()->count();
        $totalPayrollThisMonth = PayrollPeriod::whereMonth('pay_date', now()->month)
            ->whereYear('pay_date', now()->year)
            ->where('status', 'paid')
            ->sum('total_net');
        
        $pendingPayroll = PayrollPeriod::where('status', 'processing')->count();
        $totalLeaveRequests = LeaveRequest::count();
        $approvedLeaveRequests = LeaveRequest::where('status', 'approved')->count();

        return [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'total_payroll_month' => $totalPayrollThisMonth,
            'pending_payroll' => $pendingPayroll,
            'total_leave_requests' => $totalLeaveRequests,
            'approved_leave_requests' => $approvedLeaveRequests,
        ];
    }

    /**
     * Display employee management page.
     */
    public function employees(Request $request)
    {
        $query = User::with('employeeHrDetails')
            ->whereIn('role', ['technician', 'admin', 'manager', 'service_advisor']);
        
        // Apply filters
        if ($request->has('department')) {
            $query->whereHas('employeeHrDetails', function ($q) use ($request) {
                $q->where('department', $request->department);
            });
        }
        
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereHas('employeeHrDetails', function ($q) {
                    $q->active();
                });
            } elseif ($request->status === 'terminated') {
                $query->whereHas('employeeHrDetails', function ($q) {
                    $q->terminated();
                });
            }
        }
        
        $employees = $query->paginate(20);
        $departments = EmployeeHrDetail::distinct('department')->pluck('department');

        return view('hr-payroll.employees.index', compact('employees', 'departments'));
    }

    /**
     * Display employee details page.
     */
    public function showEmployee($id)
    {
        $employee = User::with('employeeHrDetails')->findOrFail($id);
        
        // Check if HR details exist
        $hrDetails = $employee->employeeHrDetails;
        
        // Initialize empty collections for related data
        $payrollRecords = collect();
        $timeAttendance = collect();
        $currentYearBalance = null;
        
        // Try to load related data if models exist
        try {
            if (class_exists('App\\Models\\PayrollRecord')) {
                $payrollRecords = \App\Models\PayrollRecord::where('employee_id', $id)->latest()->take(10)->get();
            }
            
            if (class_exists('App\\Models\\TimeAttendance')) {
                $timeAttendance = \App\Models\TimeAttendance::where('employee_id', $id)->latest()->take(20)->get();
            }
            
            if (class_exists('App\\Models\\LeaveBalance')) {
                $currentYearBalance = \App\Models\LeaveBalance::where('employee_id', $id)
                    ->where('year', now()->year)
                    ->first();
            }
        } catch (\Exception $e) {
            // Silently fail - these are optional features
        }

        return view('hr-payroll.employees.show', compact(
            'employee', 
            'hrDetails',
            'payrollRecords', 
            'timeAttendance',
            'currentYearBalance'
        ));
    }

    /**
     * Display payroll periods page.
     */
    public function payrollPeriods(Request $request)
    {
        $query = PayrollPeriod::with('processedBy');
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('year')) {
            $query->whereYear('start_date', $request->year);
        }
        
        $periods = $query->latest()->paginate(20);
        $years = PayrollPeriod::select(DB::raw('YEAR(start_date) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('hr-payroll.payroll.periods', compact('periods', 'years'));
    }

    /**
     * Display payroll period details.
     */
    public function showPayrollPeriod($id)
    {
        $period = PayrollPeriod::with(['payrollRecords.employee', 'processedBy'])->findOrFail($id);
        $payrollRecords = $period->payrollRecords()->with('employee.employeeHrDetails')->paginate(20);
        
        $stats = [
            'total_gross' => $period->payrollRecords->sum('total_gross'),
            'total_deductions' => $period->payrollRecords->sum('total_deductions'),
            'total_net' => $period->payrollRecords->sum('net_pay'),
            'employee_count' => $period->payrollRecords->count(),
        ];

        return view('hr-payroll.payroll.show-period', compact('period', 'payrollRecords', 'stats'));
    }

    /**
     * Process payroll for a period.
     */
    public function processPayroll($id)
    {
        $period = PayrollPeriod::findOrFail($id);
        
        if (!$period->canBeProcessed()) {
            return redirect()->back()->with('error', 'Payroll period cannot be processed at this time.');
        }

        DB::transaction(function () use ($period) {
            // Get all active employees
            $activeEmployees = EmployeeHrDetail::active()->with('user')->get();
            
            foreach ($activeEmployees as $employee) {
                // Calculate payroll for each employee
                $payrollRecord = $this->calculateEmployeePayroll($employee, $period);
                $payrollRecord->save();
            }
            
            // Update period status
            $period->update([
                'status' => 'processing',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
            
            // Calculate period statistics
            $period->calculateStatistics();
        });

        return redirect()->route('hr-payroll.payroll.periods.show', $id)
            ->with('success', 'Payroll processed successfully.');
    }

    /**
     * Calculate payroll for an employee.
     */
    private function calculateEmployeePayroll(EmployeeHrDetail $employee, PayrollPeriod $period): PayrollRecord
    {
        // Get time attendance for the period
        $timeAttendance = TimeAttendance::where('employee_id', $employee->user_id)
            ->whereBetween('work_date', [$period->start_date, $period->end_date])
            ->where('approved', true)
            ->get();
        
        // Calculate hours
        $regularHours = $timeAttendance->sum('regular_hours');
        $overtimeHours = $timeAttendance->sum('overtime_hours');
        $doubleTimeHours = $timeAttendance->sum('double_time_hours');
        
        // Get employee rates
        $regularRate = $employee->base_salary; // Assuming hourly rate is stored in base_salary
        $overtimeRate = $regularRate * 1.5;
        $doubleTimeRate = $regularRate * 2;
        
        // Calculate earnings
        $regularPay = $regularHours * $regularRate;
        $overtimePay = $overtimeHours * $overtimeRate;
        $doubleTimePay = $doubleTimeHours * $doubleTimeRate;
        
        // Get other earnings (bonus, commission, etc.)
        $bonus = 0; // Would come from other calculations
        $commission = 0; // Would come from sales/performance
        $otherEarnings = 0;
        
        $totalGross = $regularPay + $overtimePay + $doubleTimePay + $bonus + $commission + $otherEarnings;
        
        // Calculate deductions
        $deductions = $this->calculateDeductions($employee, $totalGross);
        
        $netPay = $totalGross - $deductions['total'];
        
        // Create payroll record
        return new PayrollRecord([
            'payroll_period_id' => $period->id,
            'employee_id' => $employee->user_id,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'double_time_hours' => $doubleTimeHours,
            'regular_rate' => $regularRate,
            'overtime_rate' => $overtimeRate,
            'double_time_rate' => $doubleTimeRate,
            'regular_pay' => $regularPay,
            'overtime_pay' => $overtimePay,
            'double_time_pay' => $doubleTimePay,
            'bonus' => $bonus,
            'commission' => $commission,
            'other_earnings' => $otherEarnings,
            'total_gross' => $totalGross,
            'federal_tax' => $deductions['federal_tax'],
            'state_tax' => $deductions['state_tax'],
            'social_security' => $deductions['social_security'],
            'medicare' => $deductions['medicare'],
            'health_insurance' => $deductions['health_insurance'],
            'retirement_contribution' => $deductions['retirement_contribution'],
            'other_deductions' => $deductions['other_deductions'],
            'total_deductions' => $deductions['total'],
            'net_pay' => $netPay,
            'status' => 'calculated',
            'pay_date' => $period->pay_date,
            'earnings_breakdown' => [
                'regular' => $regularPay,
                'overtime' => $overtimePay,
                'double_time' => $doubleTimePay,
                'bonus' => $bonus,
                'commission' => $commission,
                'other' => $otherEarnings,
            ],
            'deductions_breakdown' => $deductions,
        ]);
    }

    /**
     * Calculate deductions for an employee.
     */
    private function calculateDeductions(EmployeeHrDetail $employee, float $grossPay): array
    {
        // Simplified deduction calculation
        // In a real system, this would use tax tables and employee-specific settings
        
        $federalTax = $grossPay * 0.12; // 12% federal tax (simplified)
        $stateTax = $grossPay * 0.05; // 5% state tax (simplified)
        $socialSecurity = $grossPay * 0.062; // 6.2% social security
        $medicare = $grossPay * 0.0145; // 1.45% medicare
        
        // Get employee-specific deductions
        $employeeDeductions = EmployeeDeduction::where('employee_id', $employee->user_id)
            ->where('is_active', true)
            ->get();
        
        $healthInsurance = 0;
        $retirementContribution = 0;
        $otherDeductions = 0;
        
        foreach ($employeeDeductions as $deduction) {
            $setting = $deduction->deductionSetting;
            if ($setting->deduction_type === 'health_insurance') {
                $healthInsurance = $deduction->amount;
            } elseif ($setting->deduction_type === 'retirement') {
                $retirementContribution = $deduction->amount;
            } else {
                $otherDeductions += $deduction->amount;
            }
        }
        
        $total = $federalTax + $stateTax + $socialSecurity + $medicare 
               + $healthInsurance + $retirementContribution + $otherDeductions;
        
        return [
            'federal_tax' => $federalTax,
            'state_tax' => $stateTax,
            'social_security' => $socialSecurity,
            'medicare' => $medicare,
            'health_insurance' => $healthInsurance,
            'retirement_contribution' => $retirementContribution,
            'other_deductions' => $otherDeductions,
            'total' => $total,
        ];
    }

    /**
     * Display time and attendance page.
     */
    public function timeAttendance(Request $request)
    {
        $query = TimeAttendance::with('employee');
        
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        if ($request->has('date')) {
            $query->where('work_date', $request->date);
        } elseif ($request->has('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                $query->whereBetween('work_date', [$dates[0], $dates[1]]);
            }
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $timeAttendance = $query->latest()->paginate(30);
        $employees = User::whereIn('role', ['technician', 'admin'])->get();

        return view('hr-payroll.time-attendance.index', compact('timeAttendance', 'employees'));
    }

    /**
     * Store a new time attendance entry.
     */
    public function storeTimeAttendance(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'work_date' => 'required|date',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'status' => 'required|in:present,absent,late,on_leave',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Calculate hours (only for present status)
            $regularHours = 0;
            $overtimeHours = 0;
            $doubleTimeHours = 0;
            
            if ($request->status === 'present') {
                $clockIn = \Carbon\Carbon::createFromFormat('H:i', $request->clock_in);
                $clockOut = \Carbon\Carbon::createFromFormat('H:i', $request->clock_out);
                
                $totalMinutes = $clockOut->diffInMinutes($clockIn);
                $regularHours = min(8, $totalMinutes / 60); // First 8 hours are regular
                $overtimeHours = max(0, ($totalMinutes / 60) - 8); // Anything over 8 is overtime
                // Could add logic for double time (e.g., weekends, holidays)
            }

            // Check if time attendance already exists for this employee on this date
            $existingAttendance = TimeAttendance::where('employee_id', $request->employee_id)
                ->whereDate('work_date', $request->work_date)
                ->first();

            if ($existingAttendance) {
                // Update existing record
                $existingAttendance->update([
                    'clock_in' => $request->clock_in,
                    'clock_out' => $request->clock_out,
                    'regular_hours' => $regularHours,
                    'overtime_hours' => $overtimeHours,
                    'double_time_hours' => $doubleTimeHours,
                    'shift_type' => 'day', // Default shift type
                    'notes' => $request->notes,
                    'status' => $request->status,
                ]);

                $timeAttendance = $existingAttendance;
                $message = 'Time entry updated successfully!';
            } else {
                // Create new record
                $timeAttendance = TimeAttendance::create([
                    'employee_id' => $request->employee_id,
                    'work_date' => $request->work_date,
                    'clock_in' => $request->clock_in,
                    'clock_out' => $request->clock_out,
                    'regular_hours' => $regularHours,
                    'overtime_hours' => $overtimeHours,
                    'double_time_hours' => $doubleTimeHours,
                    'shift_type' => 'day', // Default shift type
                    'notes' => $request->notes,
                    'status' => $request->status,
                ]);

                $message = 'Time entry added successfully!';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $timeAttendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add time entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display leave management page.
     */
    public function leaveManagement(Request $request)
    {
        $query = LeaveRequest::with('employee');
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }
        
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        $leaveRequests = $query->latest()->paginate(20);
        $employees = User::whereIn('role', ['technician', 'admin'])->get();
        $leaveTypes = LeaveRequest::distinct('leave_type')->pluck('leave_type');

        return view('hr-payroll.leave.index', compact('leaveRequests', 'employees', 'leaveTypes'));
    }

    /**
     * Approve or reject leave request.
     */
    public function updateLeaveRequest(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'approval_notes' => 'nullable|string|max:500',
        ]);
        
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        if ($leaveRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Leave request has already been processed.');
        }
        
        DB::transaction(function () use ($leaveRequest, $request) {
            $leaveRequest->update([
                'status' => $request->status,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes,
            ]);
            
            // Update leave balance if approved
            if ($request->status === 'approved') {
                $this->updateLeaveBalance($leaveRequest);
            }
        });
        
        $action = $request->status === 'approved' ? 'approved' : 'rejected';
        return redirect()->back()->with('success', "Leave request {$action} successfully.");
    }

    /**
     * Update leave balance when leave is approved.
     */
    private function updateLeaveBalance(LeaveRequest $leaveRequest): void
    {
        $balance = LeaveBalance::firstOrCreate(
            [
                'employee_id' => $leaveRequest->employee_id,
                'year' => $leaveRequest->start_date->year,
            ],
            [
                'vacation_days' => 10, // Default vacation days
                'sick_days' => 5, // Default sick days
                'personal_days' => 2, // Default personal days
            ]
        );
        
        $field = match($leaveRequest->leave_type) {
            'vacation' => 'vacation_used',
            'sick' => 'sick_used',
            'personal' => 'personal_used',
            default => 'other_used',
        };
        
        $balance->increment($field, $leaveRequest->total_days);
        
        // Recalculate remaining days
        $balance->update([
            'vacation_remaining' => $balance->vacation_days - $balance->vacation_used,
            'sick_remaining' => $balance->sick_days - $balance->sick_used,
            'personal_remaining' => $balance->personal_days - $balance->personal_used,
        ]);
    }

    /**
     * Display reports page.
     */
    public function reports()
    {
        return view('hr-payroll.reports.index');
    }

    /**
     * Generate payroll report.
     */
    public function generatePayrollReport(Request $request)
    {
        // Only validate if we have report_type (meaning form was submitted)
        if ($request->has('report_type')) {
            $request->validate([
                'report_type' => 'required|in:summary,detailed,tax,deduction',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
            
            $periods = PayrollPeriod::whereBetween('start_date', [$request->start_date, $request->end_date])
                ->where('status', 'paid')
                ->with('payrollRecords.employee')
                ->get();
            
            $reportData = $this->compilePayrollReport($periods, $request->report_type);
        } else {
            // Default values for initial page load
            $reportData = [
                'periods' => collect(),
                'total_gross' => 0,
                'total_deductions' => 0,
                'total_net' => 0,
                'employee_count' => 0,
                'report_type' => 'summary',
            ];
        }
        
        return view('hr-payroll.reports.payroll', compact('reportData', 'request'));
    }

    /**
     * Compile payroll report data.
     */
    private function compilePayrollReport($periods, $reportType): array
    {
        $data = [
            'periods' => $periods,
            'total_gross' => $periods->sum('total_gross'),
            'total_deductions' => $periods->sum('total_deductions'),
            'total_net' => $periods->sum('total_net'),
            'employee_count' => $periods->sum('employee_count'),
            'report_type' => $reportType,
            'generated_at' => now(),
        ];
        
        if ($reportType === 'detailed') {
            $employeeData = [];
            foreach ($periods as $period) {
                foreach ($period->payrollRecords as $record) {
                    $employeeId = $record->employee_id;
                    if (!isset($employeeData[$employeeId])) {
                        $employeeData[$employeeId] = [
                            'employee' => $record->employee->name ?? 'Unknown',
                            'total_gross' => 0,
                            'total_deductions' => 0,
                            'total_net' => 0,
                            'periods' => 0,
                        ];
                    }
                    
                    $employeeData[$employeeId]['total_gross'] += $record->total_gross;
                    $employeeData[$employeeId]['total_deductions'] += $record->total_deductions;
                    $employeeData[$employeeId]['total_net'] += $record->net_pay;
                    $employeeData[$employeeId]['periods']++;
                }
            }
            $data['employee_data'] = $employeeData;
        }
        
        if ($reportType === 'tax') {
            $taxData = [
                'federal_tax' => 0,
                'state_tax' => 0,
                'social_security' => 0,
                'medicare' => 0,
            ];
            
            foreach ($periods as $period) {
                foreach ($period->payrollRecords as $record) {
                    $taxData['federal_tax'] += $record->federal_tax;
                    $taxData['state_tax'] += $record->state_tax;
                    $taxData['social_security'] += $record->social_security;
                    $taxData['medicare'] += $record->medicare;
                }
            }
            $data['tax_data'] = $taxData;
        }
        
        return $data;
    }

    /**
     * Display employee self-service portal.
     */
    public function employeePortal()
    {
        $employee = auth()->user();
        $employeeHrDetails = $employee->employeeHrDetails;
        $recentPayrolls = PayrollRecord::where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();
        $currentYearBalance = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->first();
        $pendingLeaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();
        $recentTimeAttendance = TimeAttendance::where('employee_id', $employee->id)
            ->latest()
            ->take(10)
            ->get();

        return view('hr-payroll.portal.dashboard', compact(
            'employee',
            'employeeHrDetails',
            'recentPayrolls',
            'currentYearBalance',
            'pendingLeaveRequests',
            'recentTimeAttendance'
        ));
    }

    /**
     * Display employee payslips.
     */
    public function employeePayslips(Request $request)
    {
        $employee = auth()->user();
        
        $query = PayrollRecord::where('employee_id', $employee->id)
            ->with('payrollPeriod');
        
        if ($request->has('year')) {
            $query->whereYear('pay_date', $request->year);
        }
        
        $payslips = $query->latest()->paginate(12);
        $years = PayrollRecord::where('employee_id', $employee->id)
            ->select(DB::raw('YEAR(pay_date) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('hr-payroll.portal.payslips', compact('payslips', 'years'));
    }

    /**
     * Display employee leave requests.
     */
    public function employeeLeaveRequests()
    {
        $employee = auth()->user();
        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->latest()
            ->paginate(10);
        $currentYearBalance = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->first();

        return view('hr-payroll.portal.leave-requests', compact('leaveRequests', 'currentYearBalance'));
    }

    /**
     * Submit new leave request.
     */
    public function submitLeaveRequest(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:vacation,sick,personal,bereavement,maternity,paternity',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:2048',
        ]);
        
        $employee = auth()->user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        // Check leave balance
        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $startDate->year)
            ->first();
        
        if ($balance) {
            $remainingField = match($request->leave_type) {
                'vacation' => 'vacation_remaining',
                'sick' => 'sick_remaining',
                'personal' => 'personal_remaining',
                default => null,
            };
            
            if ($remainingField && $balance->$remainingField < $totalDays) {
                return redirect()->back()->with('error', 'Insufficient leave balance.');
            }
        }
        
        // Create leave request
        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $request->leave_type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);
        
        // Handle attachments
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('leave-attachments');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
            $leaveRequest->update(['attachments' => $attachments]);
        }
        
        return redirect()->route('hr-payroll.portal.leave-requests')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Clock in/out for employee.
     */
    public function clockInOut(Request $request)
    {
        $employee = auth()->user();
        $today = now()->toDateString();
        
        $existingRecord = TimeAttendance::where('employee_id', $employee->id)
            ->where('work_date', $today)
            ->first();
        
        if ($existingRecord && $existingRecord->clock_in && !$existingRecord->clock_out) {
            // Clock out
            $existingRecord->update([
                'clock_out' => now()->format('H:i:s'),
            ]);
            $existingRecord->calculateHoursFromClockTimes();
            $existingRecord->save();
            
            $message = 'Clocked out successfully at ' . now()->format('h:i A');
        } else {
            // Clock in or create new record
            TimeAttendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'work_date' => $today,
                ],
                [
                    'clock_in' => now()->format('H:i:s'),
                    'status' => 'present',
                    'location' => $request->ip(),
                    'device_id' => $request->userAgent(),
                ]
            );
            
            $message = 'Clocked in successfully at ' . now()->format('h:i A');
        }
        
        return redirect()->route('hr-payroll.portal.dashboard')
            ->with('success', $message);
    }

    /**
     * Display employee time attendance.
     */
    public function employeeTimeAttendance(Request $request)
    {
        $employee = auth()->user();
        
        $query = TimeAttendance::where('employee_id', $employee->id);
        
        if ($request->has('month')) {
            $month = Carbon::parse($request->month);
            $query->whereMonth('work_date', $month->month)
                  ->whereYear('work_date', $month->year);
        } else {
            $query->whereMonth('work_date', now()->month)
                  ->whereYear('work_date', now()->year);
        }
        
        $timeAttendance = $query->orderBy('work_date', 'desc')->paginate(31);
        $monthlyStats = $this->calculateMonthlyTimeStats($employee->id);
        
        return view('hr-payroll.portal.time-attendance', compact('timeAttendance', 'monthlyStats'));
    }

    /**
     * Calculate monthly time statistics.
     */
    private function calculateMonthlyTimeStats($employeeId): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        $records = TimeAttendance::where('employee_id', $employeeId)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->where('approved', true)
            ->get();
        
        return [
            'total_days' => $records->count(),
            'regular_hours' => $records->sum('regular_hours'),
            'overtime_hours' => $records->sum('overtime_hours'),
            'double_time_hours' => $records->sum('double_time_hours'),
            'total_hours' => $records->sum(function ($record) {
                return $record->regular_hours + $record->overtime_hours + $record->double_time_hours;
            }),
            'average_hours_per_day' => $records->count() > 0 
                ? $records->sum(function ($record) {
                    return $record->regular_hours + $record->overtime_hours + $record->double_time_hours;
                }) / $records->count()
                : 0,
        ];
    }

    /**
     * Display leave balances.
     */
    public function leaveBalances(Request $request)
    {
        $query = LeaveBalance::with('employee.user');
        
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        // Note: leave_balances table doesn't have a leave_type column
        // Each record contains balances for all leave types (vacation, sick, personal)
        // Filtering by leave_type is not supported in the current schema
        // if ($request->has('leave_type')) {
        //     $query->where('leave_type', $request->leave_type);
        // }
        
        $balances = $query->orderBy('employee_id')->paginate(20);
        $leaveTypes = LeaveRequest::select('leave_type')->distinct()->pluck('leave_type');
        $employees = \App\Models\EmployeeHrDetail::with('user')->get();
        
        return view('hr-payroll.leave.balances', compact('balances', 'leaveTypes', 'employees'));
    }

    /**
     * Export time attendance data.
     */
    public function exportTimeAttendance(Request $request)
    {
        try {
            $query = TimeAttendance::with('employee');
            
            if ($request->has('start_date')) {
                $query->where('work_date', '>=', $request->start_date);
            }
            
            if ($request->has('end_date')) {
                $query->where('work_date', '<=', $request->end_date);
            }
            
            if ($request->has('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            
            $timeAttendance = $query->orderBy('work_date', 'desc')->get();
            
            // Generate CSV file
            $filename = 'time_attendance_export_' . date('Y-m-d_H-i-s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($timeAttendance) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'Employee ID',
                    'Employee Name',
                    'Work Date',
                    'Clock In',
                    'Clock Out',
                    'Regular Hours',
                    'Overtime Hours',
                    'Double Time Hours',
                    'Total Hours',
                    'Status',
                    'Shift Type',
                    'Notes',
                    'Created At',
                    'Updated At'
                ]);
                
                // CSV data rows
                foreach ($timeAttendance as $record) {
                    $employeeName = $record->employee 
                        ? $record->employee->name 
                        : 'N/A';
                    
                    $totalHours = $record->regular_hours + $record->overtime_hours + $record->double_time_hours;
                    
                    fputcsv($file, [
                        $record->employee_id,
                        $employeeName,
                        $record->work_date,
                        $record->clock_in,
                        $record->clock_out,
                        $record->regular_hours,
                        $record->overtime_hours,
                        $record->double_time_hours,
                        $totalHours,
                        ucfirst($record->status),
                        $record->shift_type,
                        $record->notes,
                        $record->created_at,
                        $record->updated_at
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('Time attendance export error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new employee.
     */
    public function createEmployee()
    {
        return view('hr-payroll.employees.create');
    }

    /**
     * Store a newly created employee.
     */
    public function storeEmployee(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id',
            'phone' => 'nullable|string|max:20',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'employment_status' => 'required|string|in:full_time,part_time,contract,temporary,intern',
            'hire_date' => 'required|date',
            'base_salary' => 'nullable|numeric|min:0',
            'role' => 'required|string|in:admin,manager,service_advisor,technician',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Create the user
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Hash::make($validated['password']),
                'employee_id' => $validated['employee_id'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'],
                'hire_date' => $validated['hire_date'],
                'is_active' => true,
                'employment_type' => $validated['employment_status'],
            ]);

            // Create HR details
            $hrDetail = \App\Models\EmployeeHrDetail::create([
                'user_id' => $user->id,
                'employee_number' => $validated['employee_id'] ?? 'EMP' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'department' => $validated['department'],
                'position' => $validated['position'],
                'job_title' => $validated['position'],
                'employment_status' => $validated['employment_status'],
                'hire_date' => $validated['hire_date'],
                'base_salary' => $validated['base_salary'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()->route('hr-payroll.employees')->with('success', 'Employee created successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing an employee.
     */
    public function editEmployee($id)
    {
        $employee = User::with('employeeHrDetails')->findOrFail($id);
        return view('hr-payroll.employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee.
     */
    public function updateEmployee(Request $request, $id)
    {
        // Find the user
        $user = User::findOrFail($id);
        
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id,' . $id,
            'phone' => 'nullable|string|max:20',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'employment_status' => 'required|string|in:full_time,part_time,contract,temporary,intern',
            'hire_date' => 'required|date',
            'base_salary' => 'nullable|numeric|min:0',
            'role' => 'required|string|in:admin,manager,service_advisor,technician',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Update the user
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'employee_id' => $validated['employee_id'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'],
                'hire_date' => $validated['hire_date'],
                'employment_type' => $validated['employment_status'],
            ]);

            // Update or create HR details
            $hrData = [
                'department' => $validated['department'],
                'position' => $validated['position'],
                'job_title' => $validated['position'],
                'employment_status' => $validated['employment_status'],
                'hire_date' => $validated['hire_date'],
                'base_salary' => $validated['base_salary'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ];

            if ($user->employeeHrDetails) {
                $user->employeeHrDetails->update($hrData);
            } else {
                $hrData['user_id'] = $user->id;
                $hrData['employee_number'] = $validated['employee_id'] ?? 'EMP' . str_pad($user->id, 5, '0', STR_PAD_LEFT);
                \App\Models\EmployeeHrDetail::create($hrData);
            }

            return redirect()->route('hr-payroll.employees.show', $id)->with('success', 'Employee updated successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update employee: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified employee.
     */
    public function destroyEmployee($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Delete HR details if they exist
            if ($user->employeeHrDetails) {
                $user->employeeHrDetails->delete();
            }
            
            // Delete the user
            $user->delete();

            return redirect()->route('hr-payroll.employees')->with('success', 'Employee deleted successfully!');
            
        } catch (\Exception $e) {
            return redirect()->route('hr-payroll.employees')
                ->withErrors(['error' => 'Failed to delete employee: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new payroll period.
     */
    public function createPayrollPeriod()
    {
        return view('hr-payroll.payroll.create-period');
    }

    /**
     * Store a newly created payroll period.
     */
    public function storePayrollPeriod(Request $request)
    {
        $request->validate([
            'period_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pay_date' => 'required|date|after_or_equal:end_date',
            'status' => 'required|in:draft,open,processing,completed,locked',
            'notes' => 'nullable|string',
        ]);

        try {
            $period = PayrollPeriod::create([
                'period_name' => $request->period_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pay_date' => $request->pay_date,
                'status' => $request->status,
                'notes' => $request->notes,
                'total_gross' => 0,
                'total_deductions' => 0,
                'total_net' => 0,
                'employee_count' => 0,
            ]);

            return redirect()->route('hr-payroll.payroll.periods')->with('success', 'Payroll period created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create payroll period: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing a payroll period.
     */
    public function editPayrollPeriod($id)
    {
        $period = \App\Models\PayrollPeriod::findOrFail($id);
        return view('hr-payroll.payroll.edit-period', compact('period'));
    }

    /**
     * Update the specified payroll period.
     */
    public function updatePayrollPeriod(Request $request, $id)
    {
        $request->validate([
            'period_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pay_date' => 'required|date|after_or_equal:end_date',
            'status' => 'required|in:draft,open,processing,completed,locked',
            'notes' => 'nullable|string',
        ]);

        try {
            $period = PayrollPeriod::findOrFail($id);
            $period->update([
                'period_name' => $request->period_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pay_date' => $request->pay_date,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            return redirect()->route('hr-payroll.payroll.periods.show', $id)->with('success', 'Payroll period updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update payroll period: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified payroll period.
     */
    public function destroyPayrollPeriod($id)
    {
        try {
            $period = PayrollPeriod::findOrFail($id);
            
            // Check if payroll period can be deleted (e.g., not processed)
            if ($period->status === 'processing' || $period->status === 'completed' || $period->status === 'paid') {
                return redirect()->route('hr-payroll.payroll.periods')
                    ->withErrors(['error' => 'Cannot delete payroll period with status: ' . $period->status]);
            }
            
            $period->delete();
            
            return redirect()->route('hr-payroll.payroll.periods')->with('success', 'Payroll period deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('hr-payroll.payroll.periods')
                ->withErrors(['error' => 'Failed to delete payroll period: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the payroll processing page.
     */
    public function processPayrollPage()
    {
        // Get payroll periods that can be processed (draft, processing, or approved)
        $payrollPeriods = PayrollPeriod::whereIn('status', ['draft', 'processing', 'approved'])
            ->orderBy('start_date', 'desc')
            ->get();
            
        return view('hr-payroll.payroll.process', compact('payrollPeriods'));
    }
}