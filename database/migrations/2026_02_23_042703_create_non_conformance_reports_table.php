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
        Schema::create('non_conformance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('ncr_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['internal', 'external', 'customer', 'supplier'])->default('internal');
            $table->enum('severity', ['minor', 'major', 'critical'])->default('minor');
            $table->foreignId('audit_id')->nullable()->constrained('quality_audits')->onDelete('set null');
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');
            $table->json('evidence')->nullable(); // JSON array of evidence (photos, documents)
            $table->enum('status', ['open', 'investigating', 'action_required', 'resolved', 'closed'])->default('open');
            $table->date('reported_date');
            $table->date('due_date')->nullable();
            $table->date('resolved_date')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('containment_actions')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['ncr_number', 'status']);
            $table->index(['type', 'severity']);
            $table->index(['reported_date', 'due_date']);
            $table->index(['assigned_to', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_conformance_reports');
    }
};