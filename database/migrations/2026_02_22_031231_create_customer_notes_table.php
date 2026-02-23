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
        Schema::create('customer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('note_type', ['general', 'preference', 'complaint', 'compliment', 'follow_up', 'reminder'])->default('general');
            $table->text('content');
            $table->boolean('is_important')->default(false);
            $table->boolean('requires_follow_up')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->boolean('follow_up_completed')->default(false);
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['customer_id', 'note_type'], 'cust_notes_type_idx');
            $table->index(['customer_id', 'is_important'], 'cust_notes_important_idx');
            $table->index(['customer_id', 'requires_follow_up', 'follow_up_completed'], 'cust_notes_followup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_notes');
    }
};