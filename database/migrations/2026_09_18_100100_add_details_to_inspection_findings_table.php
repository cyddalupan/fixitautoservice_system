<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_findings', function (Blueprint $table) {
            // Grouping (drag & drop cards share one labor cost)
            if (!Schema::hasColumn('inspection_findings', 'group_id')) {
                $table->foreignId('group_id')->nullable()->after('inspection_id')
                    ->constrained('inspection_finding_groups')->nullOnDelete();
            }
            // Parts / pricing detail per finding
            if (!Schema::hasColumn('inspection_findings', 'part_name')) {
                $table->string('part_name', 255)->nullable()->after('issue_title');
            }
            if (!Schema::hasColumn('inspection_findings', 'remarks')) {
                $table->text('remarks')->nullable()->after('detailed_notes');
            }
            if (!Schema::hasColumn('inspection_findings', 'quantity')) {
                $table->decimal('quantity', 10, 2)->default(1)->after('severity');
            }
            if (!Schema::hasColumn('inspection_findings', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inspection_findings', function (Blueprint $table) {
            foreach (['group_id', 'part_name', 'remarks', 'quantity', 'unit_price'] as $col) {
                if (Schema::hasColumn('inspection_findings', $col)) {
                    if ($col === 'group_id') {
                        $table->dropConstrainedForeignId('group_id');
                    } else {
                        $table->dropColumn($col);
                    }
                }
            }
        });
    }
};
