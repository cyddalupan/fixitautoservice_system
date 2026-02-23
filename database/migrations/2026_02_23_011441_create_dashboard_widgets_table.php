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
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('widget_type'); // metric_card, chart, table, kpi
            $table->string('widget_title');
            $table->string('metric_name')->nullable(); // Which metric this widget displays
            $table->json('widget_config')->nullable(); // JSON configuration for the widget
            $table->integer('column_position')->default(0);
            $table->integer('row_position')->default(0);
            $table->integer('width')->default(1); // 1-4 columns
            $table->integer('height')->default(1); // 1-4 rows
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_collapsed')->default(false);
            $table->integer('refresh_interval')->default(300); // seconds, 0 = manual
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_visible']);
            $table->index(['user_id', 'column_position', 'row_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
