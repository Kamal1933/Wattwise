<?php

namespace Database\Seeders;

use App\Models\ApplianceCategory;
use Illuminate\Database\Seeder;

class ApplianceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => 'cooling',
                'name' => 'Pendingin Ruangan (AC & Kipas)',
                'icon' => 'snowflake',
                'description' => 'Air conditioner, evaporative cooler, kipas angin dinding/berdiri',
            ],
            [
                'code' => 'refrigeration',
                'name' => 'Pendingin Makanan (Kulkas & Freezer)',
                'icon' => 'box',
                'description' => 'Kulkas 1 pintu, 2 pintu, side-by-side, freezer box',
            ],
            [
                'code' => 'lighting',
                'name' => 'Penerangan & Lampu',
                'icon' => 'lightbulb',
                'description' => 'Lampu LED, lampu TL/neon, lampu pijar, penerangan luar',
            ],
            [
                'code' => 'entertainment',
                'name' => 'Hiburan & Perangkat IT',
                'icon' => 'tv',
                'description' => 'Smart TV, sound system, konsol game, PC desktop, router Wi-Fi',
            ],
            [
                'code' => 'kitchen',
                'name' => 'Peralatan Dapur & Memasak',
                'icon' => 'utensils',
                'description' => 'Rice cooker, microwave, oven listrik, air fryer, dispenser air',
            ],
            [
                'code' => 'laundry',
                'name' => 'Pencucian & Kebersihan',
                'icon' => 'shirt',
                'description' => 'Mesin cuci front load/top load, setrika listrik, vacuum cleaner',
            ],
            [
                'code' => 'water_pump',
                'name' => 'Pompa & Pemanas Air',
                'icon' => 'droplet',
                'description' => 'Pompa air sumur/jet pump, booster pump, water heater listrik',
            ],
            [
                'code' => 'other',
                'name' => 'Peralatan Lainnya',
                'icon' => 'zap',
                'description' => 'Pompa kolam, charger kendaraan listrik, stopkontak serbaguna',
            ],
        ];

        foreach ($categories as $category) {
            ApplianceCategory::updateOrCreate(['code' => $category['code']], $category);
        }
    }
}
