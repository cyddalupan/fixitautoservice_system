<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `is_quotation_added` to inspection_findings.
 *
 * A finding with this flag = it was added directly on the Repair Quotation page
 * (not carried over from the Repair Order inspection). On the quotation, only these
 * items may be deleted; findings that came from the Repair Order are locked.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('inspection_findings', 'is_quotation_added')) {
            Schema::table('inspection_findings', function (Blueprint $table) {
                $table->boolean('is_quotation_added')->default(false)->after('is_declined');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inspection_findings', 'is_quotation_added')) {
            Schema::table('inspection_findings', function (Blueprint $table) {
                $table->dropColumn('is_quotation_added');
            });
        }
    }
};
