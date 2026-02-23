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
        Schema::create('profit_analysis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->date('analysis_date');
            $table->decimal('total_revenue', 12, 2);
            $table->decimal('total_cost', 12, 2);
            $table->decimal('gross_profit', 12, 2);
            $table->decimal('gross_profit_margin', 5, 2);
            $table->decimal('labor_revenue', 12, 2);
            $table->decimal('labor_cost', 12, 2);
            $table->decimal('labor_profit', 12, 2);
            $table->decimal('labor_profit_margin', 5, 2);
            $table->decimal('parts_revenue', 12, 2);
            $table->decimal('parts_cost', 12, 2);
            $table->decimal('parts_profit', 12, 2);
            $table->decimal('parts_profit_margin', 5, 2);
            $table->decimal('other_revenue', 12, 2)->default(0);
            $table->decimal('other_cost', 12, 2)->default(0);
            $table->decimal('other_profit', 12, 2)->default(0);
            $table->decimal('overhead_allocation', 12, 2)->default(0);
            $table->decimal('net_profit', 12, 2);
            $table->decimal('net_profit_margin', 5, 2);
            $table->json('cost_breakdown')->nullable();
            $table->json('revenue_breakdown')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('analysis_date');
            $table->index('work_order_id');
            $table->index('invoice_id');
            $table->index('gross_profit_margin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_analysis');
    }
};
