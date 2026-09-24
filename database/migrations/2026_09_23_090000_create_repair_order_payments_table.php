<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair Order payments + proof uploads, with a simple verification
     * (pending -> verified/rejected) so the system can track whether a
     * repair order is unpaid, partially paid (down payment) or fully paid.
     *
     * Additive feature — no existing tables are modified.
     */
    public function up(): void
    {
        Schema::create('repair_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_inspection_id')
                ->constrained('vehicle_inspections')
                ->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()
                ->constrained('customers')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            // What the uploader declares it is.
            $table->enum('payment_type', ['down_payment', 'full_payment'])->default('down_payment');
            $table->string('payment_method')->nullable();   // cash, gcash, bank_transfer, ...
            $table->string('reference_number')->nullable();
            $table->string('proof_path')->nullable();       // uploaded proof (image / pdf)
            // Verification workflow.
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['vehicle_inspection_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_order_payments');
    }
};
