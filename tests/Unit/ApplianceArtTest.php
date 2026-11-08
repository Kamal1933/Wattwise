<?php

namespace Tests\Unit;

use App\View\Components\ApplianceArt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ApplianceArtTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: ?string, 2: string}>
     */
    public static function appliances(): array
    {
        return [
            'ac by name' => ['AC Inverter Kamar Utama (1 PK)', 'cooling', 'ac'],
            'fridge' => ['Kulkas 2 Pintu Inverter', 'refrigeration', 'fridge'],
            'fan in cooling category' => ['Kipas Angin Berdiri', 'cooling', 'fan'],
            'rice cooker' => ['Rice Cooker Digital', 'kitchen', 'rice_cooker'],
            'tv' => ['Smart TV LED 50 Inch', 'entertainment', 'tv'],
            'router' => ['Wi-Fi Router & Modem', 'entertainment', 'router'],
            'washer' => ['Mesin Cuci Front Loading', 'laundry', 'washer'],
            'iron in laundry category' => ['Setrika Listrik Otomatis', 'laundry', 'iron'],
            'pump' => ['Pompa Air Otomatis (Jet Pump)', 'water_pump', 'pump'],
            'lamp' => ['Lampu LED Ruang Tamu', 'lighting', 'lamp'],
            'unknown name falls back to category' => ['Dispenser', 'kitchen', 'rice_cooker'],
            'unknown everything' => ['Alat misterius', null, 'plug'],
        ];
    }

    #[DataProvider('appliances')]
    public function test_illustration_type_is_resolved_from_name_then_category(string $name, ?string $category, string $expected): void
    {
        $this->assertSame($expected, ApplianceArt::typeFor($name, $category));
    }
}
