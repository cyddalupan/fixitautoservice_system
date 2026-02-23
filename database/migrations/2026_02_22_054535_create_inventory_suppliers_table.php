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
        Schema::create('inventory_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('payment_terms')->nullable();
            $table->decimal('credit_limit', 10, 2)->nullable();
            $table->decimal('current_balance', 10, 2)->default(0.00);
            $table->string('tax_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->integer('lead_time_days')->default(1);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->boolean('is_preferred')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('is_preferred');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_suppliers');
    }
};