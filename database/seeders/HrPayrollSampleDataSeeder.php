<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class HrPayrollSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating HR Payroll sample data...');
        
        // Get existing users (excluding admin)
        $users = User::where('id', '!=', 1)->get();
        
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please create users first.');
            return;
        }
        
        // 1. Create Employee HR Details
        $this->command->info('Creating employee HR details...');
        $departments = ['Service', 'Sales', 'Administration', 'Management', 'Parts'];
        $positions = ['Manager', 'Supervisor', 'Senior', 'Junior', 'Trainee'];
        $jobTitles = [
            'Service Advisor', 'Master Technician', 'Brake Specialist', 
            'Shop Manager', 'Parts Manager', 'Receptionist', 'Accountant'
        ];
        
        foreach ($users as $user) {
            DB::table('employee_hr_details')->insert([
                'user_id' => $user->id,
                'employee_number' => 'EMP' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'department' => $departments[array_rand($departments)],
                'position' => $positions[array_rand($positions)],
                'job_title' => $jobTitles[array_rand($jobTitles)],
                'employment_status' => ['full_time', 'part_time', 'contract'][array_rand(['full_time', 'part_time', 'contract'])],
                'hire_date' => Carbon::now()->subMonths(rand(6, 36))->format('Y-m-d'),
                'base_salary' => rand(30000, 80000),
                'pay_frequency' => ['weekly', 'biweekly', 'monthly'][array_rand(['weekly', 'biweekly', 'monthly'])],
                'pay_type' => ['hourly', 'salary'][array_rand(['hourly', 'salary'])],
                'bank_name' => ['BPI', 'BDO', 'Metrobank', 'Security Bank'][array_rand(['BPI', 'BDO', 'Metrobank', 'Security Bank'])],
                'bank_account_number' => 'ACC' . rand(100000000, 999999999),
                'date_of_birth' => Carbon::now()->subYears(rand(25, 55))->format('Y-m-d'),
                'marital_status' => ['single', 'married', 'divorced', 'separated'][array_rand(['single', 'married', 'divorced', 'separated'])],
                'dependents' => rand(0, 4),
                'emergency_contacts' => json_encode([
                    [
                        'name' => 'Emergency Contact ' . $user->name,
                        'relationship' => ['Spouse', 'Parent', 'Sibling', 'Friend'][array_rand(['Spouse', 'Parent', 'Sibling', 'Friend'])],
                        'phone' => '+639' . rand(100000000, 999999999),
                        'email' => 'emergency' . strtolower(str_replace(' ', '', $user->name)) . '@example.com'
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // 2. Create Payroll Periods (last 3 months)
        $this->command->info('Creating payroll periods...');
        $periods = [];
        for ($i = 1; $i <= 3; $i++) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $payDate = $endDate->copy()->addDays(5);
            
            $periods[] = [
                'period_name' => $startDate->format('F Y'),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'pay_date' => $payDate->format('Y-m-d'),
                'status' => ['draft', 'processing', 'approved', 'paid'][array_rand(['draft', 'processing', 'approved', 'paid'])],
                'total_gross' => rand(50000, 200000),
                'total_deductions' => rand(5000, 30000),
                'total_net' => rand(45000, 170000),
                'employee_count' => count($users),
                'processed_by' => 1, // Admin user
                'processed_at' => $payDate->format('Y-m-d H:i:s'),
                'notes' => 'Sample payroll period for ' . $startDate->format('F Y'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('payroll_periods')->insert($periods);
        
        // 3. Create Payroll Records for each period and employee
        $this->command->info('Creating payroll records...');
        $payrollPeriods = DB::table('payroll_periods')->get();
        $payrollRecords = [];
        
        foreach ($payrollPeriods as $period) {
            foreach ($users as $user) {
                $regularHours = rand(160, 200);
                $overtimeHours = rand(0, 40);
                $regularRate = rand(300, 800) / 10; // 30-80 per hour
                $overtimeRate = $regularRate * 1.5;
                
                $regularPay = $regularHours * $regularRate;
                $overtimePay = $overtimeHours * $overtimeRate;
                $bonus = rand(0, 5000);
                $totalGross = $regularPay + $overtimePay + $bonus;
                
                // Deductions
                $federalTax = $totalGross * 0.12;
                $stateTax = $totalGross * 0.05;
                $socialSecurity = $totalGross * 0.062;
                $medicare = $totalGross * 0.0145;
                $healthInsurance = 500;
                $totalDeductions = $federalTax + $stateTax + $socialSecurity + $medicare + $healthInsurance;
                
                $payrollRecords[] = [
                    'payroll_period_id' => $period->id,
                    'employee_id' => $user->id,
                    'regular_hours' => $regularHours,
                    'overtime_hours' => $overtimeHours,
                    'regular_rate' => $regularRate,
                    'overtime_rate' => $overtimeRate,
                    'regular_pay' => $regularPay,
                    'overtime_pay' => $overtimePay,
                    'bonus' => $bonus,
                    'total_gross' => $totalGross,
                    'federal_tax' => $federalTax,
                    'state_tax' => $stateTax,
                    'social_security' => $socialSecurity,
                    'medicare' => $medicare,
                    'health_insurance' => $healthInsurance,
                    'total_deductions' => $totalDeductions,
                    'net_pay' => $totalGross - $totalDeductions,
                    'status' => $period->status,
                    'pay_date' => $period->pay_date,
                    'payment_method' => ['direct_deposit', 'check'][array_rand(['direct_deposit', 'check'])],
                    'check_number' => $period->status === 'paid' ? 'CHK' . rand(1000, 9999) : null,
                    'notes' => 'Payroll for ' . $period->period_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        DB::table('payroll_records')->insert($payrollRecords);
        
        // 4. Create Time & Attendance records (last 30 days)
        $this->command->info('Creating time & attendance records...');
        $timeRecords = [];
        
        for ($i = 1; $i <= 30; $i++) {
            $workDate = Carbon::now()->subDays($i)->format('Y-m-d');
            
            // Skip weekends (Saturday = 6, Sunday = 0)
            $dayOfWeek = Carbon::parse($workDate)->dayOfWeek;
            if ($dayOfWeek === 0 || $dayOfWeek === 6) {
                continue;
            }
            
            foreach ($users as $user) {
                // 90% chance of being present
                $status = rand(1, 10) <= 9 ? 'present' : (rand(1, 10) <= 2 ? 'late' : 'absent');
                
                if ($status === 'present') {
                    $clockIn = Carbon::createFromTime(8, rand(0, 30), 0)->format('H:i:s');
                    $clockOut = Carbon::createFromTime(17, rand(0, 30), 0)->format('H:i:s');
                    $regularHours = 8.0;
                    $overtimeHours = rand(0, 10) <= 3 ? rand(1, 3) : 0;
                } else {
                    $clockIn = null;
                    $clockOut = null;
                    $regularHours = 0;
                    $overtimeHours = 0;
                }
                
                $timeRecords[] = [
                    'employee_id' => $user->id,
                    'work_date' => $workDate,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'regular_hours' => $regularHours,
                    'overtime_hours' => $overtimeHours,
                    'shift_type' => ['morning', 'afternoon', 'night'][array_rand(['morning', 'afternoon', 'night'])],
                    'location' => 'Main Office',
                    'status' => $status,
                    'approved' => $status === 'present' ? true : false,
                    'approved_by' => $status === 'present' ? 1 : null,
                    'approved_at' => $status === 'present' ? $workDate . ' 18:00:00' : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        DB::table('time_attendance')->insert($timeRecords);
        
        // 5. Create Leave Requests
        $this->command->info('Creating leave requests...');
        $leaveTypes = ['vacation', 'sick', 'personal', 'bereavement'];
        $leaveRequests = [];
        
        foreach ($users as $user) {
            // Create 1-3 leave requests per employee
            for ($j = 1; $j <= rand(1, 3); $j++) {
                $startDate = Carbon::now()->subDays(rand(10, 90));
                $totalDays = rand(1, 5);
                $endDate = $startDate->copy()->addDays($totalDays - 1);
                
                $leaveRequests[] = [
                    'employee_id' => $user->id,
                    'leave_type' => $leaveTypes[array_rand($leaveTypes)],
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'total_days' => $totalDays,
                    'reason' => 'Sample leave request for ' . $leaveTypes[array_rand($leaveTypes)] . ' leave',
                    'status' => ['pending', 'approved', 'rejected'][array_rand(['pending', 'approved', 'rejected'])],
                    'approved_by' => $user->id === 1 ? null : 1,
                    'approved_at' => $user->id === 1 ? null : $endDate->format('Y-m-d H:i:s'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        DB::table('leave_requests')->insert($leaveRequests);
        
        // 6. Create Leave Balances for current year
        $this->command->info('Creating leave balances...');
        $currentYear = date('Y');
        $leaveBalances = [];
        
        foreach ($users as $user) {
            $vacationDays = 15;
            $sickDays = 10;
            $personalDays = 5;
            
            $vacationUsed = rand(0, 8);
            $sickUsed = rand(0, 5);
            $personalUsed = rand(0, 3);
            
            $leaveBalances[] = [
                'employee_id' => $user->id,
                'year' => $currentYear,
                'vacation_days' => $vacationDays,
                'sick_days' => $sickDays,
                'personal_days' => $personalDays,
                'vacation_used' => $vacationUsed,
                'sick_used' => $sickUsed,
                'personal_used' => $personalUsed,
                'vacation_remaining' => $vacationDays - $vacationUsed,
                'sick_remaining' => $sickDays - $sickUsed,
                'personal_remaining' => $personalDays - $personalUsed,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('leave_balances')->insert($leaveBalances);
        
        // 7. Create Tax Settings
        $this->command->info('Creating tax settings...');
        $taxSettings = [
            [
                'tax_type' => 'federal',
                'tax_name' => 'Federal Income Tax',
                'rate' => 12.0,
                'description' => 'Federal income tax withholding',
                'effective_date' => Carbon::now()->subYear()->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tax_type' => 'state',
                'tax_name' => 'State Income Tax',
                'rate' => 5.0,
                'description' => 'State income tax withholding',
                'effective_date' => Carbon::now()->subYear()->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tax_type' => 'federal',
                'tax_name' => 'Social Security',
                'rate' => 6.2,
                'description' => 'Social Security tax (employee portion)',
                'effective_date' => Carbon::now()->subYear()->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tax_type' => 'federal',
                'tax_name' => 'Medicare',
                'rate' => 1.45,
                'description' => 'Medicare tax (employee portion)',
                'effective_date' => Carbon::now()->subYear()->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('tax_settings')->insert($taxSettings);
        
        // 8. Create Deduction Settings
        $this->command->info('Creating deduction settings...');
        $deductionSettings = [
            [
                'deduction_type' => 'health_insurance',
                'deduction_name' => 'Health Insurance Premium',
                'calculation_type' => 'fixed',
                'fixed_amount' => 500.00,
                'description' => 'Monthly health insurance premium',
                'effective_date' => Carbon::now()->subYear()->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'deduction_type' => 'retirement',
                'deduction_name' => '401(k) Contribution',
                'calculation_type' => 'percentage',
                'rate' => 3.0,
                'description' => 'Retirement plan contribution',
                'effective_date' => Carbon::now()->subYear()->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'deduction_type' => 'other',
                'deduction_name' => 'Union Dues',
                'calculation_type' => 'fixed',
                'fixed_amount' => 50.00,
                'description' => 'Monthly union membership dues',
                'effective_date' => Carbon::now()->subYear()->format('Y-m-d'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('deduction_settings')->insert($deductionSettings);
        
        // 9. Create Employee Deductions
        $this->command->info('Creating employee deductions...');
        $employeeDeductions = [];
        $deductionSettings = DB::table('deduction_settings')->get();
        
        foreach ($users as $user) {
            foreach ($deductionSettings as $setting) {
                // 70% chance of having each deduction
                if (rand(1, 10) <= 7) {
                    $employeeDeductions[] = [
                        'employee_id' => $user->id,
                        'deduction_setting_id' => $setting->id,
                        'amount' => $setting->fixed_amount ?? 0,
                        'frequency' => 'per_paycheck',
                        'start_date' => Carbon::now()->subMonths(rand(1, 12))->format('Y-m-d'),
                        'is_active' => true,
                        'notes' => 'Auto-enrolled deduction',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        DB::table('employee_deductions')->insert($employeeDeductions);
        
        // 10. Create Payroll History Logs
        $this->command->info('Creating payroll history logs...');
        $payrollHistoryLogs = [];
        
        foreach ($payrollPeriods as $period) {
            foreach ($users as $user) {
                $actions = ['created', 'updated', 'calculated', 'approved', 'paid'];
                
                foreach ($actions as $action) {
                    // 30% chance of creating a log for each action
                    if (rand(1, 10) <= 3) {
                        $payrollHistoryLogs[] = [
                            'payroll_period_id' => $period->id,
                            'employee_id' => $user->id,
                            'action' => $action,
                            'description' => ucfirst($action) . ' payroll for ' . $period->period_name,
                            'performed_by' => 1, // Admin user
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }
        
        DB::table('payroll_history_logs')->insert($payrollHistoryLogs);
        
        $this->command->info('HR Payroll sample data created successfully!');
        $this->command->info('Total records created:');
        $this->command->info('- Employee HR Details: ' . count($users));
        $this->command->info('- Payroll Periods: ' . count($periods));
        $this->command->info('- Payroll Records: ' . count($payrollRecords));
        $this->command->info('- Time & Attendance: ' . count($timeRecords));
        $this->command->info('- Leave Requests: ' . count($leaveRequests));
        $this->command->info('- Leave Balances: ' . count($leaveBalances));
        $this->command->info('- Tax Settings: ' . count($taxSettings));
        $this->command->info('- Deduction Settings: ' . count($deductionSettings));
        $this->command->info('- Employee Deductions: ' . count($employeeDeductions));
        $this->command->info('- Payroll History Logs: ' . count($payrollHistoryLogs));
    }
}
