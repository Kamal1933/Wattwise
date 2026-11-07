<?php

namespace Tests\Unit;

use App\Services\Calculation\EnergyCalculationEngine;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EnergyCalculationEngineTest extends TestCase
{
    protected EnergyCalculationEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new EnergyCalculationEngine;
    }

    public function test_calculates_monthly_kwh_accurately(): void
    {
        // 650W * 9h * 0.700 usage_factor / 1000 * 30 days * 1 unit = 122.85 kWh
        $kwh = $this->engine->calculateMonthlyKwh(
            powerWatt: 650.0,
            dailyHours: 9.0,
            daysPerMonth: 30,
            usageFactor: 0.700,
            quantity: 1
        );

        $this->assertEquals(122.85, $kwh);
    }

    public function test_calculates_refrigerator_with_duty_cycle(): void
    {
        // 100W * 24h * 0.500 / 1000 * 30 days = 36.0 kWh
        $kwh = $this->engine->calculateMonthlyKwh(
            powerWatt: 100.0,
            dailyHours: 24.0,
            daysPerMonth: 30,
            usageFactor: 0.500,
            quantity: 1
        );

        $this->assertEquals(36.0, $kwh);
    }

    public function test_calculates_multiple_quantities_correctly(): void
    {
        // 9W * 6h * 1.0 / 1000 * 30 days * 8 bulbs = 12.96 kWh
        $kwh = $this->engine->calculateMonthlyKwh(
            powerWatt: 9.0,
            dailyHours: 6.0,
            daysPerMonth: 30,
            usageFactor: 1.0,
            quantity: 8
        );

        $this->assertEquals(12.96, $kwh);
    }

    public function test_calculates_cost_with_tariff_rate_and_base_fee(): void
    {
        // 100 kWh * Rp 1.444,70 + Rp 10.000 base fee = Rp 154.470
        $cost = $this->engine->calculateCost(100.0, 1444.70, 10000.0);
        $this->assertEquals(154470.0, $cost);
    }

    public function test_rejects_negative_or_zero_power(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->engine->calculateMonthlyKwh(0.0, 5.0, 30, 1.0, 1);
    }

    public function test_rejects_daily_hours_exceeding_24(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->engine->calculateMonthlyKwh(100.0, 24.5, 30, 1.0, 1);
    }

    public function test_rejects_invalid_usage_factor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->engine->calculateMonthlyKwh(100.0, 5.0, 30, 1.5, 1);
    }
}
