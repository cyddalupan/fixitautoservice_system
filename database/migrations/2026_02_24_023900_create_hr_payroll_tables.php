<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Employee HR Details Table (extends users table)
        Schema::create('employee_hr_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_number')->unique();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('job_title')->nullable();
            $table->enum('employment_status', ['full_time', 'part_time', 'contract', 'temporary', 'intern'])->default('full_time');
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->string('termination_reason')->nullable();
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->string('pay_frequency')->default('biweekly'); // weekly, biweekly, monthly
            $table->string('pay_type')->default('hourly'); // hourly, salary, commission
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_routing_number')->nullable();
            $table->string('social_security_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('marital_status')->nullable();
            $table->integer('dependents')->default(0);
            $table->json('emergency_contacts')->nullable();
            $table->json('benefits_enrollment')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'employee_number']);
            $table->index('department');
            $table->index('employment_status');
        });

        // Payroll Periods Table
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('pay_date');
            $table->enum('status', ['draft', 'processing', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->decimal('total_gross', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('total_net', 12, 2)->default(0);
            $table->integer('employee_count')->default(0);
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['start_date', 'end_date']);
            $table->index('status');
            $table->index('pay_date');
        });

        // Payroll Records Table
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->decimal('regular_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('double_time_hours', 8, 2)->default(0);
            $table->decimal('regular_rate', 10, 2)->default(0);
            $table->decimal('overtime_rate', 10, 2)->default(0);
            $table->decimal('double_time_rate', 10, 2)->default(0);
            $table->decimal('regular_pay', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('double_time_pay', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('commission', 12, 2)->default(0);
            $table->decimal('other_earnings', 12, 2)->default(0);
            $table->decimal('total_gross', 12, 2)->default(0);
            
            // Deductions
            $table->decimal('federal_tax', 12, 2)->default(0);
            $table->decimal('state_tax', 12, 2)->default(0);
            $table->decimal('social_security', 12, 2)->default(0);
            $table->decimal('medicare', 12, 2)->default(0);
            $table->decimal('health_insurance', 12, 2)->default(0);
            $table->decimal('retirement_contribution', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            
            // Net Pay
            $table->decimal('net_pay', 12, 2)->default(0);
            
            // Status
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->date('pay_date');
            $table->string('payment_method')->nullable();
            $table->string('check_number')->nullable();
            $table->text('notes')->nullable();
            $table->json('earnings_breakdown')->nullable();
            $table->json('deductions_breakdown')->nullable();
            $table->timestamps();
            
            $table->index(['payroll_period_id', 'employee_id']);
            $table->index('status');
            $table->index('pay_date');
        });

        // Time & Attendance Table
        Schema::create('time_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->date('work_date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->decimal('regular_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('double_time_hours', 8, 2)->default(0);
            $table->string('shift_type')->nullable();
            $table->string('location')->nullable();
            $table->string('device_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'early_departure', 'on_leave', 'holiday'])->default('present');
            $table->text('notes')->nullable();
            $table->boolean('approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->unique(['employee_id', 'work_date']);
            $table->index(['employee_id', 'work_date', 'status']);
            $table->index('work_date');
        });

        // Leave Requests Table
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->string('leave_type'); // vacation, sick, personal, bereavement, maternity, paternity
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('leave_type');
        });

        // Leave Balances Table
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->integer('year');
            $table->decimal('vacation_days', 5, 1)->default(0);
            $table->decimal('sick_days', 5, 1)->default(0);
            $table->decimal('personal_days', 5, 1)->default(0);
            $table->decimal('vacation_used', 5, 1)->default(0);
            $table->decimal('sick_used', 5, 1)->default(0);
            $table->decimal('personal_used', 5, 1)->default(0);
            $table->decimal('vacation_remaining', 5, 1)->default(0);
            $table->decimal('sick_remaining', 5, 1)->default(0);
            $table->decimal('personal_remaining', 5, 1)->default(0);
            $table->timestamps();
            
            $table->unique(['employee_id', 'year']);
            $table->index('employee_id');
        });

        // Tax Settings Table
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tax_type'); // federal, state, local
            $table->string('tax_name');
            $table->decimal('rate', 5, 3)->default(0); // percentage rate
            $table->decimal('fixed_amount', 10, 2)->nullable();
            $table->decimal('minimum_income', 12, 2)->nullable();
            $table->decimal('maximum_income', 12, 2)->nullable();
            $table->json('brackets')->nullable(); // For progressive tax rates
            $table->boolean('is_active')->default(true);
            $table->date('effective_date');
            $table->date('expiration_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['tax_type', 'is_active']);
            $table->index('effective_date');
        });

        // Deduction Settings Table
        Schema::create('deduction_settings', function (Blueprint $table) {
            $table->id();
            $table->string('deduction_type'); // health_insurance, retirement, garnishment, other
            $table->string('deduction_name');
            $table->enum('calculation_type', ['percentage', 'fixed', 'formula'])->default('percentage');
            $table->decimal('rate', 5, 3)->nullable(); // percentage rate
            $table->decimal('fixed_amount', 10, 2)->nullable();
            $table->string('formula')->nullable();
            $table->decimal('minimum_amount', 10, 2)->nullable();
            $table->decimal('maximum_amount', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_date');
            $table->date('expiration_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['deduction_type', 'is_active']);
        });

        // Employee Deductions Table
        Schema::create('employee_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('deduction_setting_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('frequency', ['per_paycheck', 'monthly', 'quarterly', 'annually'])->default('per_paycheck');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'is_active']);
            $table->index('deduction_setting_id');
        });

        // Payroll History Log Table
        Schema::create('payroll_history_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->string('action'); // created, updated, calculated, approved, paid, cancelled
            $table->text('description');
            $table->json('changes')->nullable();
            $table->foreignId('performed_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['employee_id', 'action']);
            $table->index('payroll_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_history_logs');
        Schema::dropIfExists('employee_deductions');
        Schema::dropIfExists('deduction_settings');
        Schema::dropIfExists('tax_settings');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('time_attendance');
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('employee_hr_details');
    }
};