<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_finding_groups', function (Blueprint $table) {
            // Whether the group name is auto-derived from the categories of its
            // cards (true) or was typed by the user (false). Persisted so the
            // board keeps auto-naming after a page reload.
            if (!Schema::hasColumn('inspection_finding_groups', 'auto_name')) {
                $table->boolean('auto_name')->default(true)->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inspection_finding_groups', function (Blueprint $table) {
            if (Schema::hasColumn('inspection_finding_groups', 'auto_name')) {
                $table->dropColumn('auto_name');
            }
        });
    }
};
