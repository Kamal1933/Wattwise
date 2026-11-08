<?php

namespace App\Services\ExpertSystem;

use App\Models\ExpertRule;
use App\Models\RuleCondition;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ExplanationGenerator
{
    /**
     * Human-readable metadata for known working-memory facts.
     * Facts not listed here (e.g. facts derived by rules) fall back to a humanized name.
     *
     * @var array<string, array{label: string, unit?: string, decimals?: int, percent?: bool, currency?: bool}>
     */
    public const FACT_LABELS = [
        'appliance_name' => ['label' => 'nama peralatan'],
        'category_code' => ['label' => 'kategori'],
        'category_name' => ['label' => 'nama kategori'],
        'power_watt' => ['label' => 'daya', 'unit' => 'W', 'decimals' => 0],
        'quantity' => ['label' => 'jumlah unit', 'unit' => 'unit', 'decimals' => 0],
        'daily_hours' => ['label' => 'durasi pemakaian', 'unit' => 'jam/hari', 'decimals' => 1],
        'days_per_month' => ['label' => 'hari pemakaian', 'unit' => 'hari/bulan', 'decimals' => 0],
        'usage_factor' => ['label' => 'faktor pemakaian', 'decimals' => 2],
        'monthly_kwh' => ['label' => 'konsumsi bulanan', 'unit' => 'kWh', 'decimals' => 1],
        'monthly_cost' => ['label' => 'biaya bulanan', 'decimals' => 0, 'currency' => true],
        'appliance_share_pct' => ['label' => 'porsi dari total konsumsi rumah', 'unit' => '%', 'decimals' => 1],
        'rate_per_kwh' => ['label' => 'tarif per kWh', 'decimals' => 2, 'currency' => true],
        'applied_rate_per_kwh' => ['label' => 'tarif per kWh', 'decimals' => 2, 'currency' => true],
        'total_estimated_kwh' => ['label' => 'total konsumsi rumah', 'unit' => 'kWh', 'decimals' => 1],
        'total_estimated_cost' => ['label' => 'total biaya rumah', 'decimals' => 0, 'currency' => true],
        'tariff_code' => ['label' => 'golongan tarif'],
        'power_limit_va' => ['label' => 'daya kontrak PLN', 'unit' => 'VA', 'decimals' => 0],
        'simultaneous_active_watt' => ['label' => 'total daya peralatan aktif', 'unit' => 'W', 'decimals' => 0],
        'simultaneous_load_ratio' => ['label' => 'rasio beban terhadap daya kontrak', 'decimals' => 1, 'percent' => true],
        'occupant_count' => ['label' => 'jumlah penghuni', 'unit' => 'orang', 'decimals' => 0],
        'appliance_count' => ['label' => 'jumlah peralatan aktif', 'unit' => 'unit', 'decimals' => 0],
        'is_heavy_consumer' => ['label' => 'peralatan berkonsumsi tinggi'],
        'capacity_at_risk' => ['label' => 'rumah berisiko kelebihan beban'],
    ];

    /**
     * @var array<string, string>
     */
    public const OPERATOR_LABELS = [
        '>' => '>',
        '>=' => '≥',
        '<' => '<',
        '<=' => '≤',
        '==' => '=',
        '!=' => '≠',
        'IN' => 'salah satu dari',
        'CONTAINS' => 'mengandung',
    ];

    /**
     * Build the evidence text from the rule conditions that were actually satisfied,
     * so the explanation always matches the thresholds stored in the database.
     *
     * @param  array<string, mixed>  $facts
     * @param  Collection<int, RuleCondition>|null  $matchedConditions  The satisfied condition group; defaults to the rule's first group.
     * @param  array<string, string>  $derivedFrom  Fact name => code of the rule that derived it.
     */
    public function generateEvidence(
        ExpertRule $rule,
        array $facts,
        ?Collection $matchedConditions = null,
        array $derivedFrom = []
    ): string {
        $matchedConditions ??= $rule->conditions
            ->groupBy(fn (RuleCondition $condition): int => (int) ($condition->condition_group ?? 1))
            ->first() ?? collect();

        $subject = $rule->isHouseholdScoped()
            ? 'rumah tangga'
            : "'".($facts['appliance_name'] ?? 'Peralatan')."'";

        $clauses = $matchedConditions->map(function (RuleCondition $condition) use ($facts, $derivedFrom): string {
            $param = $condition->parameter_name;
            $clause = sprintf(
                '%s = %s (syarat: %s %s)',
                $this->labelFor($param),
                $this->formatFact($param, $facts[$param] ?? null),
                self::OPERATOR_LABELS[$condition->operator] ?? $condition->operator,
                $this->formatComparisonValue($param, $condition->operator, $condition->comparison_value)
            );

            if (isset($derivedFrom[$param])) {
                $clause .= " — fakta turunan dari aturan {$derivedFrom[$param]}";
            }

            return $clause;
        })->all();

        $evidence = "Bukti Inferensi: aturan {$rule->rule_code} terpicu pada {$subject} karena ".implode('; ', $clauses).'.';

        $groupCount = $rule->conditions->pluck('condition_group')->unique()->count();
        if ($groupCount > 1) {
            $groupNumber = $matchedConditions->first()?->condition_group ?? 1;
            $evidence .= " Terpenuhi lewat kelompok kondisi #{$groupNumber} dari {$groupCount} alternatif (OR).";
        }

        return $evidence;
    }

    /**
     * Interpolate ":fact_name" placeholders in a recommendation template with formatted fact values.
     * Units are left to the template text, so only the number is inserted.
     *
     * @param  array<string, mixed>  $facts
     */
    public function formatRecommendation(string $template, array $facts): string
    {
        $placeholders = array_keys($facts);
        usort($placeholders, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        $replacements = [];
        foreach ($placeholders as $param) {
            $replacements[':'.$param] = $this->formatFact($param, $facts[$param], withUnit: false);
        }

        return strtr($template, $replacements);
    }

    /**
     * Describe a rule condition in plain words, e.g. "durasi pemakaian ≥ 8,0 jam/hari".
     *
     * @param  array<string, string>  $valueLabels  Raw comparison value => display label (e.g. category codes to names).
     */
    public function describeCondition(RuleCondition $condition, array $valueLabels = []): string
    {
        $param = $condition->parameter_name;
        $value = $condition->operator === 'IN'
            ? implode(', ', array_map(
                fn (string $item): string => $valueLabels[trim($item)] ?? trim($item),
                explode(',', $condition->comparison_value)
            ))
            : ($valueLabels[$condition->comparison_value] ?? $this->formatComparisonValue($param, $condition->operator, $condition->comparison_value));

        return sprintf('%s %s %s', $this->labelFor($param), self::OPERATOR_LABELS[$condition->operator] ?? $condition->operator, $value);
    }

    /**
     * Human-readable label of a fact.
     */
    public function labelFor(string $param): string
    {
        return self::FACT_LABELS[$param]['label'] ?? Str::of($param)->replace('_', ' ')->toString();
    }

    /**
     * Format a fact value for display (Indonesian number format, units, booleans as ya/tidak).
     */
    public function formatFact(string $param, mixed $value, bool $withUnit = true): string
    {
        if ($value === null) {
            return '-';
        }

        if (is_bool($value) || in_array($value, ['true', 'false'], true)) {
            return in_array($value, [true, 'true'], true) ? 'ya' : 'tidak';
        }

        if (! is_numeric($value)) {
            return (string) $value;
        }

        $meta = self::FACT_LABELS[$param] ?? ['label' => $param];
        $number = (float) $value;

        if ($meta['percent'] ?? false) {
            return number_format($number * 100, $meta['decimals'] ?? 1, ',', '.').($withUnit ? '%' : '');
        }

        $formatted = number_format($number, $meta['decimals'] ?? $this->decimalsFor($number), ',', '.');

        if (! $withUnit) {
            return $formatted;
        }

        if ($meta['currency'] ?? false) {
            return 'Rp'.$formatted;
        }

        return isset($meta['unit']) ? "{$formatted} {$meta['unit']}" : $formatted;
    }

    /**
     * Format the threshold side of a condition.
     */
    protected function formatComparisonValue(string $param, string $operator, string $comparisonValue): string
    {
        if ($operator === 'IN') {
            return implode(', ', array_map('trim', explode(',', $comparisonValue)));
        }

        if ($operator === 'CONTAINS') {
            return "'{$comparisonValue}'";
        }

        return $this->formatFact($param, $comparisonValue);
    }

    protected function decimalsFor(float $number): int
    {
        return floor($number) === $number ? 0 : 2;
    }

    /**
     * Calculate conservative, verifiable potential savings based on the rule category.
     *
     * @param  array<string, mixed>  $facts
     * @return array{kwh: float, cost: float}
     */
    public function estimateSavings(ExpertRule $rule, array $facts, float $ratePerKwh): array
    {
        $monthlyKwh = (float) ($facts['monthly_kwh'] ?? 0.0);
        $powerWatt = (float) ($facts['power_watt'] ?? 0.0);
        $dailyHours = (float) ($facts['daily_hours'] ?? 0.0);
        $quantity = (int) ($facts['quantity'] ?? 1);

        $savedKwh = 0.0;

        switch ($rule->rule_code) {
            case 'R-DOM-01':
                // Reducing usage by 1.5 hours daily
                $savedKwh = ($dailyHours > 0) ? ($monthlyKwh * (1.5 / $dailyHours)) : ($monthlyKwh * 0.15);
                break;

            case 'R-COOL-01':
                // Setting thermostat 2-3°C higher saves roughly 15-20% of AC consumption (18% used)
                $savedKwh = $monthlyKwh * 0.18;
                break;

            case 'R-VAMP-01':
                // Eliminating 8 hours of unused standby power per day
                $savedKwh = (($powerWatt * 8 * 30 * $quantity) / 1000.0);
                break;

            case 'R-LIGHT-01':
                // Replacing high-wattage lamp (e.g. 40W) with 9W LED
                $wattDelta = max(0, $powerWatt - 9.0);
                $savedKwh = (($wattDelta * $dailyHours * 30 * $quantity) / 1000.0);
                break;

            case 'R-WATER-01':
                // Reducing water heater heating schedule by 30%
                $savedKwh = $monthlyKwh * 0.25;
                break;

            case 'R-CAP-01':
            case 'R-PEAK-01':
                // Load scheduling prevents tripping but does not directly reduce monthly kWh
                $savedKwh = 0.0;
                break;

            default:
                $savedKwh = $monthlyKwh * 0.10;
                break;
        }

        $savedKwh = round(min($monthlyKwh > 0 ? $monthlyKwh : $savedKwh, $savedKwh), 2);
        $savedCost = round($savedKwh * $ratePerKwh, 2);

        return [
            'kwh' => $savedKwh,
            'cost' => $savedCost,
        ];
    }
}
