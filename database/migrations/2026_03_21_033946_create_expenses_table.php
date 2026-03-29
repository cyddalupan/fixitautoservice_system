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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique()->nullable();
            $table->date('date');
            $table->string('category'); // e.g., 'office', 'tools', 'utilities', 'rent', 'supplies', 'vehicle', 'other'
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->string('vendor')->nullable();
            $table->string('payment_method')->nullable(); // e.g., 'cash', 'check', 'credit_card', 'bank_transfer'
            $table->string('reference_number')->nullable(); // check number, transaction id, etc.
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // who recorded it
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
