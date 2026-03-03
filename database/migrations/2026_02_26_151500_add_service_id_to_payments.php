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
        // Add service_id to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->string('service_id')->nullable()->after('customer_id')->comment('Main service tracking ID');
            $table->index('service_id');
        });

        // Add estimate_id to invoices table if it doesn't exist
        if (!Schema::hasColumn('invoices', 'estimate_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('estimate_id')->nullable()->after('work_order_id')->constrained()->onDelete('set null');
            });
        }

        // Add service_id to invoices table if it doesn't exist
        if (!Schema::hasColumn('invoices', 'service_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('service_id')->nullable()->after('estimate_id')->comment('Main service tracking ID');
                $table->index('service_id');
            });
        }

        // Ensure appointments table has service_id (already exists and is unique)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('service_id');
        });

        if (Schema::hasColumn('invoices', 'estimate_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['estimate_id']);
                $table->dropColumn('estimate_id');
            });
        }

        if (Schema::hasColumn('invoices', 'service_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('service_id');
            });
        }
    }
};