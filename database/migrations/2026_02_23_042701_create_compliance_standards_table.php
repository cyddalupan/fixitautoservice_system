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
        Schema::create('compliance_standards', function (Blueprint $table) {
            $table->id();
            $table->string('code'); // e.g., 'ASE', 'OSHA', 'EPA', 'DOT'
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // e.g., 'safety', 'environmental', 'technical', 'administrative'
            $table->text('requirements')->nullable();
            $table->date('effective_date');
            $table->date('expiration_date')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->integer('revision_number')->default(1);
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['code', 'revision_number']);
            $table->index(['category', 'is_mandatory']);
            $table->index('expiration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_standards');
    }
};