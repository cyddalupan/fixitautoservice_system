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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_advisor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Work Order Information
            $table->string('work_order_number')->unique();
            $table->date('work_order_date');
            $table->enum('work_order_status', ['draft', 'pending_approval', 'approved', 'in_progress', 'on_hold', 'completed', 'cancelled', 'invoiced'])->default('draft');
            $table->enum('priority', ['low', 'normal', 'high', 'emergency'])->default('normal');
            $table->string('work_order_type')->default('repair'); // repair, maintenance, inspection, diagnostic, recall
            
            // Vehicle Information
            $table->integer('odometer_in')->nullable();
            $table->integer('odometer_out')->nullable();
            $table->string('fuel_level')->nullable(); // full, 3/4, 1/2, 1/4, empty
            $table->text('vehicle_condition')->nullable(); // notes on vehicle condition
            
            // Customer Concerns
            $table->text('customer_concerns')->nullable();
            $table->text('customer_complaints')->nullable();
            
            // Diagnosis
            $table->text('initial_diagnosis')->nullable();
            $table->text('technician_diagnosis')->nullable();
            $table->text('recommended_services')->nullable();
            $table->text('additional_notes')->nullable();
            
            // Estimate
            $table->decimal('estimated_labor_hours', 8, 2)->default(0);
            $table->decimal('estimated_labor_cost', 10, 2)->default(0);
            $table->decimal('estimated_parts_cost', 10, 2)->default(0);
            $table->decimal('estimated_tax', 10, 2)->default(0);
            $table->decimal('estimated_total', 10, 2)->default(0);
            $table->boolean('estimate_approved')->default(false);
            $table->timestamp('estimate_approved_at')->nullable();
            $table->text('estimate_notes')->nullable();
            
            // Actual Costs
            $table->decimal('actual_labor_hours', 8, 2)->default(0);
            $table->decimal('actual_labor_cost', 10, 2)->default(0);
            $table->decimal('actual_parts_cost', 10, 2)->default(0);
            $table->decimal('actual_tax', 10, 2)->default(0);
            $table->decimal('actual_total', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            
            // Payment
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue', 'written_off'])->default('pending');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2)->default(0);
            $table->date('payment_due_date')->nullable();
            
            // Warranty
            $table->boolean('is_warranty_work')->default(false);
            $table->string('warranty_type')->nullable(); // manufacturer, extended, recall
            $table->string('warranty_number')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->decimal('warranty_coverage', 10, 2)->default(0);
            
            // Insurance
            $table->boolean('is_insurance_work')->default(false);
            $table->string('insurance_company')->nullable();
            $table->string('insurance_claim_number')->nullable();
            $table->string('insurance_adjuster')->nullable();
            $table->decimal('insurance_deductible', 10, 2)->default(0);
            
            // Timeline
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('diagnosis_start_time')->nullable();
            $table->timestamp('diagnosis_complete_time')->nullable();
            $table->timestamp('work_start_time')->nullable();
            $table->timestamp('work_complete_time')->nullable();
            $table->timestamp('quality_check_time')->nullable();
            $table->timestamp('customer_notified_time')->nullable();
            $table->timestamp('customer_pickup_time')->nullable();
            $table->timestamp('invoice_sent_time')->nullable();
            
            // Bay Assignment
            $table->integer('bay_number')->nullable();
            $table->enum('bay_status', ['assigned', 'occupied', 'available', 'maintenance'])->nullable();
            
            // Parts
            $table->json('parts_required')->nullable(); // List of parts needed
            $table->json('parts_used')->nullable(); // List of parts actually used
            $table->boolean('parts_ordered')->default(false);
            $table->timestamp('parts_ordered_at')->nullable();
            $table->timestamp('parts_received_at')->nullable();
            
            // Labor
            $table->json('labor_tasks')->nullable(); // List of labor tasks
            $table->json('technician_assignments')->nullable(); // Technician assignments for tasks
            
            // Quality Control
            $table->boolean('quality_check_passed')->default(false);
            $table->foreignId('quality_check_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('quality_check_at')->nullable();
            $table->text('quality_check_notes')->nullable();
            
            // Customer Communication
            $table->boolean('customer_notified')->default(false);
            $table->enum('notification_method', ['sms', 'email', 'phone', 'in_person'])->nullable();
            $table->text('customer_communication_log')->nullable();
            
            // Completion
            $table->text('work_performed')->nullable();
            $table->text('technician_notes')->nullable();
            $table->text('service_advisor_notes')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->integer('customer_rating')->nullable(); // 1-5 stars
            
            // Flags
            $table->boolean('requires_customer_approval')->default(false);
            $table->boolean('customer_approval_received')->default(false);
            $table->timestamp('customer_approval_at')->nullable();
            $table->boolean('requires_manager_approval')->default(false);
            $table->boolean('manager_approval_received')->default(false);
            $table->timestamp('manager_approval_at')->nullable();
            $table->boolean('is_rush_order')->default(false);
            $table->boolean('is_complex_job')->default(false);
            $table->boolean('has_safety_concerns')->default(false);
            
            // Metadata
            $table->json('attachments')->nullable(); // URLs or paths to attached files
            $table->json('tags')->nullable(); // Tags for categorization
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('work_order_number');
            $table->index('work_order_status');
            $table->index('work_order_date');
            $table->index('customer_id');
            $table->index('vehicle_id');
            $table->index('technician_id');
            $table->index(['work_order_status', 'priority']);
        });
        
        // Create work order items table for line items
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->string('item_type'); // labor, part, sublet, fee, tax, discount
            $table->string('description');
            $table->string('part_number')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->nullable(); // hours, each, etc.
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->boolean('is_estimate')->default(true); // true for estimate, false for actual
            $table->boolean('is_warranty')->default(false);
            $table->boolean('is_insurance')->default(false);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('work_order_id');
            $table->index('item_type');
            $table->index('is_estimate');
        });
        
        // Create work order tasks table for task tracking
        Schema::create('work_order_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->enum('task_status', ['pending', 'assigned', 'in_progress', 'completed', 'on_hold', 'cancelled'])->default('pending');
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('estimated_hours', 5, 2)->default(0);
            $table->decimal('actual_hours', 5, 2)->default(0);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('complete_time')->nullable();
            $table->integer('sequence')->default(0);
            $table->boolean('is_critical_path')->default(false);
            $table->text('technician_notes')->nullable();
            $table->text('quality_check_notes')->nullable();
            $table->boolean('quality_check_passed')->default(false);
            $table->foreignId('quality_check_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('quality_check_at')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            
            $table->index('work_order_id');
            $table->index('task_status');
            $table->index('assigned_technician_id');
            $table->index(['work_order_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_tasks');
        Schema::dropIfExists('work_order_items');
        Schema::dropIfExists('work_orders');
    }
};