<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // VIN Decoding fields
            $table->string('trim')->nullable()->after('model');
            $table->string('body_style')->nullable()->after('trim');
            $table->string('drive_type')->nullable()->after('body_style');
            $table->string('manufacturer')->nullable()->after('drive_type');
            $table->string('plant_code')->nullable()->after('manufacturer');
            $table->string('series')->nullable()->after('plant_code');
            $table->string('vehicle_class')->nullable()->after('series');
            $table->integer('doors')->nullable()->after('vehicle_class');
            $table->integer('passenger_capacity')->nullable()->after('doors');
            $table->decimal('gross_vehicle_weight', 10, 2)->nullable()->after('passenger_capacity');
            $table->string('country_of_origin')->nullable()->after('gross_vehicle_weight');
            
            // VIN decoding metadata
            $table->timestamp('vin_decoded_at')->nullable()->after('country_of_origin');
            $table->string('vin_source')->nullable()->after('vin_decoded_at');
            $table->boolean('vin_valid')->default(false)->after('vin_source');
            $table->text('vin_validation_notes')->nullable()->after('vin_valid');
            
            // Recall tracking enhancements
            $table->integer('open_recall_count')->default(0)->after('has_recall');
            $table->date('last_recall_check')->nullable()->after('open_recall_count');
            $table->boolean('recall_check_required')->default(false)->after('last_recall_check');
            
            // Service history enhancements
            $table->json('detailed_service_history')->nullable()->after('service_history_summary');
            $table->date('first_service_date')->nullable()->after('detailed_service_history');
            $table->integer('total_service_cost')->default(0)->after('first_service_date');
            
            // Indexes for new fields
            $table->index(['make', 'model', 'year', 'trim']);
            $table->index('vin_decoded_at');
            $table->index('open_recall_count');
            $table->index('last_recall_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Remove VIN Decoding fields
            $table->dropColumn([
                'trim',
                'body_style', 
                'drive_type',
                'manufacturer',
                'plant_code',
                'series',
                'vehicle_class',
                'doors',
                'passenger_capacity',
                'gross_vehicle_weight',
                'country_of_origin',
                'vin_decoded_at',
                'vin_source',
                'vin_valid',
                'vin_validation_notes',
                'open_recall_count',
                'last_recall_check',
                'recall_check_required',
                'detailed_service_history',
                'first_service_date',
                'total_service_cost'
            ]);
            
            // Drop indexes
            $table->dropIndex(['make_model_year_trim_index']);
            $table->dropIndex(['vin_decoded_at']);
            $table->dropIndex(['open_recall_count']);
            $table->dropIndex(['last_recall_check']);
        });
    }
};