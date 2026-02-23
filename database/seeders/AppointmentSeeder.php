<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $customers = Customer::all();
        $vehicles = Vehicle::all();
        $technicians = User::where('role', 'technician')->get();
        $advisors = User::where('role', 'service_advisor')->get();
        
        if ($customers->isEmpty() || $vehicles->isEmpty()) {
            $this->command->info('No customers or vehicles found. Please run CustomerSeeder first.');
            return;
        }
        
        // Sample appointment types
        $appointmentTypes = [
            'regular_service',
            'emergency',
            'inspection',
            'diagnostic',
            'repair',
            'maintenance',
            'tire_service',
            'oil_change',
            'brake_service',
            'other',
        ];
        
        // Sample service types
        $serviceTypes = [
            ['Oil Change', 'Tire Rotation'],
            ['Brake Inspection', 'Brake Pad Replacement'],
            ['Engine Diagnostic', 'Spark Plug Replacement'],
            ['Transmission Service', 'Fluid Change'],
            ['AC Service', 'Battery Check'],
            ['Wheel Alignment', 'Tire Replacement'],
            ['Suspension Check', 'Shock Replacement'],
            ['Exhaust System', 'Muffler Replacement'],
        ];
        
        // Create appointments for the next 30 days
        for ($i = 0; $i < 50; $i++) {
            $customer = $customers->random();
            $vehicle = $vehicles->where('customer_id', $customer->id)->first() ?? $vehicles->random();
            $technician = $technicians->random();
            $advisor = $advisors->random();
            
            // Random date within next 30 days
            $daysOffset = rand(0, 30);
            $appointmentDate = Carbon::today()->addDays($daysOffset);
            
            // Random time between 8 AM and 5 PM
            $hour = rand(8, 16);
            $minute = rand(0, 1) ? '00' : '30';
            $appointmentTime = sprintf('%02d:%s', $hour, $minute);
            
            // Determine if waitlist (10% chance)
            $isWaitlist = rand(1, 10) === 1;
            
            // Determine status based on date
            $statuses = ['scheduled', 'confirmed', 'checked_in', 'in_progress', 'completed', 'cancelled', 'no_show'];
            $statusWeights = [20, 30, 10, 5, 20, 10, 5];
            
            if ($isWaitlist) {
                $appointmentStatus = 'scheduled';
            } elseif ($appointmentDate->isPast()) {
                $appointmentStatus = $this->weightedRandom($statuses, $statusWeights);
            } else {
                $appointmentStatus = rand(1, 10) <= 8 ? 'scheduled' : 'confirmed';
            }
            
            // Set timestamps based on status
            $timestamps = [];
            if (in_array($appointmentStatus, ['confirmed', 'checked_in', 'in_progress', 'completed', 'cancelled'])) {
                $timestamps[$appointmentStatus . '_at'] = $appointmentDate->copy()->subDays(rand(0, $daysOffset));
            }
            
            // Determine if reminder sent (for past appointments)
            $reminderSent = $appointmentDate->isPast() && rand(1, 10) <= 7;
            
            Appointment::create([
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'assigned_technician_id' => $technician->id,
                'service_advisor_id' => $advisor->id,
                'appointment_number' => Appointment::generateAppointmentNumber(),
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'appointment_type' => $appointmentTypes[array_rand($appointmentTypes)],
                'appointment_status' => $appointmentStatus,
                'priority' => $this->weightedRandom(['low', 'normal', 'high', 'emergency'], [10, 70, 15, 5]),
                'service_request' => $this->generateServiceRequest(),
                'service_types' => json_encode($serviceTypes[array_rand($serviceTypes)]),
                'estimated_duration' => rand(5, 40) / 10, // 0.5 to 4.0 hours
                'estimated_cost' => rand(5000, 50000) / 100, // $50 to $500
                'bay_number' => rand(1, 8),
                'bay_status' => in_array($appointmentStatus, ['checked_in', 'in_progress']) ? 'occupied' : 'available',
                'sms_reminder_sent' => $reminderSent && rand(1, 2) === 1,
                'email_reminder_sent' => $reminderSent && rand(1, 2) === 1,
                'reminder_sent_at' => $reminderSent ? $appointmentDate->copy()->subDays(1) : null,
                'confirmation_sent' => $appointmentStatus !== 'scheduled' && rand(1, 10) <= 8,
                'confirmation_sent_at' => $appointmentStatus !== 'scheduled' ? $appointmentDate->copy()->subDays(rand(1, 3)) : null,
                'is_waitlist' => $isWaitlist,
                'waitlist_position' => $isWaitlist ? rand(1, 5) : null,
                'requires_deposit' => rand(1, 10) <= 2,
                'deposit_amount' => rand(1, 10) <= 2 ? rand(2000, 10000) / 100 : null,
                'deposit_status' => rand(1, 10) <= 2 ? $this->weightedRandom(['pending', 'paid', 'refunded'], [30, 60, 10]) : null,
                'customer_notes' => rand(1, 10) <= 3 ? $this->generateCustomerNotes() : null,
                'preferred_communication' => json_encode($this->getPreferredCommunication()),
                'scheduled_at' => $appointmentDate->copy()->subDays(rand(1, 14)),
                ...$timestamps,
                'booking_source' => $this->weightedRandom(['website', 'mobile_app', 'phone', 'walk_in'], [40, 20, 30, 10]),
            ]);
        }
        
        $this->command->info('Appointments seeded successfully!');
    }
    
    /**
     * Weighted random selection.
     */
    private function weightedRandom(array $items, array $weights): mixed
    {
        $total = array_sum($weights);
        $random = mt_rand(1, $total);
        
        foreach ($items as $index => $item) {
            $random -= $weights[$index];
            if ($random <= 0) {
                return $item;
            }
        }
        
        return $items[0];
    }
    
    /**
     * Generate a service request.
     */
    private function generateServiceRequest(): string
    {
        $requests = [
            'Vehicle making strange noise when braking',
            'Check engine light is on',
            'Need oil change and tire rotation',
            'AC not blowing cold air',
            'Brakes feel soft and spongy',
            'Transmission shifting roughly',
            'Suspension feels loose over bumps',
            'Exhaust smell in cabin',
            'Battery keeps dying',
            'Regular maintenance service',
            'Pre-purchase inspection',
            'Emergency repair - vehicle won\'t start',
            'Tire replacement needed',
            'Wheel alignment issue',
            'Headlights not working properly',
        ];
        
        return $requests[array_rand($requests)];
    }
    
    /**
     * Generate customer notes.
     */
    private function generateCustomerNotes(): string
    {
        $notes = [
            'Customer prefers morning appointments',
            'Will drop off vehicle the night before',
            'Needs loaner car if available',
            'Allergies to certain cleaning products',
            'Prefers specific technician if available',
            'Will wait for vehicle',
            'Needs detailed estimate before work',
            'Warranty work - bring documentation',
            'First time customer - extra attention needed',
            'VIP customer - handle with care',
        ];
        
        return $notes[array_rand($notes)];
    }
    
    /**
     * Get preferred communication methods.
     */
    private function getPreferredCommunication(): array
    {
        $methods = ['sms', 'email', 'call'];
        $selected = [];
        
        foreach ($methods as $method) {
            if (rand(1, 10) <= 7) {
                $selected[] = $method;
            }
        }
        
        return empty($selected) ? ['sms'] : $selected;
    }
}