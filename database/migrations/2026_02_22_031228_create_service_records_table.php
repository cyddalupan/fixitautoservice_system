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
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('service_date');
            $table->integer('odometer_at_service');
            $table->string('service_type');
            $table->text('description');
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('parts_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->enum('service_status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('service_advisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('work_order_number')->unique();
            $table->text('diagnosis')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('parts_used')->nullable();
            $table->json('inspection_results')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('warranty_work')->default(false);
            $table->string('warranty_type')->nullable();
            $table->date('next_service_date')->nullable();
            $table->integer('next_service_odometer')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->integer('customer_rating')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['vehicle_id', 'service_date']);
            $table->index(['customer_id', 'service_date']);
            $table->index('service_type');
            $table->index('payment_status');
            $table->index('service_status');
            $table->index('work_order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};