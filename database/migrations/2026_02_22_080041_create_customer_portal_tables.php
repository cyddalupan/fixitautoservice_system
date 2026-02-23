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
        // Customer portal users table (separate from admin users)
        Schema::create('portal_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('verification_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->boolean('is_active')->default(true);
            $table->string('two_factor_secret')->nullable();
            $table->string('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->timestamps();
        });

        // Customer portal sessions (for tracking portal activity)
        Schema::create('portal_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_user_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
            $table->timestamps();
        });

        // Customer portal notifications
        Schema::create('portal_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Customer portal preferences
        Schema::create('portal_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_user_id')->constrained()->onDelete('cascade');
            $table->string('notification_email_appointments')->default('immediate');
            $table->string('notification_email_reminders')->default('24_hours');
            $table->string('notification_email_promotions')->default('weekly');
            $table->string('notification_sms_appointments')->default('immediate');
            $table->string('notification_sms_reminders')->default('24_hours');
            $table->string('notification_sms_promotions')->default('never');
            $table->boolean('receive_service_reminders')->default(true);
            $table->boolean('receive_promotional_offers')->default(true);
            $table->boolean('receive_birthday_offers')->default(true);
            $table->boolean('receive_newsletter')->default(true);
            $table->string('preferred_contact_method')->default('email');
            $table->string('preferred_communication_time')->default('anytime');
            $table->timestamps();
        });

        // Customer portal documents (for sharing documents with customers)
        Schema::create('portal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('document_type'); // invoice, estimate, inspection_report, service_record, warranty
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->integer('file_size');
            $table->boolean('is_shared')->default(false);
            $table->timestamp('shared_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();
        });

        // Customer portal messages (communication between shop and customer)
        Schema::create('portal_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('subject');
            $table->text('message');
            $table->string('message_type'); // appointment_update, service_update, general, question, quote
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('requires_action')->default(false);
            $table->string('action_type')->nullable(); // approve_estimate, schedule_appointment, provide_info
            $table->json('action_data')->nullable();
            $table->timestamps();
        });

        // Customer portal service requests (customers can request services)
        Schema::create('portal_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            $table->string('request_type'); // maintenance, repair, diagnostic, quote, other
            $table->text('description');
            $table->json('service_items')->nullable(); // JSON array of requested services
            $table->string('urgency'); // routine, soon, urgent
            $table->string('preferred_date')->nullable();
            $table->string('preferred_time')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, scheduled, completed, cancelled
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Customer portal reviews
        Schema::create('portal_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('rating')->default(5); // 1-5 stars
            $table->text('review_text')->nullable();
            $table->json('review_ratings')->nullable(); // JSON for specific ratings (quality, timeliness, communication, etc.)
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->string('response_text')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        // Customer portal loyalty points
        Schema::create('portal_loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type'); // earned, redeemed, expired, adjusted
            $table->integer('points');
            $table->text('description');
            $table->foreignId('related_appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('related_work_order_id')->nullable()->constrained('work_orders')->onDelete('set null');
            $table->integer('balance_after');
            $table->timestamps();
        });

        // Customer portal appointments (view-only for customers)
        Schema::create('portal_appointment_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignId('portal_user_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_viewed_at');
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_appointment_views');
        Schema::dropIfExists('portal_loyalty_points');
        Schema::dropIfExists('portal_reviews');
        Schema::dropIfExists('portal_service_requests');
        Schema::dropIfExists('portal_messages');
        Schema::dropIfExists('portal_documents');
        Schema::dropIfExists('portal_preferences');
        Schema::dropIfExists('portal_notifications');
        Schema::dropIfExists('portal_sessions');
        Schema::dropIfExists('portal_users');
    }
};