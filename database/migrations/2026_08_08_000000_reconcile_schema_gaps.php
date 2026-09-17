<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schema reconciliation — fills columns referenced by models/controllers
 * that were never created by any migration (post-run rewritten migrations
 * left the DB schema behind the code).
 */
return new class extends Migration
{
    public function up(): void
    {
        // job_orders.estimate_id — Estimate::jobOrder() hasOne + convertToJobOrder()
        if (!Schema::hasColumn('job_orders', 'estimate_id')) {
            Schema::table('job_orders', function (Blueprint $table) {
                $table->foreignId('estimate_id')->nullable()->after('vehicle_id')
                    ->constrained('estimates')->nullOnDelete();
            });
        }

        // estimates gaps
        if (!Schema::hasColumn('estimates', 'inspection_id')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->foreignId('inspection_id')->nullable()->after('id')
                    ->constrained('vehicle_inspections')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('estimates', 'tax_total')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->decimal('tax_total', 12, 2)->nullable()->after('tax_amount');
            });
        }
        if (!Schema::hasColumn('estimates', 'user_id')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('service_advisor_id')
                    ->constrained('users')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('estimates', 'approved_by')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->foreignId('approved_by')->nullable()->after('user_id')
                    ->constrained('users')->nullOnDelete();
            });
        }

        // invoice_items gaps
        if (!Schema::hasColumn('invoice_items', 'total_price')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->decimal('total_price', 12, 2)->nullable()->after('unit_price');
            });
        }
        if (!Schema::hasColumn('invoice_items', 'taxable')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->boolean('taxable')->default(false)->after('total_price');
            });
        }

        // payments.payment_method — Payment model scope/accessor
        if (!Schema::hasColumn('payments', 'payment_method')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('payment_method')->nullable()->after('amount');
            });
        }
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estimate_id');
        });
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inspection_id');
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('tax_total');
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['total_price', 'taxable']);
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
