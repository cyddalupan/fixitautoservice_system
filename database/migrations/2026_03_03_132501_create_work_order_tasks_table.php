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
        Schema::create('work_order_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->enum('task_status', ['pending', 'assigned', 'in_progress', 'completed', 'on_hold', 'cancelled'])->default('pending');
            
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->decimal('actual_hours', 8, 2)->default(0);
            
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
            $table->softDeletes();
            
            // Indexes
            $table->index('work_order_id');
            $table->index('assigned_technician_id');
            $table->index('task_status');
            $table->index('sequence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_tasks');
    }
};