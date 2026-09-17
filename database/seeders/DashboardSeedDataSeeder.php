<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Seeds realistic production-like data across the core business tables so the
 * dashboard and modules have meaningful content to work with. ADDITIVE only —
 * safe to run against an existing database (appends, never deletes).
 */
class DashboardSeedDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding realistic Fix-It data...');

        // ------------------------------------------------------------------
        // 1. Technicians & Service Advisors (users) — needed as FKs
        // ------------------------------------------------------------------
        $technicians = [];
        $advisor = null;

        $techNames = [
            ['Juan', 'Dela Cruz'], ['Miguel', 'Santos'], ['Andres', 'Reyes'],
            ['Carlos', 'Garcia'], ['Ramon', 'Mendoza'],
        ];
        foreach ($techNames as $i => [$fn, $ln]) {
            $email = 'tech' . ($i + 1) . '@fixit.test';
            $u = User::where('email', $email)->first();
            if (!$u) {
                $u = User::create([
                    'name' => $fn . ' ' . $ln,
                    'email' => $email,
                    'password' => bcrypt('FixIt1234'),
                    'role' => 'technician',
                    'email_verified_at' => now(),
                ]);
            }
            $technicians[] = $u;
        }

        $advisor = User::where('email', 'advisor@fixit.test')->first();
        if (!$advisor) {
            $advisor = User::create([
                'name' => 'Marketing Fix-It',
                'email' => 'advisor@fixit.test',
                'password' => bcrypt('FixIt1234'),
                'role' => 'service_advisor',
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('  users: ' . (count($technicians) + ($advisor ? 1 : 0)) . ' technician/advisor');

        // ------------------------------------------------------------------
        // 2. Customers & Vehicles
        // ------------------------------------------------------------------
        $customerSeeds = [
            ['Juan','Dela Cruz','juan.delacruz@gmail.com','09171234567','24 Mabini St','Manila','NCR','1000'],
            ['Maria','Santos','maria.santos@gmail.com','09181234568','12 Rizal Ave','Quezon City','NCR','1100'],
            ['Jose','Ramos','jose.ramos@yahoo.com','09171234569','88 Katipunan Rd','Quezon City','NCR','1110'],
            ['Ana','Villanueva','ana.villanueva@gmail.com','09181234570','45 Banawe St','Manila','NCR','1006'],
            ['Pedro','Castillo','pedro.castillo@outlook.com','09171234571','6 MacArthur Hwy','Caloocan','NCR','1400'],
            ['Liza','Aquino','liza.aquino@gmail.com','09181234572','33 Commonwealth Ave','Quezon City','NCR','1121'],
            ['Ramon','Bautista','ramon.bautista@gmail.com','09171234573','77 Buendia Ave','Makati','NCR','1200'],
            ['Sofia','Navarro','sofia.navarro@yahoo.com','09181234574','21 A. Mabini St','Pasig','NCR','1600'],
            ['Marco','Lopez','marco.lopez@gmail.com','09171234575','98 Edsa Guerrilla','Mandaluyong','NCR','1550'],
            ['Elena','Gonzales','elena.gonzales@gmail.com','09181234576','15 N. Domingo','San Juan','NCR','1500'],
            ['Chris','Lim','chris.lim@yahoo.com','09171234577','201 Araneta Ave','Quezon City','NCR','1105'],
            ['Grace','Fernandez','grace.fernandez@gmail.com','09181234578','54 Taft Ave','Pasay','NCR','1300'],
            ['Dante','Salazar','dante.salazar@gmail.com','09171234579','33 Mabuhay St','Manila','NCR','1002'],
            ['Hazel','Torres','hazel.torres@gmail.com','09181234580','66 Quezon Blvd','Manila','NCR','1004'],
            ['Paolo','Mercado','paolo.mercado@gmail.com','09171234581','82 Shaw Blvd','Pasig','NCR','1603'],
            ['Carmen','Rivera','carmen.rivera@gmail.com','09181234582','9 Kalaw St','Manila','NCR','1001'],
            ['Rico','Dizon','rico.dizon@gmail.com','09171234583','27 Panay Ave','Quezon City','NCR','1103'],
            ['Bianca','Cruz','bianca.cruz@gmail.com','09181234584','71 Gil Puyat','Makati','NCR','1209'],
            ['Allan','Roxas','allan.roxas@gmail.com','09171234585','14 Jupiter St','Makati','NCR','1203'],
            ['Nena','Padilla','nena.padilla@gmail.com','09181234586','50 Visayas Ave','Quezon City','NCR','1114'],
            ['Rey','Ocampo','rey.ocampo@gmail.com','09171234587','6 LRT Rd','Manila','NCR','1007'],
            ['Jenny','Alvarez','jenny.alvarez@gmail.com','09181234588','240 Times St','Quezon City','NCR','1118'],
            ['Dennis','Salvador','dennis.salvador@gmail.com','09171234589','18 Yakal St','Makati','NCR','1229'],
            ['Myla','De Guzman','myla.deguzman@gmail.com','09181234590','93 Sucat Rd','Muntinlupa','NCR','1700'],
            ['Vic','Hernandez','vic.hernandez@gmail.com','09171234591','29 Alabang','Muntinlupa','NCR','1781'],
            ['Kim','Vargas','kim.vargas@gmail.com','09181234592','44 Bambang St','Manila','NCR','1009'],
            ['Jack','Suarez','jack.suarez@gmail.com','09171234593','12 Pioneer St','Pasig','NCR','1605'],
            ['Ivy','Mallari','ivy.mallari@gmail.com','09181234594','58 Kalayaan Ave','Quezon City','NCR','1100'],
            ['Ben','Castro','ben.castro@gmail.com','09171234595','3 P. Ocampo','Manila','NCR','1007'],
            ['Tina','Manalo','tina.manalo@gmail.com','09181234596','76 España Blvd','Manila','NCR','1008'],
        ];

        $vehicleSeeds = [
            'car' => ['Toyota','Camry','Vios','Corolla','Altis','Yaris','Innova'],
            'suv' => ['Toyota','Fortuner','Hilux','Montero','Everest','Tucson','MUX'],
            'sedan' => ['Honda','Civic','City','Accord','Mazda3','Mirage'],
        ];
        $allMakes = ['Toyota','Honda','Mitsubishi','Nissan','Mazda','Suzuki','Ford','Hyundai','Isuzu'];
        $colors = ['White','Silver','Black','Gray','Red','Blue','Dark Gray','Beige'];

        $customerIds = [];
        foreach ($customerSeeds as $i => [$fn, $ln, $em, $ph, $addr, $city, $state, $zip]) {
            $existing = Customer::where('email', $em)->first();
            if ($existing) { $customerIds[] = $existing->id; continue; }
            $c = Customer::create([
                'first_name' => $fn,
                'last_name' => $ln,
                'email' => $em,
                'phone' => $ph,
                'address' => $addr,
                'city' => $city,
                'state' => $state,
                'zip_code' => $zip,
                'customer_type' => array_rand(array_flip(['individual','individual','individual','commercial'])),
                'payment_terms' => ['net_15','net_30','net_60','cod'][array_rand(['net_15','net_30','net_60','cod'])],
                'is_active' => 1,
                'customer_since' => Carbon::today()->subDays(rand(30, 700)),
                'loyalty_points' => rand(0, 1200),
                'preferred_contact' => ['email','phone','sms'][array_rand(['email','phone','sms'])],
                'balance' => 0,
            ]);
            $customerIds[] = $c->id;

            // 1-3 vehicles per customer
            $nv = rand(1, 3);
            for ($j = 0; $j < $nv; $j++) {
                $make = $allMakes[array_rand($allMakes)];
                $modelPool = $vehicleSeeds[array_rand($vehicleSeeds)];
                // make/model loosely coherent
                $model = $modelPool[array_rand($modelPool)];
                if (in_array($make, ['Toyota','Honda','Mitsubishi'])) {
                    $tp = ['Camry','Vios','Corolla','Altis','Civic','City','Mirage','Lancer','Montero','Fortuner'];
                    $model = $tp[array_rand($tp)];
                }
                Vehicle::create([
                    'customer_id' => $c->id,
                    'vin' => 'FIXIT' . strtoupper(Str::random(12)),
                    'license_plate' => $this->plate(),
                    'make' => $make,
                    'model' => $model,
                    'year' => rand(2008, 2024),
                    'color' => $colors[array_rand($colors)],
                    'vehicle_type' => ['car','car','car','suv','van'][array_rand(['car','car','car','suv','van'])],
                    'transmission' => ['automatic','automatic','manual'][array_rand(['automatic','automatic','manual'])],
                    'fuel_type' => ['gasoline','diesel','hybrid'][array_rand(['gasoline','gasoline','diesel'])],
                    'odometer' => rand(20000, 180000),
                    'is_active' => 1,
                ]);
            }
        }

        $this->command->info('  customers: ' . count($customerIds));
        $vehicles = Vehicle::whereIn('customer_id', $customerIds)->get();
        $this->command->info('  vehicles: ' . $vehicles->count());

        if ($vehicles->isEmpty() || empty($customerIds)) {
            $this->command->error('No vehicles/customers seeded — aborting.');
            return;
        }

        $vids = $vehicles->pluck('id')->all();
        $cids = array_values(array_unique($customerIds));

        // ------------------------------------------------------------------
        // 3. Appointments — spread over last 4 months + next 3 weeks
        // ------------------------------------------------------------------
        $apptTypes = ['regular_service','maintenance','oil_change','brake_service','diagnostic','tire_service','inspection','repair','preventive_maintenance','aircon_service','underchassis_service','engine_service','electrical_repair','general_checkup'];
        $apptStatus = ['scheduled','confirmed','checked_in','in_progress','completed','completed','completed','completed','cancelled'];
        $requests = [
            'Oil change and check-up','Engine making ticking noise','Brakes feel spongy','Aircon not cooling','Check engine light on','Tire replacement','Wheel alignment','Transmission shifting rough','Regular 10,000km maintenance','Suspension clunking over bumps','Battery kept dying','Pre-purchase inspection','Exhaust smell in cabin','Coolant leaking under car','Home service request please',
        ];

        for ($i = 0; $i < 140; $i++) {
            $cid = $cids[array_rand($cids)];
            $vehicle = $vehicles->where('customer_id', $cid)->first() ?? $vehicles->random();
            $tech = $technicians[array_rand($technicians)];
            $date = Carbon::today()->subDays(rand(0, 120));
            $hour = rand(8, 16); $minute = rand(0,1) ? '00' : '30';
            $isPast = $date->lt(Carbon::today());
            $status = $isPast ? $apptStatus[array_rand($apptStatus)] : (rand(1,10)<=8 ? 'scheduled' : 'confirmed');
            $cost = rand(1500, 45000);
            Appointment::create([
                'customer_id' => $cid,
                'vehicle_id' => $vehicle->id,
                'assigned_technician_id' => $tech->id,
                'service_advisor_id' => $advisor->id,
                'appointment_number' => Appointment::generateAppointmentNumber(),
                'appointment_date' => $date,
                'appointment_time' => sprintf('%02d:%s:00', $hour, $minute),
                'appointment_type' => $apptTypes[array_rand($apptTypes)],
                'appointment_status' => $status,
                'priority' => $this->w(['normal','normal','normal','high','low'],[50,40,10]), // placeholder normalize
                'service_request' => $requests[array_rand($requests)],
                'service_types' => json_encode([$this->serviceTypeKey()]),
                'estimated_duration' => rand(5,40)/10,
                'estimated_cost' => $cost,
                'bay_number' => rand(1,8),
                'bay_status' => in_array($status, ['checked_in','in_progress']) ? 'occupied' : 'available',
                'sms_reminder_sent' => $isPast && rand(1,10)<=7 ? 1 : 0,
                'email_reminder_sent' => $isPast && rand(1,10)<=7 ? 1 : 0,
                'booking_source' => $this->w(['website','phone','walk_in','mobile_app'],[30,30,20,20]),
                'scheduled_at' => $date->copy()->subDays(rand(0,14)),
                'confirmed_at' => $status !== 'scheduled' ? $date->copy()->subDays(rand(0,3)) : null,
                'checked_in_at' => in_array($status,['checked_in','in_progress','completed']) ? $date->copy()->addHours(rand(0,2)) : null,
                'completed_at' => in_array($status,['completed','cancelled']) ? $date->copy()->addHours(rand(3,12)) : null,
            ]);
        }
        $this->command->info('  appointments: 140');

        // ------------------------------------------------------------------
        // 4. Vehicle Inspections
        // ------------------------------------------------------------------
        $inspStatus = ['passed','passed','passed','attention_needed','failed'];
        for ($i = 0; $i < 45; $i++) {
            $vehicle = $vehicles->random();
            $cid = $vehicle->customer_id;
            $date = Carbon::today()->subDays(rand(0, 100));
            $status = $inspStatus[array_rand($inspStatus)];
            $checked = rand(25, 45);
            $passed = max(0, $checked - rand(0, 8));
            $failed = rand(0, 5);
            $attention = max(0, $checked - $passed - $failed);
            VehicleInspection::create([
                'customer_id' => $cid,
                'vehicle_id' => $vehicle->id,
                'technician_id' => $technicians[array_rand($technicians)]->id,
                'service_advisor_id' => $advisor->id,
                'inspection_type' => json_encode(['Preventive Maintenance Check']),
                'inspection_status' => $status,
                'inspection_name' => 'Standard Vehicle Health Check',
                'inspection_notes' => 'Full inspection performed by technician',
                'total_items_checked' => $checked,
                'items_passed' => $passed,
                'items_failed' => $failed,
                'items_attention_needed' => $attention,
                'items_not_applicable' => rand(0, 6),
                'inspection_score' => round(($passed / max(1,$checked)) * 100, 1),
                'has_safety_concerns' => $failed > 2 ? 1 : 0,
                'has_urgent_issues' => $failed > 3 ? 1 : 0,
                'inspection_started_at' => $date->copy()->addHours(8),
                'inspection_completed_at' => $date->copy()->addHours(9),
                'report_generated_at' => $date->copy()->addHours(10),
                'created_at' => $date->copy()->addHours(8),
                'updated_at' => $date->copy()->addHours(10),
            ]);
        }
        $this->command->info('  vehicle_inspections: 45');

        // NOTE: job_orders & estimates tables are schema stubs in this env
        // (missing most columns) — skipped to avoid corrupt data. See memory notes.

        // ------------------------------------------------------------------
        // 5. Service Records
        // ------------------------------------------------------------------
        $serviceTypes = ['Oil Change','Tune-up','Brake Service','Aircon Cleaning','Tire Rotation','Wheel Alignment','Battery Replacement','Coolant Flush','Transmission Service','Engine Diagnostic','Underchassis Service','General Checkup'];
        for ($i = 0; $i < 70; $i++) {
            $vehicle = $vehicles->random();
            $cid = $vehicle->customer_id;
            $date = Carbon::today()->subDays(rand(0, 120));
            $labor = rand(500, 6000);
            $parts = rand(500, 25000);
            $subtotal = $labor + $parts;
            $tax = round($subtotal * 0.12, 2);
            $final = round($subtotal + $tax, 2);
            ServiceRecord::create([
                'customer_id' => $cid,
                'vehicle_id' => $vehicle->id,
                'service_date' => $date,
                'odometer_at_service' => rand(20000,180000),
                'service_type' => $serviceTypes[array_rand($serviceTypes)],
                'description' => $requests[array_rand($requests)],
                'labor_cost' => $labor,
                'parts_cost' => $parts,
                'total_cost' => $final,
                'tax_amount' => $tax,
                'discount_amount' => 0,
                'final_amount' => $final,
                'payment_status' => $this->w(['paid','paid','partial','pending'],[50,25,15,10]),
                'service_status' => 'completed',
                'technician_id' => $technicians[array_rand($technicians)]->id,
                'service_advisor_id' => $advisor->id,
                'work_order_number' => 'SR-' . strtoupper(Str::random(8)),
                'diagnosis' => $requests[array_rand($requests)],
                'recommendations' => 'Recommended to monitor ' . $requests[array_rand($requests)],
                'customer_rating' => rand(3,5),
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
        $this->command->info('  service_records: 70');

        // NOTE: work_orders table has no model (App\Models\WorkOrder missing) and
        // invoices/payments tables don't exist in this env — skipped. ServiceRecords
        // are the revenue source for the dashboard.

        $this->command->info('Seed complete! Dashboard-ready realistic data added.');
    }

    private function w(array $items, array $weights)
    {
        $total = array_sum($weights);
        $r = mt_rand(1, $total);
        foreach ($items as $i => $it) { $r -= $weights[$i]; if ($r <= 0) return $it; }
        return $items[0];
    }

    private function serviceTypeKey(): string
    {
        $k = ['preventive_maintenance','basic_tune_up','egr_service','aircon_cleaning','aircon_general_cleaning','aircon_service','underchassis_service','engine_service'];
        return $k[array_rand($k)];
    }

    private function plate(): string
    {
        $letters = strtoupper(Str::random(3));
        return $letters . rand(100, 999);
    }
}
