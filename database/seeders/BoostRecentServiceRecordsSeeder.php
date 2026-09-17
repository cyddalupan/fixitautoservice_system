<?php

namespace Database\Seeders;

use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BoostRecentServiceRecordsSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = Vehicle::inRandomOrder()->take(60)->get();
        $techs = User::where('role', 'technician')->inRandomOrder()->get();
        $advisor = User::where('role', 'service_advisor')->first();
        if ($vehicles->isEmpty() || $techs->isEmpty() || !$advisor) {
            $this->command->error('Need vehicles + technicians + advisor.');
            return;
        }

        $types = ['Oil Change','Tune-up','Brake Service','Aircon Cleaning','Tire Rotation','Wheel Alignment','Battery Replacement','Coolant Flush','Transmission Service','Engine Diagnostic'];
        $reqs = ['Oil change and check-up','Engine making ticking noise','Brakes feel spongy','Aircon not cooling','Check engine light on','Tire replacement','Regular maintenance','Suspension clunking','Battery kept dying','Exhaust smell in cabin'];

        for ($i = 0; $i < 24; $i++) {
            $v = $vehicles->random();
            $t = $techs->random();
            $date = Carbon::today()->subDays(rand(0, 12));
            $labor = rand(500, 6000);
            $parts = rand(800, 20000);
            $sub = $labor + $parts;
            $tax = round($sub * 0.12, 2);
            $final = round($sub + $tax, 2);
            ServiceRecord::create([
                'customer_id' => $v->customer_id,
                'vehicle_id' => $v->id,
                'service_date' => $date,
                'odometer_at_service' => rand(20000, 180000),
                'service_type' => $types[array_rand($types)],
                'description' => $reqs[array_rand($reqs)],
                'labor_cost' => $labor,
                'parts_cost' => $parts,
                'total_cost' => $final,
                'tax_amount' => $tax,
                'discount_amount' => 0,
                'final_amount' => $final,
                'payment_status' => ['paid', 'paid', 'partial'][array_rand([0, 0, 1])],
                'service_status' => 'completed',
                'technician_id' => $t->id,
                'service_advisor_id' => $advisor->id,
                'work_order_number' => 'SR-' . strtoupper(Str::random(8)),
                'diagnosis' => $reqs[array_rand($reqs)],
                'recommendations' => 'Monitor and return for follow-up',
                'customer_rating' => rand(3, 5),
            ]);
        }
        $this->command->info('Added 24 recent service records (current month activity).');
    }
}
