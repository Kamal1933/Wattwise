<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TariffRate;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Typical household appliances offered in the landing page calculator.
     * Watt and usage factor follow common label values; visitors adjust hours and quantity.
     *
     * @var list<array{key: string, name: string, category: string, watt: int, hours: float, factor: float, quantity: int, selected: bool}>
     */
    public const APPLIANCE_PRESETS = [
        ['key' => 'ac', 'name' => 'AC 1 PK', 'category' => 'cooling', 'watt' => 750, 'hours' => 8, 'factor' => 0.8, 'quantity' => 1, 'selected' => true],
        ['key' => 'fridge', 'name' => 'Kulkas', 'category' => 'refrigeration', 'watt' => 120, 'hours' => 24, 'factor' => 0.5, 'quantity' => 1, 'selected' => true],
        ['key' => 'rice_cooker', 'name' => 'Rice cooker', 'category' => 'kitchen', 'watt' => 350, 'hours' => 4, 'factor' => 0.5, 'quantity' => 1, 'selected' => true],
        ['key' => 'tv', 'name' => 'Televisi', 'category' => 'entertainment', 'watt' => 90, 'hours' => 5, 'factor' => 1.0, 'quantity' => 1, 'selected' => true],
        ['key' => 'led', 'name' => 'Lampu LED', 'category' => 'lighting', 'watt' => 9, 'hours' => 10, 'factor' => 1.0, 'quantity' => 6, 'selected' => true],
        ['key' => 'old_lamp', 'name' => 'Lampu pijar / neon lama', 'category' => 'lighting', 'watt' => 40, 'hours' => 10, 'factor' => 1.0, 'quantity' => 2, 'selected' => false],
        ['key' => 'pump', 'name' => 'Pompa air', 'category' => 'water_pump', 'watt' => 250, 'hours' => 2, 'factor' => 1.0, 'quantity' => 1, 'selected' => true],
        ['key' => 'iron', 'name' => 'Setrika', 'category' => 'laundry', 'watt' => 350, 'hours' => 1, 'factor' => 1.0, 'quantity' => 1, 'selected' => false],
        ['key' => 'washer', 'name' => 'Mesin cuci', 'category' => 'laundry', 'watt' => 350, 'hours' => 1, 'factor' => 1.0, 'quantity' => 1, 'selected' => false],
        ['key' => 'router', 'name' => 'Wi-Fi router', 'category' => 'entertainment', 'watt' => 12, 'hours' => 24, 'factor' => 1.0, 'quantity' => 1, 'selected' => true],
        ['key' => 'fan', 'name' => 'Kipas angin', 'category' => 'cooling', 'watt' => 45, 'hours' => 8, 'factor' => 1.0, 'quantity' => 1, 'selected' => false],
        ['key' => 'water_heater', 'name' => 'Pemanas air', 'category' => 'water_pump', 'watt' => 1200, 'hours' => 1, 'factor' => 1.0, 'quantity' => 1, 'selected' => false],
    ];

    public function index(): View
    {
        $tariffs = TariffRate::where('is_active', true)
            ->orderBy('power_limit_va')
            ->get(['id', 'code', 'name', 'power_limit_va', 'rate_per_kwh', 'base_fee'])
            ->map(fn (TariffRate $tariff): array => [
                'id' => $tariff->id,
                'code' => $tariff->code,
                'name' => $tariff->name,
                'power_limit_va' => (float) $tariff->power_limit_va,
                'rate_per_kwh' => (float) $tariff->rate_per_kwh,
                'base_fee' => (float) $tariff->base_fee,
            ])
            ->values();

        $defaultTariff = $tariffs->firstWhere('power_limit_va', 1300) ?? $tariffs->first();

        return view('landing', [
            'tariffs' => $tariffs,
            'defaultTariffId' => $defaultTariff['id'] ?? null,
            'appliancePresets' => self::APPLIANCE_PRESETS,
        ]);
    }
}
