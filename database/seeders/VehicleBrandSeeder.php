<?php

namespace Database\Seeders;

use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;

class VehicleBrandSeeder extends Seeder
{
    /**
     * All brands and their models from the hardcoded JS data.
     */
    protected array $brands = [
        'Toyota' => ['Vios', 'Wigo', 'Fortuner', 'Hilux', 'Innova', 'Corolla', 'Camry', 'RAV4', 'Land Cruiser', 'Hiace', 'Rush', 'Avanza', 'Yaris', 'Prius', 'C-HR', 'Alphard', 'Vellfire', 'Granvia', 'Coaster', '86', 'Supra', 'GR Yaris', 'GR Corolla', 'Sienna', 'Tacoma', 'Tundra', '4Runner', 'Highlander', 'Sequoia'],
        'Honda' => ['City', 'Brio', 'Civic', 'Accord', 'CR-V', 'HR-V', 'BR-V', 'Jazz', 'Mobilio', 'Odyssey', 'Pilot', 'Ridgeline', 'HRV', 'CRV', 'Fit', 'Legend', 'NSX'],
        'Ford' => ['Ranger', 'Everest', 'Territory', 'F-150', 'Raptor', 'Wildtrak', 'Explorer', 'Expedition', 'Escape', 'Focus', 'Mustang', 'Fiesta', 'EcoSport', 'Edge', 'Bronco', 'Maverick', 'Transit'],
        'Chevrolet' => ['Trailblazer', 'Colorado', 'Captiva', 'Spark', 'Cruze', 'Malibu', 'Tahoe', 'Suburban', 'Silverado', 'Traverse', 'Orlando', 'Aveo', 'Optra', 'Sail', 'Beat'],
        'Nissan' => ['Navara', 'Terra', 'Urvan', 'Almera', 'X-Trail', 'Patrol', 'Juke', 'Kicks', 'Leaf', 'Sentra', 'Altima', 'Maxima', '370Z', 'GT-R', 'NV350', 'Livina', 'Grand Livina', 'Serena'],
        'Hyundai' => ['Accent', 'Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Creta', 'Staria', 'Stargazer', 'Ioniq', 'Kona', 'Palisade', 'Venue', 'i10', 'i20', 'i30', 'H-100', 'Grand Starex', 'Starex', 'H350'],
        'Kia' => ['Seltos', 'Sportage', 'Sorento', 'Carnival', 'Stonic', 'Rio', 'Forte', 'Optima', 'Soul', 'Telluride', 'Picanto', 'K2500', 'K2700', 'Pride', 'Carens', 'Niro', 'EV6', 'Soul EV'],
        'Mitsubishi' => ['Montero Sport', 'Strada', 'Xpander', 'Mirage', 'Mirage G4', 'Lancer', 'Outlander', 'Eclipse Cross', 'Pajero', 'L300', 'Adventure', 'Delica', 'Fuso', 'Canter', 'Xforce'],
        'Mazda' => ['CX-5', 'CX-9', 'CX-30', 'CX-8', 'Mazda3', 'Mazda6', 'BT-50', 'MX-5', 'CX-3', 'CX-60', 'CX-90', '2', '5', '8', 'RX-8', 'RX-7'],
        'Subaru' => ['Forester', 'Outback', 'XV', 'Impreza', 'Legacy', 'WRX', 'BRZ', 'Levorg', 'Ascent', 'Solterra', 'Crosstrek'],
        'Volkswagen' => ['Tiguan', 'Teramont', 'Santana', 'Lavida', 'Polo', 'Golf', 'Passat', 'T-Cross', 'T-Roc', 'Beetle', 'Jetta', 'Touran', 'Sharan', 'Caddy', 'Transporter', 'Amarok'],
        'BMW' => ['3 Series', '5 Series', '7 Series', 'X1', 'X3', 'X5', 'X7', 'i3', 'i8', '2 Series', '4 Series', '6 Series', '8 Series', 'X2', 'X4', 'X6', 'i4', 'iX', 'Z4', 'M3', 'M5'],
        'Mercedes-Benz' => ['C-Class', 'E-Class', 'S-Class', 'GLA', 'GLC', 'GLE', 'GLS', 'A-Class', 'B-Class', 'CLA', 'CLS', 'G-Class', 'GLB', 'GLC Coupe', 'GLE Coupe', 'AMG GT', 'EQC', 'EQE', 'EQS'],
        'Audi' => ['A3', 'A4', 'A6', 'A8', 'Q2', 'Q3', 'Q5', 'Q7', 'Q8', 'TT', 'A1', 'A5', 'A7', 'Q4', 'e-tron', 'e-tron GT', 'RS3', 'RS5', 'RS6', 'RS7', 'R8'],
        'Lexus' => ['ES', 'LS', 'RX', 'NX', 'UX', 'LX', 'GX', 'IS', 'RC', 'LC', 'LM', 'UX 300e', 'RZ'],
        'Isuzu' => ['D-Max', 'MU-X', 'Crosswind', 'Alterra', 'Hi-Lander', 'N-Series', 'F-Series', 'Giga', 'Forward', 'ELF', 'Journey', 'Sportivo', 'X-Rider'],
        'Suzuki' => ['Ertiga', 'Swift', 'Ciaz', 'Vitara', 'Jimny', 'Carry', 'APV', 'Celerio', 'S-Presso', 'XL7', 'Baleno', 'Ignis', 'S-Cross', 'Every', 'Super Carry', 'Katana', 'Burgman'],
        'Volvo' => ['XC40', 'XC60', 'XC90', 'S60', 'S90', 'V60', 'V90', 'C40', 'EX30', 'EX90', 'S40', 'V40', 'C30', 'XC70', 'V70', 'S80'],
        'Foton' => ['Thunder', 'Gratour', 'Tunland', 'View', 'Blizzard', 'Toplander', 'Midi', 'Sauvana'],
        'JAC' => ['S3', 'S5', 'T6', 'T8', 'N56', 'N90', 'X200', 'X500'],
        'Geely' => ['Coolray', 'Azkarra', 'Okavango', 'Emgrand', 'Geometry C', 'Geometry A', 'Borui', 'Boyue', 'Xingyue'],
        'MG' => ['ZS', 'HS', 'RX5', 'RX8', '5', '6', 'ZS EV', 'HS Plug-in', 'Marvel R'],
        'Peugeot' => ['2008', '3008', '5008', '208', '308', '508', 'Partner', 'Expert', 'Traveller'],
        'Renault' => ['Koleos', 'Captur', 'Megane', 'Clio', 'Talisman', 'Kadjar', 'Arkana', 'Zoe', 'Twizy'],
        'SsangYong' => ['Tivoli', 'Korando', 'Rexton', 'Musso', 'Actyon', 'Rodius', 'Stavic', 'Kyron'],
        'Chery' => ['Tiggo', 'Arrizo', 'QQ', 'Fulwin', 'OMODA', 'Jaecoo'],
        'Changan' => ['CS35', 'CS55', 'CS75', 'CS85', 'CS95', 'Eado', 'Alsvin', 'Hunter', 'UNI-K', 'UNI-V'],
        'BYD' => ['Dolphin', 'Atto 3', 'Han', 'Tang', 'Song', 'Qin', 'Yuan', 'Seal', 'Seagull'],
        // Additional brands in the JS that have no models listed
        'Jeep' => ['Wrangler', 'Grand Cherokee', 'Cherokee', 'Compass', 'Renegade', 'Gladiator'],
        'Dodge' => ['Charger', 'Challenger', 'Durango', 'Grand Caravan'],
        'Chrysler' => ['300', 'Pacifica', 'Voyager'],
        'Ram' => ['1500', '2500', 'ProMaster'],
        'GMC' => ['Sierra', 'Yukon', 'Acadia', 'Terrain'],
        'Buick' => ['Encore', 'Envision', 'Enclave', 'Regal'],
        'Cadillac' => ['Escalade', 'XT4', 'XT5', 'XT6', 'CT4', 'CT5'],
        'Acura' => ['MDX', 'RDX', 'TLX', 'Integra', 'NSX'],
        'Infiniti' => ['Q50', 'Q60', 'QX50', 'QX60', 'QX80'],
        'Lincoln' => ['Navigator', 'Aviator', 'Corsair', 'Nautilus'],
        'Mini' => ['Cooper', 'Countryman', 'Clubman'],
        'Porsche' => ['911', 'Cayenne', 'Macan', 'Panamera', 'Taycan'],
        'Land Rover' => ['Range Rover', 'Discovery', 'Defender', 'Evoque', 'Velar'],
        'Jaguar' => ['F-PACE', 'E-PACE', 'I-PACE', 'XE', 'XF'],
        'Ferrari' => ['488', '812', 'SF90', 'Roma', 'Portofino'],
        'Lamborghini' => ['Urus', 'Huracan', 'Aventador'],
        'Maserati' => ['Levante', 'Ghibli', 'Quattroporte', 'MC20'],
        'Bentley' => ['Continental', 'Bentayga', 'Flying Spur'],
        'Rolls-Royce' => ['Phantom', 'Ghost', 'Cullinan', 'Wraith'],
        'Tesla' => ['Model 3', 'Model Y', 'Model S', 'Model X', 'Cybertruck'],
        'Fiat' => ['500', 'Panda', 'Tipo', 'Doblo'],
        'Alfa Romeo' => ['Giulia', 'Stelvio', 'Tonale'],
        'Haima' => ['Family', 'M3', '7X'],
        'DFSK' => ['Glory', 'C-Series', 'Mini Truck'],
        'Mahindra' => ['Scorpio', 'XUV500', 'Thar', 'Bolero'],
        'Tata' => ['Nexon', 'Harrier', 'Safari', 'Tiago'],
        'Proton' => ['X70', 'X50', 'Persona', 'Saga'],
        'Great Wall' => ['Wingle', 'Steed', 'Cannon', 'Poer'],
        'Haval' => ['H6', 'Jolion', 'H2', 'H9', 'Dargo'],
        'JMC' => ['Vigus', 'Vigus Plus', 'Grand Avenue'],
        'King Long' => ['Coaster', 'Bus', 'Mini Bus'],
        'Golden Dragon' => ['Bus', 'Mini Bus'],
        'Yutong' => ['Bus', 'Coach', 'Mini Bus'],
        'Higer' => ['Bus', 'Coach'],
        'Fuso' => ['Canter', 'Fighter', 'Super Great'],
        'Hino' => ['300 Series', '500 Series', '700 Series'],
        'UD Trucks' => ['Condor', 'Quester', 'Croner'],
        'Scania' => ['R-Series', 'G-Series', 'P-Series', 'Tourist Coach'],
        'MAN' => ['TGS', 'TGX', 'Lions Coach'],
        'Iveco' => ['Daily', 'Stralis', 'Trakker'],
        'Kenworth' => ['T680', 'T880', 'W900'],
        'Peterbilt' => ['579', '567', '389'],
        'Freightliner' => ['Cascadia', 'M2', 'Sprinter'],
        'Mack' => ['Anthem', 'Pinnacle', 'Granite'],
        'Volvo Trucks' => ['FH', 'FM', 'VNL', 'VNX'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $count = 0;
        $modelCount = 0;

        foreach ($this->brands as $brandName => $models) {
            $brand = VehicleBrand::firstOrCreate(
                ['name' => $brandName],
                [
                    'is_active' => true,
                    'popularity_score' => 0,
                ]
            );
            $count++;

            foreach ($models as $modelName) {
                VehicleModel::firstOrCreate(
                    [
                        'vehicle_brand_id' => $brand->id,
                        'name' => $modelName,
                    ],
                    [
                        'is_active' => true,
                        'popularity_score' => 0,
                        'vehicle_type' => $this->guessVehicleType($modelName, $brandName),
                    ]
                );
                $modelCount++;
            }
        }

        $this->command->info("Seeded {$count} brands and {$modelCount} models.");
    }

    /**
     * Guess the vehicle type based on model/common characteristics.
     */
    private function guessVehicleType(string $model, string $brand): string
    {
        $suvModels = ['Fortuner', 'RAV4', 'C-HR', 'CR-V', 'HR-V', 'BR-V', 'Everest', 'Territory', 'EcoSport', 'Tucson', 'Santa Fe', 'Seltos', 'Sportage', 'Sorento', 'Montero Sport', 'Outlander', 'Pajero', 'CX-5', 'CX-9', 'CX-30', 'CX-8', 'CX-3', 'Forester', 'Outback', 'XV', 'Tiguan', 'Teramont', 'X1', 'X3', 'X5', 'X7', 'GLA', 'GLC', 'GLE', 'Q2', 'Q3', 'Q5', 'Q7', 'RX', 'NX', 'UX', 'LX', 'GX', 'MU-X', 'Vitara', 'Jimny', 'XL7', 'XC40', 'XC60', 'XC90', 'S3', 'S5', 'Coolray', 'Azkarra', 'Okavango', 'ZS', 'HS', 'RX5', '2008', '3008', '5008', 'Koleos', 'Captur', 'Tivoli', 'Korando', 'Rexton', 'Tiggo', 'CS35', 'CS55', 'CS75', 'CS95', 'Atto 3', 'Tang', 'XV', 'Creta', 'Kicks', 'Juke', 'Venue', 'Kona', 'Stonic', 'Mazda CX-5', 'C-HR', 'Eclipse Cross', 'Territory', 'Ascent', 'Solterra', 'Crosstrek', 'Telluride', 'Palisade', 'Highlander', 'Pilot', 'Pathfinder', 'Aviator', 'Corsair', 'Nautilus', 'Escape', 'Edge', 'Bronco', 'Blazer', 'Traverse', 'Enclave', 'Envision', 'Encore', 'XT4', 'XT5', 'XT6', 'QX50', 'QX60', 'QX80', 'Levante', 'Urus', 'Cayenne', 'Macan', 'Bentayga', 'Cullinan', 'Wrangler', 'Grand Cherokee', 'Cherokee', 'Compass', 'Renegade', 'E-PACE', 'F-PACE', 'I-PACE', 'Velar', 'Evoque', 'Defender', 'Discovery'];

        $truckModels = ['Hilux', 'Ranger', 'Navara', 'Strada', 'D-Max', 'BT-50', 'F-150', 'Silverado', 'Sierra', 'Colorado', 'Canyon', 'Tacoma', 'Tundra', 'Frontier', 'Amarok', 'T6', 'T8', 'Thunder', 'Tunland'];

        $vanModels = ['Hiace', 'Urvan', 'NV350', 'Grand Starex', 'Starex', 'H-100', 'H350', 'L300', 'APV', 'Carry', 'Alphard', 'Vellfire', 'Granvia', 'Coaster', 'Carnival'];

        $suvCheck = strtoupper($model);
        if (in_array($model, $suvModels)) return 'suv';
        if (in_array($model, $truckModels)) return 'truck';
        if (in_array($model, $vanModels)) return 'van';
        return 'car';
    }
}
