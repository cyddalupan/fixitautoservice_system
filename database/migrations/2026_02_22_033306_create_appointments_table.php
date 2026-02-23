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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('service_advisor_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Appointment Details
            $table->string('appointment_number')->unique();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('appointment_type', ['regular_service', 'emergency', 'inspection', 'diagnostic', 'repair', 'maintenance', 'tire_service', 'oil_change', 'brake_service', 'other']);
            $table->enum('appointment_status', ['scheduled', 'confirmed', 'checked_in', 'in_progress', 'completed', 'cancelled', 'no_show', 'rescheduled']);
            $table->enum('priority', ['low', 'normal', 'high', 'emergency'])->default('normal');
            
            // Service Details
            $table->text('service_request')->nullable();
            $table->json('service_types')->nullable(); // Array of service types
            $table->decimal('estimated_duration', 5, 2)->nullable(); // in hours
            $table->decimal('estimated_cost', 10, 2)->nullable();
            
            // Bay Management
            $table->integer('bay_number')->nullable();
            $table->enum('bay_status', ['available', 'occupied', 'maintenance'])->nullable();
            
            // Communication
            $table->boolean('sms_reminder_sent')->default(false);
            $table->boolean('email_reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->boolean('confirmation_sent')->default(false);
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->boolean('follow_up_sent')->default(false);
            $table->timestamp('follow_up_sent_at')->nullable();
            
            // Waitlist Management
            $table->boolean('is_waitlist')->default(false);
            $table->integer('waitlist_position')->nullable();
            $table->timestamp('waitlist_converted_at')->nullable();
            
            // No-Show Prevention
            $table->integer('no_show_count')->default(0);
            $table->timestamp('last_no_show_at')->nullable();
            $table->boolean('requires_deposit')->default(false);
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->enum('deposit_status', ['pending', 'paid', 'refunded', 'forfeited'])->nullable();
            
            // Customer Preferences
            $table->text('customer_notes')->nullable();
            $table->json('preferred_communication')->nullable(); // ['sms', 'email', 'call']
            $table->string('preferred_contact_time')->nullable();
            
            // Technician Assignment
            $table->json('required_skills')->nullable(); // Skills required for this appointment
            $table->json('technician_preferences')->nullable(); // Preferred technicians
            
            // Timeline
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Online Booking
            $table->string('booking_source')->nullable(); // ['website', 'mobile_app', 'phone', 'walk_in']
            $table->ipAddress('booking_ip')->nullable();
            $table->string('booking_referrer')->nullable();
            
            // Metadata
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['appointment_date', 'appointment_time']);
            $table->index(['appointment_status', 'appointment_date']);
            $table->index(['customer_id', 'appointment_date']);
            $table->index(['assigned_technician_id', 'appointment_date']);
            $table->index(['bay_number', 'appointment_date']);
            $table->index(['appointment_type', 'appointment_date']);
            $table->index(['priority', 'appointment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};