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
        Schema::create('service_progress', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship to track any service entity
            $table->morphs('serviceable');
            
            // Customer and vehicle reference
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            
            // Progress tracking for each step
            $table->boolean('has_appointment')->default(false);
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->timestamp('appointment_created_at')->nullable();
            
            $table->boolean('has_inspection')->default(false);
            $table->foreignId('inspection_id')->nullable()->constrained('vehicle_inspections')->onDelete('set null');
            $table->timestamp('inspection_created_at')->nullable();
            
            $table->boolean('has_estimate')->default(false);
            $table->foreignId('estimate_id')->nullable()->constrained('estimates')->onDelete('set null');
            $table->timestamp('estimate_created_at')->nullable();
            
            $table->boolean('has_work_order')->default(false);
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
            $table->timestamp('work_order_created_at')->nullable();
            
            $table->boolean('has_invoice')->default(false);
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->timestamp('invoice_created_at')->nullable();
            
            $table->boolean('has_payment')->default(false);
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->timestamp('payment_created_at')->nullable();
            
            // Current status and progress percentage
            $table->enum('current_stage', ['appointment', 'inspection', 'estimate', 'work_order', 'invoice', 'payment', 'completed'])->default('appointment');
            $table->integer('progress_percentage')->default(0);
            
            // Service type: full_service or parts_purchase
            $table->enum('service_type', ['full_service', 'parts_purchase'])->default('full_service');
            
            // Tracking
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['customer_id', 'current_stage']);
            $table->index(['service_type', 'progress_percentage']);
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_progress');
    }
};
