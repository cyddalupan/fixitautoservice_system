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
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->string('action_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->foreignId('ncr_id')->constrained('non_conformance_reports')->onDelete('cascade');
            $table->foreignId('standard_id')->nullable()->constrained('compliance_standards')->onDelete('set null');
            $table->enum('action_type', ['correction', 'corrective_action', 'preventive_action'])->default('corrective_action');
            $table->text('required_actions');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('restrict');
            $table->date('assigned_date');
            $table->date('due_date');
            $table->date('completed_date')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'verified', 'overdue', 'cancelled'])->default('assigned');
            $table->text('completion_notes')->nullable();
            $table->json('supporting_docs')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('verification_date')->nullable();
            $table->text('verification_notes')->nullable();
            $table->boolean('effectiveness_verified')->default(false);
            $table->date('effectiveness_check_date')->nullable();
            $table->text('effectiveness_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['action_number', 'status']);
            $table->index(['assigned_to', 'due_date']);
            $table->index(['ncr_id', 'action_type']);
            $table->index(['due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corrective_actions');
    }
};