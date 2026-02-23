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
        Schema::create('business_intelligence_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name');
            $table->string('metric_type'); // daily, weekly, monthly, quarterly, yearly
            $table->date('metric_date');
            $table->decimal('metric_value', 15, 2);
            $table->json('metric_breakdown')->nullable(); // JSON breakdown of components
            $table->string('category'); // revenue, appointments, jobs, customers, technicians
            $table->boolean('is_calculated')->default(false);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index(['metric_name', 'metric_date']);
            $table->index(['category', 'metric_type']);
            $table->index(['metric_date', 'is_calculated']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_intelligence_metrics');
    }
};
