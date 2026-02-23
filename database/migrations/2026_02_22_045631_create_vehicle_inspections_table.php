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
        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('service_advisor_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Inspection Details
            $table->string('inspection_type')->default('pre_service'); // pre_service, post_service, safety, comprehensive, custom
            $table->string('inspection_status')->default('draft'); // draft, in_progress, completed, approved, rejected, cancelled
            $table->string('inspection_name')->nullable(); // Custom name for the inspection
            $table->text('inspection_notes')->nullable();
            $table->text('technician_notes')->nullable();
            $table->text('customer_concerns')->nullable();
            $table->text('recommended_services')->nullable();
            $table->text('additional_notes')->nullable();
            
            // Inspection Results
            $table->integer('total_items_checked')->default(0);
            $table->integer('items_passed')->default(0);
            $table->integer('items_failed')->default(0);
            $table->integer('items_attention_needed')->default(0);
            $table->integer('items_not_applicable')->default(0);
            $table->decimal('inspection_score', 5, 2)->nullable(); // Percentage score
            
            // Safety & Urgency
            $table->boolean('has_safety_concerns')->default(false);
            $table->boolean('has_urgent_issues')->default(false);
            $table->boolean('has_critical_issues')->default(false);
            $table->text('safety_notes')->nullable();
            $table->text('urgent_issues_notes')->nullable();
            
            // Customer Approval
            $table->boolean('requires_customer_approval')->default(true);
            $table->boolean('customer_approved')->default(false);
            $table->string('customer_approval_method')->nullable(); // digital_signature, email, sms, in_person
            $table->timestamp('customer_approved_at')->nullable();
            $table->text('customer_approval_notes')->nullable();
            $table->string('customer_signature_path')->nullable(); // Path to digital signature file
            
            // Upsell Opportunities
            $table->boolean('has_upsell_opportunities')->default(false);
            $table->text('upsell_notes')->nullable();
            $table->decimal('estimated_upsell_value', 10, 2)->nullable();
            $table->decimal('actual_upsell_value', 10, 2)->nullable();
            
            // Media & Documentation
            $table->json('photos')->nullable(); // Array of photo paths
            $table->json('videos')->nullable(); // Array of video paths
            $table->json('documents')->nullable(); // Array of document paths
            $table->json('attachments')->nullable(); // Other attachments
            
            // Timestamps
            $table->timestamp('inspection_started_at')->nullable();
            $table->timestamp('inspection_completed_at')->nullable();
            $table->timestamp('report_generated_at')->nullable();
            $table->timestamp('report_sent_at')->nullable();
            
            // Audit Trail
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('work_order_id');
            $table->index('appointment_id');
            $table->index('customer_id');
            $table->index('vehicle_id');
            $table->index('inspection_type');
            $table->index('inspection_status');
            $table->index('has_safety_concerns');
            $table->index('has_urgent_issues');
            $table->index('customer_approved');
        });
        
        // Create inspection categories table
        Schema::create('inspection_categories', function (Blueprint $table) {
            $table->id();
            
            $table->string('category_name');
            $table->text('category_description')->nullable();
            $table->string('category_type')->default('standard'); // standard, safety, custom, manufacturer
            $table->string('vehicle_type')->nullable(); // car, truck, suv, motorcycle, all
            $table->integer('sequence')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_required')->default(false);
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('category_type');
            $table->index('vehicle_type');
            $table->index('is_active');
        });
        
        // Create inspection items table
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('inspection_id')->constrained('vehicle_inspections')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('inspection_categories')->onDelete('set null');
            
            // Item Details
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->string('item_type')->default('check'); // check, measurement, test, visual
            $table->string('item_unit')->nullable(); // mm, psi, %, etc.
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->decimal('spec_value', 10, 2)->nullable(); // Manufacturer specification
            $table->string('spec_source')->nullable(); // Manufacturer, industry standard, custom
            
            // Inspection Results
            $table->string('item_status')->default('pending'); // pending, passed, failed, attention_needed, not_applicable
            $table->decimal('measured_value', 10, 2)->nullable();
            $table->text('technician_notes')->nullable();
            $table->text('customer_notes')->nullable();
            $table->boolean('requires_attention')->default(false);
            $table->boolean('is_safety_issue')->default(false);
            $table->boolean('is_urgent_issue')->default(false);
            $table->boolean('is_critical_issue')->default(false);
            
            // Recommendations
            $table->text('recommendation')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('estimated_time_hours', 5, 2)->nullable();
            $table->string('priority')->nullable(); // low, medium, high, critical
            $table->boolean('customer_approved')->default(false);
            
            // Media
            $table->json('photos')->nullable();
            $table->json('videos')->nullable();
            $table->json('attachments')->nullable();
            
            // Sequence
            $table->integer('sequence')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('inspection_id');
            $table->index('category_id');
            $table->index('item_status');
            $table->index('requires_attention');
            $table->index('is_safety_issue');
            $table->index('priority');
        });
        
        // Create inspection templates table
        Schema::create('inspection_templates', function (Blueprint $table) {
            $table->id();
            
            $table->string('template_name');
            $table->text('template_description')->nullable();
            $table->string('template_type')->default('standard'); // standard, safety, pre_purchase, post_service
            $table->string('vehicle_type')->nullable(); // car, truck, suv, motorcycle, all
            $table->json('categories')->nullable(); // Array of category IDs
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('template_type');
            $table->index('vehicle_type');
            $table->index('is_active');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_templates');
        Schema::dropIfExists('inspection_items');
        Schema::dropIfExists('inspection_categories');
        Schema::dropIfExists('vehicle_inspections');
    }
};