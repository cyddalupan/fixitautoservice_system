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
        Schema::create('retention_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('first_service_date');
            $table->date('last_service_date')->nullable();
            $table->integer('total_services')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->integer('days_since_last_service')->nullable();
            $table->string('retention_status'); // active, at_risk, lapsed, lost
            $table->decimal('retention_score', 5, 2)->default(0); // 0-100 score
            $table->json('service_pattern')->nullable(); // JSON of service intervals
            $table->json('preferred_services')->nullable(); // JSON of service types
            $table->date('next_expected_service')->nullable();
            $table->boolean('is_at_risk')->default(false);
            $table->timestamp('risk_assessed_at')->nullable();
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index(['customer_id', 'retention_status'], 'retention_customer_status');
            $table->index(['retention_status', 'days_since_last_service'], 'retention_status_days');
            $table->index(['is_at_risk', 'next_expected_service'], 'retention_risk_next');
            $table->index(['total_services', 'total_spent'], 'retention_services_spent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retention_analytics');
    }
};
