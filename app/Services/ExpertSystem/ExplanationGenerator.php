<?php

namespace App\Services\ExpertSystem;

use App\Models\ExpertRule;

class ExplanationGenerator
{
    /**
     * Generate explainable evidence text showing exact mathematical calculations.
     *
     * @param  array<string, mixed>  $facts
     */
    public function generateEvidence(ExpertRule $rule, array $facts): string
    {
        $applianceName = $facts['appliance_name'] ?? 'Peralatan';
        $monthlyKwh = isset($facts['monthly_kwh']) ? number_format((float) $facts['monthly_kwh'], 1, ',', '.') : '0';
        $sharePct = isset($facts['appliance_share_pct']) ? number_format((float) $facts['appliance_share_pct'], 1, ',', '.') : '0';
        $dailyHours = isset($facts['daily_hours']) ? number_format((float) $facts['daily_hours'], 1, ',', '.') : '0';
        $powerWatt = isset($facts['power_watt']) ? number_format((float) $facts['power_watt'], 0, ',', '.') : '0';
        $powerLimitVa = isset($facts['power_limit_va']) ? number_format((float) $facts['power_limit_va'], 0, ',', '.') : '0';
        $simultaneousWatt = isset($facts['simultaneous_active_watt']) ? number_format((float) $facts['simultaneous_active_watt'], 0, ',', '.') : '0';
        $loadRatioPct = isset($facts['simultaneous_load_ratio']) ? number_format((float) $facts['simultaneous_load_ratio'] * 100, 1, ',', '.') : '0';

        switch ($rule->rule_code) {
            case 'R-DOM-01':
                return "Bukti Inferensi: Peralatan '{$applianceName}' menyerap {$sharePct}% ({$monthlyKwh} kWh) dari total listrik rumah tangga. Ini melebihi ambang batas aturan dominansi (>= 35.0%).";

            case 'R-COOL-01':
                return "Bukti Inferensi: Pendingin '{$applianceName}' aktif selama {$dailyHours} jam per hari (ambang batas >= 8.0 jam) dengan estimasi serapan {$monthlyKwh} kWh/bulan.";

            case 'R-VAMP-01':
                return "Bukti Inferensi: Perangkat elektronik '{$applianceName}' terdeteksi beroperasi standby terus menerus selama {$dailyHours} jam/hari tanpa pemutus arus fisik.";

            case 'R-LIGHT-01':
                return "Bukti Inferensi: Lampu '{$applianceName}' memiliki daya tinggi {$powerWatt} W (ambang batas lampu konvensional >= 25.0 W).";

            case 'R-WATER-01':
                return "Bukti Inferensi: Peralatan air '{$applianceName}' berdaya {$powerWatt} W menyedot beban kumulatif {$monthlyKwh} kWh/bulan (ambang batas >= 40.0 kWh).";

            case 'R-CAP-01':
                return "Bukti Inferensi: Total daya serap peralatan aktif ({$simultaneousWatt} W) mencapai {$loadRatioPct}% dari kapasitas daya kontrak PLN ({$powerLimitVa} VA).";

            default:
                return "Bukti Inferensi: Aturan {$rule->rule_code} terpenuhi berdasarkan parameter kondisi yang sesuai dengan data inventaris aktif.";
        }
    }

    /**
     * Interpolate variables in recommendation template.
     *
     * @param  array<string, mixed>  $facts
     */
    public function formatRecommendation(string $template, array $facts): string
    {
        $replacements = [
            ':appliance_name' => $facts['appliance_name'] ?? 'Peralatan',
            ':monthly_kwh' => isset($facts['monthly_kwh']) ? number_format((float) $facts['monthly_kwh'], 1, ',', '.') : '0',
            ':appliance_share_pct' => isset($facts['appliance_share_pct']) ? number_format((float) $facts['appliance_share_pct'], 1, ',', '.') : '0',
            ':daily_hours' => isset($facts['daily_hours']) ? number_format((float) $facts['daily_hours'], 1, ',', '.') : '0',
            ':power_watt' => isset($facts['power_watt']) ? number_format((float) $facts['power_watt'], 0, ',', '.') : '0',
            ':power_limit_va' => isset($facts['power_limit_va']) ? number_format((float) $facts['power_limit_va'], 0, ',', '.') : '0',
            ':simultaneous_active_watt' => isset($facts['simultaneous_active_watt']) ? number_format((float) $facts['simultaneous_active_watt'], 0, ',', '.') : '0',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
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
                // Setting thermostat 2-3°C higher saves ~15% AC consumption
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
                // Capacity overload prevention does not directly reduce baseline kWh, but prevents penalties/tripping
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
