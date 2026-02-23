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
        Schema::create('quality_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('checklist_id')->constrained('quality_control_checklists')->onDelete('restrict');
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('auditor_id')->constrained('users')->onDelete('restrict');
            $table->date('audit_date');
            $table->json('audit_results'); // JSON structure with scores per checklist item
            $table->integer('total_score');
            $table->integer('max_score');
            $table->decimal('percentage_score', 5, 2);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'failed', 'cancelled'])->default('scheduled');
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['audit_date', 'status']);
            $table->index(['technician_id', 'audit_date']);
            $table->index(['work_order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_audits');
    }
};