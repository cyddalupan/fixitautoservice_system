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
        Schema::create('parts_markup', function (Blueprint $table) {
            $table->id();
            $table->string('markup_name');
            $table->string('markup_type')->default('percentage'); // percentage, fixed, tiered
            $table->decimal('markup_value', 10, 2);
            $table->foreignId('category_id')->nullable()->constrained('inventory_categories')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('inventory_suppliers')->onDelete('set null');
            $table->decimal('minimum_cost', 10, 2)->nullable();
            $table->decimal('maximum_cost', 10, 2)->nullable();
            $table->decimal('minimum_retail', 10, 2)->nullable();
            $table->decimal('maximum_retail', 10, 2)->nullable();
            $table->boolean('apply_to_all_categories')->default(false);
            $table->boolean('apply_to_all_suppliers')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
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
        Schema::dropIfExists('parts_markup');
    }
};
