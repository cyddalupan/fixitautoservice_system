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
        // Create technician_profiles table
        Schema::create('technician_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_number')->unique()->nullable();
            $table->string('title')->nullable(); // Senior Technician, Master Technician, etc.
            $table->string('department')->nullable(); // Mechanical, Electrical, Body Shop, etc.
            $table->string('specialization')->nullable(); // Engine, Transmission, Electrical, etc.
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('overtime_rate', 10, 2)->nullable();
            $table->decimal('double_time_rate', 10, 2)->nullable();
            $table->integer('years_experience')->nullable();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->enum('employment_status', ['active', 'on_leave', 'suspended', 'terminated'])->default('active');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'temporary'])->default('full_time');
            $table->integer('weekly_hours')->default(40);
            $table->string('shift_schedule')->nullable(); // Morning, Evening, Night, Rotating
            $table->time('shift_start_time')->nullable();
            $table->time('shift_end_time')->nullable();
            $table->string('workstation')->nullable(); // Bay number, work area
            $table->string('tools_assigned')->nullable(); // Serialized tools assigned
            $table->string('uniform_size')->nullable();
            $table->string('safety_shoe_size')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('performance_rating', 3, 2)->nullable(); // 0.00 to 5.00
            $table->integer('quality_score')->nullable(); // 0-100
            $table->integer('efficiency_score')->nullable(); // 0-100
            $table->integer('customer_satisfaction_score')->nullable(); // 0-100
            $table->integer('jobs_completed')->default(0);
            $table->decimal('total_revenue_generated', 12, 2)->default(0);
            $table->decimal('average_job_time', 8, 2)->nullable(); // in hours
            $table->decimal('average_labor_sales', 10, 2)->nullable();
            $table->decimal('average_parts_sales', 10, 2)->nullable();
            $table->boolean('can_train_others')->default(false);
            $table->boolean('is_team_lead')->default(false);
            $table->foreignId('team_lead_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('preferences')->nullable(); // JSON for user preferences
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'employment_status']);
            $table->index(['department', 'specialization']);
            $table->index('performance_rating');
        });

        // Create technician_certifications table
        Schema::create('technician_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->string('certification_name');
            $table->string('certification_code')->nullable();
            $table->string('issuing_organization'); // ASE, Manufacturer, etc.
            $table->string('certification_level')->nullable(); // Basic, Advanced, Master, etc.
            $table->text('description')->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->string('certification_number')->nullable();
            $table->string('certificate_file_path')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->date('verified_date')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('verification_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['technician_id', 'is_active']);
            $table->index(['issuing_organization', 'certification_name'], 'tech_cert_org_name_idx');
            $table->index('expiry_date');
        });

        // Create technician_skills table
        Schema::create('technician_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->string('skill_name');
            $table->string('skill_category')->nullable(); // Mechanical, Electrical, Diagnostic, etc.
            $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('beginner');
            $table->integer('proficiency_score')->nullable(); // 0-100
            $table->date('skill_acquired_date')->nullable();
            $table->date('last_used_date')->nullable();
            $table->integer('times_used')->default(0);
            $table->decimal('success_rate', 5, 2)->nullable(); // percentage
            $table->text('description')->nullable();
            $table->boolean('is_certified')->default(false);
            $table->foreignId('certification_id')->nullable()->constrained('technician_certifications')->onDelete('set null');
            $table->boolean('is_primary_skill')->default(false);
            $table->integer('preference_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['technician_id', 'skill_category'], 'tech_skills_cat_idx');
            $table->index(['skill_name', 'proficiency_level'], 'tech_skills_name_level_idx');
            $table->index('is_primary_skill');
        });

        // Create technician_time_logs table
        Schema::create('technician_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->enum('log_type', ['clock_in', 'clock_out', 'break_start', 'break_end', 'lunch_start', 'lunch_end', 'job_start', 'job_end', 'training', 'meeting', 'maintenance', 'other']);
            $table->timestamp('log_time')->useCurrent();
            $table->string('location')->nullable(); // GPS coordinates, bay number, etc.
            $table->string('device_id')->nullable(); // Device used for logging
            $table->string('ip_address')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'adjusted'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional data in JSON format
            $table->timestamps();
            
            $table->index(['technician_id', 'log_time'], 'tech_time_log_idx');
            $table->index(['work_order_id', 'log_type'], 'time_log_wo_type_idx');
            $table->index(['log_type', 'status'], 'time_log_type_status_idx');
            $table->index('log_time');
        });

        // Create technician_performance_metrics table
        Schema::create('technician_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->date('metric_date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('daily');
            
            // Efficiency Metrics
            $table->integer('jobs_assigned')->default(0);
            $table->integer('jobs_completed')->default(0);
            $table->integer('jobs_in_progress')->default(0);
            $table->integer('jobs_on_hold')->default(0);
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->decimal('efficiency_percentage', 5, 2)->nullable(); // (estimated/actual)*100
            
            // Quality Metrics
            $table->integer('quality_checks_passed')->default(0);
            $table->integer('quality_checks_failed')->default(0);
            $table->decimal('quality_score', 5, 2)->nullable(); // 0-100
            $table->integer('comebacks')->default(0); // Jobs that had to be redone
            $table->integer('warranty_claims')->default(0);
            $table->integer('customer_complaints')->default(0);
            $table->integer('safety_incidents')->default(0);
            
            // Productivity Metrics
            $table->decimal('labor_sales', 12, 2)->default(0);
            $table->decimal('parts_sales', 12, 2)->default(0);
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('average_job_value', 10, 2)->nullable();
            $table->decimal('labor_utilization', 5, 2)->nullable(); // % of time spent on billable work
            $table->decimal('productivity_index', 5, 2)->nullable(); // Custom productivity score
            
            // Customer Satisfaction
            $table->integer('customer_ratings_received')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable(); // 0.00 to 5.00
            $table->integer('positive_reviews')->default(0);
            $table->integer('negative_reviews')->default(0);
            
            // Training & Development
            $table->integer('training_hours_completed')->default(0);
            $table->integer('certifications_earned')->default(0);
            $table->integer('skills_improved')->default(0);
            
            // Attendance & Punctuality
            $table->integer('days_present')->default(0);
            $table->integer('days_absent')->default(0);
            $table->integer('times_late')->default(0);
            $table->decimal('punctuality_score', 5, 2)->nullable(); // 0-100
            
            // Overall Scores
            $table->decimal('overall_performance_score', 5, 2)->nullable(); // 0-100
            $table->integer('performance_rank')->nullable(); // Rank among technicians
            $table->text('performance_notes')->nullable();
            $table->json('detailed_metrics')->nullable(); // JSON for additional metrics
            
            $table->timestamps();
            
            $table->unique(['technician_id', 'metric_date', 'period_type']);
            $table->index(['technician_id', 'period_type'], 'tech_perf_period_idx');
            $table->index('metric_date');
            $table->index('overall_performance_score');
        });

        // Create training_modules table
        Schema::create('training_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('module_type', ['video', 'document', 'interactive', 'assessment', 'practical'])->default('video');
            $table->string('category')->nullable(); // Technical, Safety, Customer Service, etc.
            $table->string('subcategory')->nullable();
            $table->string('skill_level')->nullable(); // Beginner, Intermediate, Advanced
            $table->integer('estimated_duration_minutes')->nullable();
            $table->string('instructor')->nullable();
            $table->string('vendor')->nullable(); // Manufacturer, ASE, etc.
            $table->string('file_path')->nullable(); // Video/document file
            $table->string('thumbnail_path')->nullable();
            $table->text('content_url')->nullable(); // External URL if applicable
            $table->text('learning_objectives')->nullable();
            $table->text('prerequisites')->nullable();
            $table->boolean('is_certification_prep')->default(false);
            $table->foreignId('certification_id')->nullable()->constrained('technician_certifications')->onDelete('set null');
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('completions_count')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category', 'subcategory'], 'training_cat_subcat_idx');
            $table->index(['module_type', 'is_active'], 'training_type_active_idx');
            $table->index('skill_level');
        });

        // Create technician_training_records table
        Schema::create('technician_training_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('training_module_id')->constrained('training_modules')->onDelete('cascade');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'failed', 'cancelled'])->default('not_started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_minutes')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->integer('score')->nullable(); // 0-100
            $table->boolean('passed')->default(false);
            $table->text('certificate_file_path')->nullable();
            $table->date('certificate_issue_date')->nullable();
            $table->date('certificate_expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('assessment_results')->nullable(); // JSON for detailed results
            $table->timestamps();
            
            $table->unique(['technician_id', 'training_module_id']);
            $table->index(['technician_id', 'status'], 'training_rec_status_idx');
            $table->index(['training_module_id', 'completed_at'], 'training_mod_completed_idx');
            $table->index('passed');
        });

        // Create parts_requests table
        Schema::create('parts_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->enum('request_type', ['standard', 'urgent', 'emergency', 'warranty', 'special_order'])->default('standard');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'ordered', 'received', 'installed', 'returned', 'cancelled'])->default('draft');
            $table->text('description')->nullable();
            $table->text('reason')->nullable(); // Why this part is needed
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->boolean('customer_approval_required')->default(false);
            $table->boolean('customer_approved')->default(false);
            $table->timestamp('customer_approved_at')->nullable();
            $table->boolean('manager_approval_required')->default(true);
            $table->boolean('manager_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('ordered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('ordered_at')->nullable();
            $table->string('supplier')->nullable();
            $table->string('supplier_order_number')->nullable();
            $table->timestamp('expected_delivery_date')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('installed_at')->nullable();
            $table->text('installation_notes')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->text('return_reason')->nullable();
            $table->enum('return_status', ['pending', 'approved', 'rejected', 'processed'])->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['technician_id', 'status'], 'parts_req_tech_status_idx');
            $table->index(['work_order_id', 'request_type'], 'parts_req_wo_type_idx');
            $table->index(['status', 'created_at'], 'parts_req_status_created_idx');
            $table->index('request_number');
        });

        // Create parts_request_items table
        Schema::create('parts_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parts_request_id')->constrained('parts_requests')->onDelete('cascade');
            $table->foreignId('inventory_id')->nullable()->constrained('inventory')->onDelete('set null');
            $table->string('part_number')->nullable();
            $table->string('part_name');
            $table->text('description')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_year')->nullable();
            $table->string('compatibility_notes')->nullable();
            $table->integer('quantity_requested')->default(1);
            $table->integer('quantity_approved')->nullable();
            $table->integer('quantity_ordered')->nullable();
            $table->integer('quantity_received')->nullable();
            $table->integer('quantity_installed')->nullable();
            $table->integer('quantity_returned')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->enum('status', ['requested', 'approved', 'rejected', 'ordered', 'received', 'installed', 'returned'])->default('requested');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['parts_request_id', 'status'], 'parts_req_items_status_idx');
            $table->index(['inventory_id', 'part_number'], 'parts_req_inv_part_idx');
            $table->index('vehicle_make');
        });

        // Add technician-specific columns to users table
        Schema::table('users', function (Blueprint $table) {
            // These columns already exist in the users table based on our analysis
            // We'll just add indexes for better performance
            $table->index(['role', 'is_active']);
            $table->index('employee_id');
            $table->index('hire_date');
        });

        // Enhance work_orders table for better technician tracking
        Schema::table('work_orders', function (Blueprint $table) {
            // Add index for technician_id
            $table->index('technician_id');
            
            // Add index for time tracking fields
            $table->index('work_start_time');
            $table->index('work_complete_time');
            
            // Add index for performance tracking
            $table->index(['technician_id', 'work_order_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order
        Schema::dropIfExists('parts_request_items');
        Schema::dropIfExists('parts_requests');
        Schema::dropIfExists('technician_training_records');
        Schema::dropIfExists('training_modules');
        Schema::dropIfExists('technician_performance_metrics');
        Schema::dropIfExists('technician_time_logs');
        Schema::dropIfExists('technician_skills');
        Schema::dropIfExists('technician_certifications');
        Schema::dropIfExists('technician_profiles');
        
        // Remove indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_active']);
            $table->dropIndex(['employee_id']);
            $table->dropIndex(['hire_date']);
        });
        
        // Remove indexes from work_orders table
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex(['technician_id']);
            $table->dropIndex(['work_start_time']);
            $table->dropIndex(['work_complete_time']);
            $table->dropIndex(['technician_id', 'work_order_status']);
        });
    }
};