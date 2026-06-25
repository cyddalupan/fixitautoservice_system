<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_type', 50)->nullable()->comment('car, suv, truck, van — null means all');
            $table->foreignId('vehicle_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('variant_label', 255)->nullable()->comment('e.g. "3L Oil", "Single AC", "Dual AC"');
            $table->decimal('price', 12, 2);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicates for same combo
            $table->unique(['service_type_id', 'vehicle_type', 'vehicle_model_id', 'variant_label'], 'service_pricing_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_pricings');
    }
};
