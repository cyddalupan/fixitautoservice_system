<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            if (!Schema::hasColumn('estimate_items', 'category')) {
                $table->enum('category', ['parts', 'labor', 'service', 'materials', 'other'])
                    ->default('parts')->after('item_name');
            }
            if (!Schema::hasColumn('estimate_items', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('estimate_items', 'discount_type')) {
                $table->enum('discount_type', ['percentage', 'fixed'])->default('fixed')->after('discount');
            }
            if (!Schema::hasColumn('estimate_items', 'tax')) {
                $table->decimal('tax', 12, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('estimate_items', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('tax');
            }
            if (!Schema::hasColumn('estimate_items', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('tax_rate');
            }
            if (!Schema::hasColumn('estimate_items', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('subtotal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropColumn(['category', 'discount', 'discount_type', 'tax', 'tax_rate', 'subtotal', 'sort_order']);
        });
    }
};
