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
        // Ensure invoice_id column exists in job_orders table
        if (!Schema::hasColumn('job_orders', 'invoice_id')) {
            Schema::table('job_orders', function (Blueprint $table) {
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null')->after('id');
            });
        } else {
            // If column exists, ensure it has proper foreign key constraint
            Schema::table('job_orders', function (Blueprint $table) {
                // Try to drop existing foreign key if it exists
                try {
                    $table->dropForeign(['invoice_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                // Re-add with proper constraint
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            });
        }

        // Ensure job_order_id column exists in invoices table
        if (!Schema::hasColumn('invoices', 'job_order_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('job_order_id')->nullable()->constrained('job_orders')->onDelete('cascade')->after('id');
            });
        } else {
            // If column exists, ensure it has proper foreign key constraint
            Schema::table('invoices', function (Blueprint $table) {
                // Try to drop existing foreign key if it exists
                try {
                    $table->dropForeign(['job_order_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                // Re-add with proper constraint
                $table->foreign('job_order_id')->references('id')->on('job_orders')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't drop columns in down migration to avoid data loss
        Schema::table('job_orders', function (Blueprint $table) {
            try {
                $table->dropForeign(['invoice_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            try {
                $table->dropForeign(['job_order_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        });
    }
};