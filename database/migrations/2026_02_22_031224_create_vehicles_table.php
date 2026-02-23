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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('vin')->unique();
            $table->string('license_plate')->nullable();
            $table->string('make');
            $table->string('model');
            $table->integer('year');
            $table->string('color')->nullable();
            $table->enum('vehicle_type', ['car', 'truck', 'suv', 'van', 'motorcycle', 'commercial'])->default('car');
            $table->string('engine_type')->nullable();
            $table->string('transmission')->nullable();
            $table->string('fuel_type')->nullable();
            $table->integer('odometer')->default(0);
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->integer('service_interval_miles')->default(5000);
            $table->integer('service_interval_months')->default(6);
            $table->json('service_history_summary')->nullable();
            $table->decimal('average_service_cost', 10, 2)->default(0);
            $table->integer('total_service_count')->default(0);
            $table->boolean('has_warranty')->default(false);
            $table->date('warranty_expiry')->nullable();
            $table->boolean('has_recall')->default(false);
            $table->text('recall_details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['customer_id', 'is_active']);
            $table->index(['make', 'model', 'year']);
            $table->index('next_service_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};