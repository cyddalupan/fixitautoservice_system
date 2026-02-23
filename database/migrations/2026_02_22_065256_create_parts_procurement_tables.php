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
        // Parts lookup history table
        Schema::create('parts_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('vin')->nullable()->index();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->integer('year')->nullable();
            $table->string('engine')->nullable();
            $table->string('transmission')->nullable();
            $table->string('part_category')->nullable();
            $table->string('part_name');
            $table->string('part_number')->nullable();
            $table->string('oem_number')->nullable();
            $table->string('description')->nullable();
            $table->json('search_results')->nullable(); // Store API search results
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Vendor price comparison table
        Schema::create('vendor_price_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parts_lookup_id')->constrained('parts_lookups')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('inventory_suppliers')->onDelete('cascade');
            $table->string('vendor_part_number')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->integer('estimated_delivery_days')->nullable();
            $table->boolean('in_stock')->default(true);
            $table->integer('quantity_available')->nullable();
            $table->string('condition')->default('new'); // new, used, refurbished, rebuilt
            $table->string('warranty')->nullable();
            $table->json('vendor_data')->nullable(); // Store raw vendor API response
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
        });

        // Parts orders table
        Schema::create('parts_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('vendor_id')->constrained('inventory_suppliers')->onDelete('cascade');
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->string('status')->default('draft'); // draft, pending, ordered, shipped, delivered, cancelled, returned
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('core_charge', 10, 2)->default(0);
            $table->decimal('core_refund', 10, 2)->default(0);
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->date('order_date')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Parts order items table
        Schema::create('parts_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parts_order_id')->constrained('parts_orders')->onDelete('cascade');
            $table->foreignId('vendor_price_comparison_id')->nullable()->constrained('vendor_price_comparisons')->onDelete('set null');
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory')->onDelete('set null');
            $table->string('part_name');
            $table->string('part_number')->nullable();
            $table->string('oem_number')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->decimal('core_charge', 10, 2)->default(0);
            $table->boolean('core_return_required')->default(false);
            $table->boolean('core_returned')->default(false);
            $table->date('core_return_date')->nullable();
            $table->decimal('core_refund_amount', 10, 2)->default(0);
            $table->string('status')->default('ordered'); // ordered, shipped, delivered, returned, cancelled
            $table->string('tracking_number')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Parts returns table
        Schema::create('parts_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('parts_order_id')->constrained('parts_orders')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('inventory_suppliers')->onDelete('cascade');
            $table->string('reason'); // wrong part, defective, damaged, no longer needed, etc.
            $table->text('description')->nullable();
            $table->string('status')->default('requested'); // requested, approved, shipped, received, refunded, rejected
            $table->string('rma_number')->nullable(); // Return Merchandise Authorization
            $table->date('return_request_date');
            $table->date('return_approval_date')->nullable();
            $table->date('return_shipped_date')->nullable();
            $table->date('return_received_date')->nullable();
            $table->date('refund_date')->nullable();
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->decimal('restocking_fee', 10, 2)->default(0);
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Parts return items table
        Schema::create('parts_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parts_return_id')->constrained('parts_returns')->onDelete('cascade');
            $table->foreignId('parts_order_item_id')->constrained('parts_order_items')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->decimal('refund_amount', 10, 2);
            $table->decimal('restocking_fee', 10, 2)->default(0);
            $table->boolean('core_return')->default(false);
            $table->decimal('core_refund', 10, 2)->default(0);
            $table->string('condition')->nullable(); // new, used, damaged, defective
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Core returns tracking table
        Schema::create('core_returns', function (Blueprint $table) {
            $table->id();
            $table->string('core_return_number')->unique();
            $table->foreignId('parts_order_id')->constrained('parts_orders')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('inventory_suppliers')->onDelete('cascade');
            $table->string('core_type'); // alternator, starter, transmission, etc.
            $table->string('core_part_number')->nullable();
            $table->string('condition'); // rebuildable, damaged, missing_parts
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, shipped, received, refunded, rejected
            $table->decimal('core_charge', 10, 2);
            $table->decimal('expected_refund', 10, 2);
            $table->decimal('actual_refund', 10, 2)->nullable();
            $table->date('return_due_date');
            $table->date('return_shipped_date')->nullable();
            $table->date('return_received_date')->nullable();
            $table->date('refund_date')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_returns');
        Schema::dropIfExists('parts_return_items');
        Schema::dropIfExists('parts_returns');
        Schema::dropIfExists('parts_order_items');
        Schema::dropIfExists('parts_orders');
        Schema::dropIfExists('vendor_price_comparisons');
        Schema::dropIfExists('parts_lookups');
    }
};