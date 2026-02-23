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
        Schema::create('report_settings', function (Blueprint $table) {
            $table->id();
            $table->string('report_type'); // daily_activity, monthly_performance, customer_history
            $table->json('columns')->nullable(); // Custom column selection
            $table->json('filters')->nullable(); // Default filters
            $table->string('schedule')->nullable(); // Automatic scheduling (daily, weekly, monthly)
            $table->string('recipients')->nullable(); // Email recipients for scheduled reports
            $table->string('format')->default('pdf'); // Default export format
            $table->boolean('is_default')->default(false); // Default report settings
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // User-specific settings
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['report_type', 'user_id']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_settings');
    }
};