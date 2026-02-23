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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained('inventory_suppliers')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->string('status')->default('draft'); // draft, pending_approval, approved, ordered, partially_received, received, cancelled
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->string('payment_terms')->nullable();
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->boolean('is_rush')->default(false);
            $table->boolean('is_backorder')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('po_number');
            $table->index('supplier_id');
            $table->index('status');
            $table->index('order_date');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};