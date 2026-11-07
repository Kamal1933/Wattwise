<?php

namespace Database\Seeders;

use App\Models\Appliance;
use App\Models\ApplianceCategory;
use App\Models\Household;
use App\Models\TariffRate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HouseholdSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@wattwise.local'],
            [
                'name' => 'Administrator WattWise',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'budi@wattwise.local'],
            [
                'name' => 'Keluarga Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );

        $tariff1300 = TariffRate::where('code', 'R-1/TR-1300')->first()
            ?? TariffRate::first();

        $household = Household::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Rumah Keluarga Budi'],
            [
                'tariff_id' => $tariff1300->id,
                'address' => 'Jl. Merpati Putih No. 12, Jakarta',
                'occupant_count' => 4,
            ]
        );

        $cooling = ApplianceCategory::where('code', 'cooling')->first();
        $refrigeration = ApplianceCategory::where('code', 'refrigeration')->first();
        $lighting = ApplianceCategory::where('code', 'lighting')->first();
        $entertainment = ApplianceCategory::where('code', 'entertainment')->first();
        $kitchen = ApplianceCategory::where('code', 'kitchen')->first();
        $laundry = ApplianceCategory::where('code', 'laundry')->first();
        $waterPump = ApplianceCategory::where('code', 'water_pump')->first();

        $appliances = [
            [
                'name' => 'AC Inverter Kamar Utama (1 PK)',
                'category_id' => $cooling?->id,
                'power_watt' => 650.00,
                'quantity' => 1,
                'daily_hours' => 9.00,
                'days_per_month' => 30,
                'usage_factor' => 0.700,
                'is_active' => true,
            ],
            [
                'name' => 'AC Standar Kamar Anak (1/2 PK)',
                'category_id' => $cooling?->id,
                'power_watt' => 390.00,
                'quantity' => 1,
                'daily_hours' => 7.00,
                'days_per_month' => 30,
                'usage_factor' => 0.850,
                'is_active' => true,
            ],
            [
                'name' => 'Kulkas 2 Pintu Inverter',
                'category_id' => $refrigeration?->id,
                'power_watt' => 100.00,
                'quantity' => 1,
                'daily_hours' => 24.00,
                'days_per_month' => 30,
                'usage_factor' => 0.500,
                'is_active' => true,
            ],
            [
                'name' => 'Lampu Pijar Teras Depan',
                'category_id' => $lighting?->id,
                'power_watt' => 40.00,
                'quantity' => 2,
                'daily_hours' => 10.00,
                'days_per_month' => 30,
                'usage_factor' => 1.000,
                'is_active' => true,
            ],
            [
                'name' => 'Lampu LED Ruang Tamu & Kamar',
                'category_id' => $lighting?->id,
                'power_watt' => 9.00,
                'quantity' => 8,
                'daily_hours' => 6.00,
                'days_per_month' => 30,
                'usage_factor' => 1.000,
                'is_active' => true,
            ],
            [
                'name' => 'Smart TV LED 50 Inch',
                'category_id' => $entertainment?->id,
                'power_watt' => 95.00,
                'quantity' => 1,
                'daily_hours' => 5.00,
                'days_per_month' => 30,
                'usage_factor' => 1.000,
                'is_active' => true,
            ],
            [
                'name' => 'Wi-Fi Router & Modem',
                'category_id' => $entertainment?->id,
                'power_watt' => 15.00,
                'quantity' => 1,
                'daily_hours' => 24.00,
                'days_per_month' => 30,
                'usage_factor' => 1.000,
                'is_active' => true,
            ],
            [
                'name' => 'Rice Cooker Digital (Memasak + Menghangatkan)',
                'category_id' => $kitchen?->id,
                'power_watt' => 380.00,
                'quantity' => 1,
                'daily_hours' => 4.00,
                'days_per_month' => 30,
                'usage_factor' => 0.600,
                'is_active' => true,
            ],
            [
                'name' => 'Mesin Cuci Front Loading',
                'category_id' => $laundry?->id,
                'power_watt' => 350.00,
                'quantity' => 1,
                'daily_hours' => 1.50,
                'days_per_month' => 16,
                'usage_factor' => 0.900,
                'is_active' => true,
            ],
            [
                'name' => 'Setrika Listrik Otomatis',
                'category_id' => $laundry?->id,
                'power_watt' => 300.00,
                'quantity' => 1,
                'daily_hours' => 1.00,
                'days_per_month' => 20,
                'usage_factor' => 0.700,
                'is_active' => true,
            ],
            [
                'name' => 'Pompa Air Otomatis (Jet Pump)',
                'category_id' => $waterPump?->id,
                'power_watt' => 250.00,
                'quantity' => 1,
                'daily_hours' => 2.00,
                'days_per_month' => 30,
                'usage_factor' => 1.000,
                'is_active' => true,
            ],
        ];

        foreach ($appliances as $applianceData) {
            if (! $applianceData['category_id']) {
                continue;
            }
            Appliance::updateOrCreate(
                [
                    'household_id' => $household->id,
                    'name' => $applianceData['name'],
                ],
                $applianceData
            );
        }
    }
}
