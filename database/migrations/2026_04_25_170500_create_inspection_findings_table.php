<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inspection_findings')) {
            Schema::create('inspection_findings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inspection_id')->constrained('vehicle_inspections')->cascadeOnDelete();
                $table->string('category', 100)->default('Other');
                $table->string('issue_title', 255);
                $table->text('detailed_notes')->nullable();
                $table->enum('severity', ['low','medium','high','critical'])->default('medium');
                $table->text('recommended_action')->nullable();
                $table->enum('estimated_urgency', ['routine','soon','urgent','immediate'])->default('routine');
                $table->decimal('estimated_cost', 10, 2)->nullable();
                $table->foreignId('tech_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('photo_path', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_linked_to_estimate')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_findings');
    }
};
