<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Adds a human-readable Repair Order reference number
 * (e.g. RO-20260924-0001), used when a Repair Quotation is
 * "promoted" back into a Repair Order.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('vehicle_inspections', 'reference_number')) {
            Schema::table('vehicle_inspections', function (Blueprint $table) {
                $table->string('reference_number', 50)->nullable()->after('id');
            });
        }

        // Backfill every existing Repair Order with a stable reference number.
        $rows = DB::table('vehicle_inspections')
            ->whereNull('reference_number')
            ->orderBy('id')
            ->get(['id', 'created_at']);

        foreach ($rows as $row) {
            $date = $row->created_at ? date('Ymd', strtotime($row->created_at)) : date('Ymd');
            DB::table('vehicle_inspections')
                ->where('id', $row->id)
                ->update([
                    'reference_number' => 'RO-' . $date . '-' . str_pad((string) $row->id, 4, '0', STR_PAD_LEFT),
                ]);
        }

        // Unique index so no two Repair Orders can ever share a reference.
        try {
            Schema::table('vehicle_inspections', function (Blueprint $table) {
                $table->unique('reference_number', 'vehicle_inspections_reference_number_unique');
            });
        } catch (\Throwable $e) {
            // Index already present — safe to ignore.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('vehicle_inspections', function (Blueprint $table) {
                $table->dropUnique('vehicle_inspections_reference_number_unique');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        if (Schema::hasColumn('vehicle_inspections', 'reference_number')) {
            Schema::table('vehicle_inspections', function (Blueprint $table) {
                $table->dropColumn('reference_number');
            });
        }
    }
};
