<?php

namespace Database\Seeders;

use App\Models\TariffRate;
use Illuminate\Database\Seeder;

class TariffRateSeeder extends Seeder
{
    public function run(): void
    {
        $tariffs = [
            [
                'code' => 'R-1/TR-900',
                'name' => 'Rumah Tangga R-1/TR 900 VA (Subsidi/Khusus)',
                'power_limit_va' => 900.00,
                'rate_per_kwh' => 1352.0000,
                'base_fee' => 0.00,
                'valid_from' => '2024-01-01',
                'valid_to' => null,
                'is_active' => true,
            ],
            [
                'code' => 'R-1/TR-1300',
                'name' => 'Rumah Tangga R-1/TR 1.300 VA',
                'power_limit_va' => 1300.00,
                'rate_per_kwh' => 1444.7000,
                'base_fee' => 0.00,
                'valid_from' => '2024-01-01',
                'valid_to' => null,
                'is_active' => true,
            ],
            [
                'code' => 'R-1/TR-2200',
                'name' => 'Rumah Tangga R-1/TR 2.200 VA',
                'power_limit_va' => 2200.00,
                'rate_per_kwh' => 1444.7000,
                'base_fee' => 0.00,
                'valid_from' => '2024-01-01',
                'valid_to' => null,
                'is_active' => true,
            ],
            [
                'code' => 'R-2/TR-3500-5500',
                'name' => 'Rumah Tangga Menengah R-2/TR 3.500 VA - 5.500 VA',
                'power_limit_va' => 3500.00,
                'rate_per_kwh' => 1699.5300,
                'base_fee' => 0.00,
                'valid_from' => '2024-01-01',
                'valid_to' => null,
                'is_active' => true,
            ],
            [
                'code' => 'R-3/TR-6600+',
                'name' => 'Rumah Tangga Besar R-3/TR 6.600 VA ke atas',
                'power_limit_va' => 6600.00,
                'rate_per_kwh' => 1699.5300,
                'base_fee' => 0.00,
                'valid_from' => '2024-01-01',
                'valid_to' => null,
                'is_active' => true,
            ],
        ];

        foreach ($tariffs as $tariff) {
            TariffRate::updateOrCreate(['code' => $tariff['code']], $tariff);
        }
    }
}
