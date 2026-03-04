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
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            
            $table->string('item_type')->default('part'); // labor, part, sublet, fee, tax, discount
            $table->text('description');
            $table->string('part_number')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('each'); // each, hour, liter, gallon, etc.
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            
            $table->boolean('is_estimate')->default(true);
            $table->boolean('is_warranty')->default(false);
            $table->boolean('is_insurance')->default(false);
            
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('work_order_id');
            $table->index('item_type');
            $table->index('part_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};