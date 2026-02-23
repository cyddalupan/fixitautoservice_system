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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('customer_type', ['individual', 'commercial', 'fleet'])->default('individual');
            $table->string('company_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->enum('payment_terms', ['net_15', 'net_30', 'net_60', 'cod'])->default('net_30');
            $table->boolean('is_active')->default(true);
            $table->date('customer_since')->nullable();
            $table->integer('loyalty_points')->default(0);
            $table->enum('preferred_contact', ['email', 'phone', 'sms'])->default('email');
            $table->text('notes')->nullable();
            $table->json('preferences')->nullable();
            $table->string('segment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};