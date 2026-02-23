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
        Schema::create('vehicle_recalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('recall_id')->nullable();
            $table->string('campaign_number')->nullable();
            $table->string('component')->nullable();
            $table->text('summary')->nullable();
            $table->text('consequence')->nullable();
            $table->text('remedy')->nullable();
            $table->date('recall_date')->nullable();
            $table->enum('status', ['open', 'in_progress', 'completed', 'closed'])->default('open');
            $table->date('notification_date')->nullable();
            $table->date('repair_date')->nullable();
            $table->text('repair_notes')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->boolean('customer_notified')->default(false);
            $table->date('customer_notification_date')->nullable();
            $table->boolean('customer_responded')->default(false);
            $table->date('customer_response_date')->nullable();
            $table->text('customer_response_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['vehicle_id', 'status']);
            $table->index(['recall_date', 'status']);
            $table->index('campaign_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_recalls');
    }
};