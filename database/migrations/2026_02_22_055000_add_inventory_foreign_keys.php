<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Decoupled FK constraints for inventory → inventory_categories /
 * inventory_suppliers.
 *
 * The original create_inventory_table migration (2026_02_22_054526) declared
 * inline FK constraints via ->constrained('inventory_categories') and
 * ->constrained('inventory_suppliers'), but those parent tables are created by
 * later migrations (2026_02_22_054529 / 054535). Because Laravel runs
 * migrations in filename order, the inventory CREATE ran first and failed with
 * "Failed to open the referenced table 'inventory_categories'" on fresh
 * databases. In production the table had been created standalone with no FKs,
 * so the migrations table/stamp also drifted.
 *
 * This migration adds the FKs after the parent tables exist, so a clean
 * migrate --force succeeds on both fresh and existing databases.
 */
class AddInventoryForeignKeys extends Migration
{
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->foreign('category_id', 'inventory_category_id_foreign')
                  ->references('id')->on('inventory_categories')
                  ->onDelete('cascade');
            $table->foreign('supplier_id', 'inventory_supplier_id_foreign')
                  ->references('id')->on('inventory_suppliers')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign('inventory_category_id_foreign');
            $table->dropForeign('inventory_supplier_id_foreign');
        });
    }
}
