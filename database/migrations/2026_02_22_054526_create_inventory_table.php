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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('inventory_categories')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('inventory_suppliers')->onDelete('cascade');
            $table->string('manufacturer')->nullable();
            $table->string('oem_number')->nullable();
            $table->string('upc')->nullable();
            $table->string('location')->nullable();
            $table->string('bin')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('minimum_stock')->default(5);
            $table->integer('reorder_point')->default(10);
            $table->decimal('cost_price', 10, 2);
            $table->decimal('retail_price', 10, 2);
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->decimal('core_price', 10, 2)->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('in_stock'); // in_stock, low_stock, out_of_stock, discontinued
            $table->date('last_purchased')->nullable();
            $table->date('last_sold')->nullable();
            $table->integer('total_sold')->default(0);
            $table->decimal('total_sales', 10, 2)->default(0.00);
            $table->decimal('total_cost', 10, 2)->default(0.00);
            $table->decimal('profit_margin', 5, 2)->default(0.00);
            $table->integer('turnover_rate')->default(0);
            $table->text('notes')->nullable();
            $table->string('image_url')->nullable();
            $table->string('barcode')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('part_number');
            $table->index('category_id');
            $table->index('supplier_id');
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};