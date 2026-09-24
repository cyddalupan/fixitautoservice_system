<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `is_declined` to inspection_findings.
 *
 * A finding marked as declined = the customer did NOT push through with it, so it is
 * separated out of the Repair Quotation (excluded from totals / printed quotation).
 * The flag is toggled by dragging a finding card into the "Not Pursued" drop zone on
 * the Repair Quotation edit page.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('inspection_findings', 'is_declined')) {
            Schema::table('inspection_findings', function (Blueprint $table) {
                $table->boolean('is_declined')->default(false)->after('is_linked_to_estimate');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inspection_findings', 'is_declined')) {
            Schema::table('inspection_findings', function (Blueprint $table) {
                $table->dropColumn('is_declined');
            });
        }
    }
};
