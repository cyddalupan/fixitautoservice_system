<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repair Quotations enhancements (all additive / non-destructive):
 *  - estimates.version            → re-send / revision tracking
 *  - estimate_item_groups table   → group quotation items with ONE shared labor price
 *  - estimate_items.group_id      → which group an item belongs to (nullable)
 *  - estimate_items.item_status   → per-line customer decision (quoted/accepted/rejected/deferred)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Version counter on the quotation itself
        if (!Schema::hasColumn('estimates', 'version')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->integer('version')->default(1)->after('status');
            });
        }

        // 2) Groups (like findings groups) — one shared labor cost per group
        if (!Schema::hasTable('estimate_item_groups')) {
            Schema::create('estimate_item_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('estimate_id')->constrained('estimates')->cascadeOnDelete();
                $table->string('name')->default('Group');
                $table->decimal('labor_cost', 12, 2)->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 3) Item → group link + per-line status
        Schema::table('estimate_items', function (Blueprint $table) {
            if (!Schema::hasColumn('estimate_items', 'group_id')) {
                $table->foreignId('group_id')->nullable()->after('estimate_id')
                    ->constrained('estimate_item_groups')->nullOnDelete();
            }
            if (!Schema::hasColumn('estimate_items', 'item_status')) {
                $table->string('item_status', 20)->default('quoted')->after('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            if (Schema::hasColumn('estimate_items', 'group_id')) {
                $table->dropConstrainedForeignId('group_id');
            }
            if (Schema::hasColumn('estimate_items', 'item_status')) {
                $table->dropColumn('item_status');
            }
        });
        Schema::dropIfExists('estimate_item_groups');
        Schema::table('estimates', function (Blueprint $table) {
            if (Schema::hasColumn('estimates', 'version')) {
                $table->dropColumn('version');
            }
        });
    }
};
