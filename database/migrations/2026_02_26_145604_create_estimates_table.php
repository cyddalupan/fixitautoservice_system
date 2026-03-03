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
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('estimate_number')->unique();
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->integer('mileage')->nullable();
            $table->decimal('labor_hours', 8, 2)->nullable();
            $table->decimal('labor_rate', 10, 2)->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->decimal('tax_amount', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'expired'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};
