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
        Schema::create('labor_rates', function (Blueprint $table) {
            $table->id();
            $table->string('rate_name');
            $table->string('rate_code')->unique();
            $table->text('description')->nullable();
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('minimum_charge', 10, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('applicable_categories')->nullable();
            $table->json('applicable_technicians')->nullable();
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labor_rates');
    }
};
