<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old ENUM and use VARCHAR for flexible statuses
        Schema::table('estimates', function (Blueprint $table) {
            $table->string('status', 50)->default('draft')->change();
        });

        // Add new columns to estimates
        Schema::table('estimates', function (Blueprint $table) {
            if (!Schema::hasColumn('estimates', 'discount_type')) {
                $table->enum('discount_type', ['percentage', 'fixed'])->nullable()->after('tax_amount');
            }
            if (!Schema::hasColumn('estimates', 'discount_value')) {
                $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('estimates', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
            }
            if (!Schema::hasColumn('estimates', 'deposit_required')) {
                $table->decimal('deposit_required', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('estimates', 'balance_remaining')) {
                $table->decimal('balance_remaining', 12, 2)->default(0)->after('deposit_required');
            }
            if (!Schema::hasColumn('estimates', 'parts_total')) {
                $table->decimal('parts_total', 12, 2)->default(0)->after('balance_remaining');
            }
            if (!Schema::hasColumn('estimates', 'labor_total')) {
                $table->decimal('labor_total', 12, 2)->default(0)->after('parts_total');
            }
            if (!Schema::hasColumn('estimates', 'labor_hours')) {
                $table->decimal('labor_hours', 8, 2)->nullable()->change();
            }
            if (!Schema::hasColumn('estimates', 'labor_rate')) {
                $table->decimal('labor_rate', 10, 2)->nullable()->change();
            }
            if (!Schema::hasColumn('estimates', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('estimates', 'customer_notes')) {
                $table->text('customer_notes')->nullable()->after('internal_notes');
            }
            if (!Schema::hasColumn('estimates', 'terms')) {
                $table->text('terms')->nullable()->after('customer_notes');
            }
            if (!Schema::hasColumn('estimates', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('terms');
            }
            if (!Schema::hasColumn('estimates', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('estimates', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('estimates', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('estimates', 'viewed_at')) {
                $table->timestamp('viewed_at')->nullable()->after('sent_at');
            }
            if (!Schema::hasColumn('estimates', 'appointment_id')) {
                $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete()->after('vehicle_id');
            }
        });

        // Update existing 'approved' status records to have approved_at
        DB::table('estimates')->where('status', 'approved')->whereNull('approved_at')->update(['approved_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type', 'discount_value', 'discount_amount',
                'deposit_required', 'balance_remaining', 'parts_total', 'labor_total',
                'internal_notes', 'customer_notes', 'terms',
                'approved_at', 'rejected_at', 'rejection_reason', 'sent_at', 'viewed_at',
            ]);
        });
    }
};
