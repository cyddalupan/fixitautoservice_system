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
        // Table 1: quality_checks - Quality check templates
        Schema::create('quality_checks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('category', ['safety', 'workmanship', 'cleanliness', 'documentation', 'parts', 'customer_service']);
            $table->json('checklist_items'); // Array of checklist items
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category', 'is_active']);
        });

        // Table 2: work_order_quality_checks - Quality checks performed on work orders
        Schema::create('work_order_quality_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->foreignId('quality_check_id')->constrained('quality_checks')->onDelete('cascade');
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'in_progress', 'passed', 'failed', 'needs_rework', 'approved', 'rejected'])->default('pending');
            $table->json('results'); // Array of pass/fail results for each checklist item
            $table->text('notes')->nullable();
            $table->json('photos')->nullable(); // Array of photo URLs
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['work_order_id', 'status']);
            $table->index(['technician_id', 'status']);
            $table->index(['supervisor_id', 'status']);
            $table->index('completed_at');
        });

        // Table 3: compliance_documents - Regulatory compliance records
        Schema::create('compliance_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_name');
            $table->enum('document_type', ['safety', 'environmental', 'licensing', 'insurance', 'training', 'other']);
            $table->string('document_number')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->date('issue_date');
            $table->date('expiration_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['document_type', 'expiration_date']);
            $table->index(['is_active', 'expiration_date']);
        });

        // Table 4: customer_satisfaction_surveys - Post-service customer feedback
        Schema::create('customer_satisfaction_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('overall_rating')->default(0); // 1-5 stars
            $table->integer('quality_rating')->default(0);
            $table->integer('timeliness_rating')->default(0);
            $table->integer('communication_rating')->default(0);
            $table->integer('cleanliness_rating')->default(0);
            $table->integer('value_rating')->default(0);
            $table->text('positive_comments')->nullable();
            $table->text('improvement_suggestions')->nullable();
            $table->boolean('would_recommend')->default(true);
            $table->boolean('would_return')->default(true);
            $table->enum('status', ['pending', 'completed', 'follow_up_needed', 'resolved'])->default('pending');
            $table->text('follow_up_notes')->nullable();
            $table->foreignId('follow_up_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('follow_up_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['work_order_id', 'customer_id']);
            $table->index(['status', 'completed_at']);
            $table->index('overall_rating');
        });

        // Table 5: quality_control_settings - System settings for quality control
        Schema::create('quality_control_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->string('data_type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('setting_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_control_settings');
        Schema::dropIfExists('customer_satisfaction_surveys');
        Schema::dropIfExists('compliance_documents');
        Schema::dropIfExists('work_order_quality_checks');
        Schema::dropIfExists('quality_checks');
    }
};