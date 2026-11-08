<?php

namespace App\Services\Calculation;

use App\Models\ActualMeterReading;
use App\Models\CalculationItem;
use App\Models\CalculationPeriod;
use App\Models\Household;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EnergyCalculationEngine
{
    /**
     * Calculate monthly energy consumption in kWh for a single appliance configuration.
     * Formula: (Watt * daily_hours * usage_factor / 1000) * days_per_month * quantity
     */
    public function calculateMonthlyKwh(
        float $powerWatt,
        float $dailyHours,
        int $daysPerMonth = 30,
        float $usageFactor = 1.0,
        int $quantity = 1
    ): float {
        $this->validateApplianceParameters($powerWatt, $dailyHours, $daysPerMonth, $usageFactor, $quantity);

        $dailyKwhPerUnit = ($powerWatt * $dailyHours * $usageFactor) / 1000.0;
        $totalMonthlyKwh = $dailyKwhPerUnit * $daysPerMonth * $quantity;

        return round($totalMonthlyKwh, 3);
    }

    /**
     * Calculate estimated cost given energy in kWh and tariff rate per kWh.
     */
    public function calculateCost(float $kwh, float $ratePerKwh, float $baseFee = 0.0): float
    {
        if ($kwh < 0 || $ratePerKwh < 0 || $baseFee < 0) {
            throw new InvalidArgumentException('Energy, rate, and base fee must be non-negative.');
        }

        return round(($kwh * $ratePerKwh) + $baseFee, 2);
    }

    /**
     * Compute full energy calculation for a household and persist calculation period with breakdown items.
     *
     * Recalculating the same household and date range updates the existing period in place
     * (replacing its items and stale recommendations) instead of creating a duplicate,
     * so linked meter readings keep pointing at the same period.
     *
     * @param  array<string, mixed>  $assumptions
     */
    public function calculateHouseholdPeriod(
        Household $household,
        Carbon|string $startDate,
        Carbon|string $endDate,
        ?string $periodName = null,
        array $assumptions = []
    ): CalculationPeriod {
        $startDate = is_string($startDate) ? Carbon::parse($startDate) : $startDate;
        $endDate = is_string($endDate) ? Carbon::parse($endDate) : $endDate;
        $periodName = $periodName ?? $startDate->translatedFormat('F Y');

        $household->loadMissing(['tariff', 'appliances.category']);
        $tariff = $household->tariff;

        if (! $tariff) {
            throw new InvalidArgumentException('Household does not have an assigned tariff.');
        }

        $activeAppliances = $household->appliances->where('is_active', true);

        // Pre-calculate items
        $rawItems = [];
        $totalEstimatedKwh = 0.0;
        $totalSimultaneousWatt = 0.0;

        foreach ($activeAppliances as $appliance) {
            $monthlyKwh = $this->calculateMonthlyKwh(
                (float) $appliance->power_watt,
                (float) $appliance->daily_hours,
                (int) $appliance->days_per_month,
                (float) $appliance->usage_factor,
                (int) $appliance->quantity
            );

            $monthlyCost = $this->calculateCost($monthlyKwh, (float) $tariff->rate_per_kwh);
            $totalEstimatedKwh += $monthlyKwh;
            $totalSimultaneousWatt += ((float) $appliance->power_watt * (int) $appliance->quantity);

            $rawItems[] = [
                'appliance' => $appliance,
                'monthly_kwh' => $monthlyKwh,
                'monthly_cost' => $monthlyCost,
            ];
        }

        $totalEstimatedCost = $this->calculateCost($totalEstimatedKwh, (float) $tariff->rate_per_kwh, (float) $tariff->base_fee);

        $mergedAssumptions = array_merge([
            'tariff_code' => $tariff->code,
            'rate_per_kwh' => (float) $tariff->rate_per_kwh,
            'power_limit_va' => (float) $tariff->power_limit_va,
            'simultaneous_active_watt' => round($totalSimultaneousWatt, 2),
            'simultaneous_load_ratio' => $tariff->power_limit_va > 0
                ? round($totalSimultaneousWatt / (float) $tariff->power_limit_va, 4)
                : 0.0,
            'calculation_engine_version' => '1.0.0',
            'calculated_at' => now()->toIso8601String(),
        ], $assumptions);

        $period = DB::transaction(function () use ($household, $startDate, $endDate, $periodName, $totalEstimatedKwh, $totalEstimatedCost, $tariff, $mergedAssumptions, $rawItems): CalculationPeriod {
            $period = CalculationPeriod::query()
                ->where('household_id', $household->id)
                ->whereDate('start_date', $startDate->toDateString())
                ->whereDate('end_date', $endDate->toDateString())
                ->lockForUpdate()
                ->first() ?? new CalculationPeriod([
                    'household_id' => $household->id,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ]);

            $period->fill([
                'period_name' => $periodName,
                'total_estimated_kwh' => round($totalEstimatedKwh, 3),
                'total_estimated_cost' => round($totalEstimatedCost, 2),
                'applied_rate_per_kwh' => (float) $tariff->rate_per_kwh,
                'calculation_assumptions' => $mergedAssumptions,
            ])->save();

            $period->items()->delete();
            $period->recommendations()->delete();

            foreach ($rawItems as $item) {
                $sharePercentage = $totalEstimatedKwh > 0
                    ? round(($item['monthly_kwh'] / $totalEstimatedKwh) * 100, 2)
                    : 0.0;

                CalculationItem::create([
                    'calculation_period_id' => $period->id,
                    'appliance_id' => $item['appliance']->id,
                    'power_watt' => (float) $item['appliance']->power_watt,
                    'quantity' => (int) $item['appliance']->quantity,
                    'daily_hours' => (float) $item['appliance']->daily_hours,
                    'days_per_month' => (int) $item['appliance']->days_per_month,
                    'usage_factor' => (float) $item['appliance']->usage_factor,
                    'monthly_kwh' => $item['monthly_kwh'],
                    'monthly_cost' => $item['monthly_cost'],
                    'percentage_of_total' => $sharePercentage,
                ]);
            }

            return $period;
        });

        return $period->unsetRelations()->load(['items.appliance.category', 'household.tariff']);
    }

    /**
     * Record actual meter reading and calculate variance against calculation period.
     */
    public function recordActualReading(
        Household $household,
        float $meterStartKwh,
        float $meterEndKwh,
        Carbon|string $readingDate,
        ?CalculationPeriod $period = null,
        ?float $actualCostBilled = null,
        ?string $receiptImageUrl = null,
        ?string $notes = null
    ): ActualMeterReading {
        if ($meterEndKwh < $meterStartKwh) {
            throw new InvalidArgumentException('Meter end reading cannot be less than start reading.');
        }

        $actualKwhConsumed = round($meterEndKwh - $meterStartKwh, 2);
        $varianceKwh = null;
        $variancePct = null;

        if ($period) {
            $estimatedKwh = (float) $period->total_estimated_kwh;
            $varianceKwh = round($actualKwhConsumed - $estimatedKwh, 2);
            $variancePct = $actualKwhConsumed > 0
                ? round((($actualKwhConsumed - $estimatedKwh) / $actualKwhConsumed) * 100, 2)
                : 0.0;
        }

        $readingDate = is_string($readingDate) ? Carbon::parse($readingDate) : $readingDate;

        return ActualMeterReading::create([
            'household_id' => $household->id,
            'calculation_period_id' => $period?->id,
            'reading_date' => $readingDate->toDateString(),
            'meter_start_kwh' => $meterStartKwh,
            'meter_end_kwh' => $meterEndKwh,
            'actual_kwh_consumed' => $actualKwhConsumed,
            'actual_cost_billed' => $actualCostBilled,
            'variance_kwh' => $varianceKwh,
            'variance_pct' => $variancePct,
            'receipt_image_url' => $receiptImageUrl,
            'notes' => $notes,
        ]);
    }

    /**
     * Validate positive boundary constraints for appliance inputs.
     */
    public function validateApplianceParameters(
        float $powerWatt,
        float $dailyHours,
        int $daysPerMonth,
        float $usageFactor,
        int $quantity
    ): void {
        if ($powerWatt <= 0) {
            throw new InvalidArgumentException('Power (Watt) must be greater than 0.');
        }

        if ($dailyHours <= 0 || $dailyHours > 24) {
            throw new InvalidArgumentException('Daily operating hours must be between 0.1 and 24.0 hours.');
        }

        if ($daysPerMonth < 1 || $daysPerMonth > 31) {
            throw new InvalidArgumentException('Days per month must be between 1 and 31 days.');
        }

        if ($usageFactor <= 0 || $usageFactor > 1.0) {
            throw new InvalidArgumentException('Usage factor must be between 0.01 and 1.0.');
        }

        if ($quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1.');
        }
    }
}
