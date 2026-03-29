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
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->enum('deduction_type', ['SSS', 'Tax', 'Cash Advance', 'Late', 'Other']);
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->foreignId('payroll_period_id')->nullable()->constrained('payroll_periods')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected', 'applied'])->default('pending');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['employee_id', 'date']);
            $table->index(['deduction_type', 'status']);
            $table->index('payroll_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
