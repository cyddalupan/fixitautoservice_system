<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->morphs('archivable'); // archivable_id + archivable_type
            $table->string('source_module'); // 'work_order', 'estimate', 'payment', 'invoice'
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('original_data');
            $table->text('notes')->nullable();
            $table->timestamp('archived_at');
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            
            // Indexes for fast batch querying
            $table->index('source_module');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
