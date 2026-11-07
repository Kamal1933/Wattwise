<?php

namespace Database\Seeders;

use App\Models\ExpertRule;
use Illuminate\Database\Seeder;

class ExpertRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'rule_code' => 'R-DOM-01',
                'category' => 'Dominance',
                'title' => 'Beban Energi Dominan Terdeteksi (>35%)',
                'description' => 'Mendeteksi peralatan yang menyerap lebih dari 35% total tagihan listrik bulanan rumah tangga.',
                'recommendation_template' => 'Peralatan :appliance_name menyerap :appliance_share_pct% dari total konsumsi listrik rumah Anda (:monthly_kwh kWh/bulan). Fokus efisiensi pada peralatan ini akan memberikan dampak penghematan terbesar.',
                'actionable_steps' => "1. Kurangi durasi operasional 1-2 jam per hari.\n2. Lakukan pengecekan efisiensi energi atau gunakan timer otomatis.\n3. Pertimbangkan peremajaan ke tipe inverter berdaya lebih hemat.",
                'severity' => 'high',
                'priority' => 10,
                'is_active' => true,
                'conditions' => [
                    ['parameter_name' => 'appliance_share_pct', 'operator' => '>=', 'comparison_value' => '35.0', 'logical_operator' => 'AND'],
                    ['parameter_name' => 'monthly_kwh', 'operator' => '>=', 'comparison_value' => '50.0', 'logical_operator' => 'AND'],
                ],
            ],
            [
                'rule_code' => 'R-COOL-01',
                'category' => 'Cooling',
                'title' => 'Optimasi Operasional Pendingin Ruangan (AC)',
                'description' => 'Mendeteksi pendingin ruangan dengan durasi operasional tinggi (>= 8 jam/hari).',
                'recommendation_template' => 'AC Anda (:appliance_name) beroperasi selama :daily_hours jam per hari dengan konsumsi :monthly_kwh kWh/bulan. Menaikkan suhu setelan dari 20-22°C ke 24-25°C dapat menghemat hingga 15-20% listrik AC.',
                'actionable_steps' => "1. Setel suhu pendingin pada 24°C - 25°C dan gunakan mode Eco/Comfort.\n2. Bersihkan filter AC setiap 2-4 minggu sekali.\n3. Pastikan ruangan tertutup rapat dari ventilasi luar saat AC menyala.",
                'severity' => 'medium',
                'priority' => 20,
                'is_active' => true,
                'conditions' => [
                    ['parameter_name' => 'category_code', 'operator' => '==', 'comparison_value' => 'cooling', 'logical_operator' => 'AND'],
                    ['parameter_name' => 'daily_hours', 'operator' => '>=', 'comparison_value' => '8.0', 'logical_operator' => 'AND'],
                ],
            ],
            [
                'rule_code' => 'R-VAMP-01',
                'category' => 'Vampire',
                'title' => 'Beban Siluman / Standby Power Terdeteksi',
                'description' => 'Mendeteksi perangkat hiburan/elektronik yang menyala non-stop 24 jam dengan konsumsi standby signifikan.',
                'recommendation_template' => 'Perangkat :appliance_name aktif terus menerus 24 jam/hari dan mengonsumsi energi tanpa henti. Beban standby elektronik rumah tangga dapat menyumbang 5-10% tagihan tanpa disadari.',
                'actionable_steps' => "1. Gunakan stopkontak saklar (switch extension cord) untuk memutus total arus saat tidak digunakan.\n2. Matikan colokan TV, konsol, dan adaptor charger saat malam atau bepergian.",
                'severity' => 'medium',
                'priority' => 30,
                'is_active' => true,
                'conditions' => [
                    ['parameter_name' => 'category_code', 'operator' => '==', 'comparison_value' => 'entertainment', 'logical_operator' => 'AND'],
                    ['parameter_name' => 'daily_hours', 'operator' => '>=', 'comparison_value' => '20.0', 'logical_operator' => 'AND'],
                ],
            ],
            [
                'rule_code' => 'R-LIGHT-01',
                'category' => 'Lighting',
                'title' => 'Potensi Pemborosan Daya Penerangan',
                'description' => 'Mendeteksi lampu dengan daya tinggi (>= 25W per titik) yang berpotensi masih menggunakan teknologi pijar/CFL konvensional.',
                'recommendation_template' => 'Lampu penerangan :appliance_name memiliki daya :power_watt W. Mengganti lampu konvensional ke teknologi LED (7W-10W) mempertahankan tingkat terang (lumen) yang sama dengan daya 70-80% lebih hemat.',
                'actionable_steps' => "1. Ganti unit lampu konvensional dengan lampu LED hemat energi (7W - 9W).\n2. Pasang sensor gerak atau timer otomatis pada lampu teras/halaman.",
                'severity' => 'low',
                'priority' => 40,
                'is_active' => true,
                'conditions' => [
                    ['parameter_name' => 'category_code', 'operator' => '==', 'comparison_value' => 'lighting', 'logical_operator' => 'AND'],
                    ['parameter_name' => 'power_watt', 'operator' => '>=', 'comparison_value' => '25.0', 'logical_operator' => 'AND'],
                ],
            ],
            [
                'rule_code' => 'R-WATER-01',
                'category' => 'Water_Pump',
                'title' => 'Pemanas / Pompa Air Beroperasi Berlebihan',
                'description' => 'Mendeteksi pemanas air atau pompa air dengan durasi tinggi atau daya besar.',
                'recommendation_template' => 'Pemanas/pompa air :appliance_name berdaya :power_watt W mengonsumsi :monthly_kwh kWh/bulan. Peralatan pemanas berbasis elemen resistif sangat cepat menyedot kuota daya.',
                'actionable_steps' => "1. Pasang timer pada pemanas air agar menyala 15-30 menit sebelum mandi saja.\n2. Untuk pompa air, periksa tandon/pelampung otomatis dan pastikan tidak ada kebocoran pipa.",
                'severity' => 'high',
                'priority' => 50,
                'is_active' => true,
                'conditions' => [
                    ['parameter_name' => 'category_code', 'operator' => 'IN', 'comparison_value' => 'water_pump', 'logical_operator' => 'AND'],
                    ['parameter_name' => 'monthly_kwh', 'operator' => '>=', 'comparison_value' => '40.0', 'logical_operator' => 'AND'],
                ],
            ],
            [
                'rule_code' => 'R-CAP-01',
                'category' => 'Capacity',
                'title' => 'Peringatan Kapasitas Beban Maksimum Daya Terpasang',
                'description' => 'Mendeteksi jika total daya serap simultan peralatan aktif mendekati batas kapasitas MCB PLN.',
                'recommendation_template' => 'Total daya peralatan aktif Anda mendekati batas kapasitas daya kontrak PLN (:power_limit_va VA). Risiko trip/jeglek sangat tinggi saat beban puncak.',
                'actionable_steps' => "1. Hindari menyalakan AC, setrika, microwave, dan pompa air secara bersamaan.\n2. Buat jadwal penggunaan peralatan berdaya besar di luar jam beban puncak.",
                'severity' => 'critical',
                'priority' => 5,
                'is_active' => true,
                'conditions' => [
                    ['parameter_name' => 'simultaneous_load_ratio', 'operator' => '>=', 'comparison_value' => '0.85', 'logical_operator' => 'AND'],
                ],
            ],
        ];

        foreach ($rules as $ruleData) {
            $conditions = $ruleData['conditions'];
            unset($ruleData['conditions']);

            $rule = ExpertRule::updateOrCreate(['rule_code' => $ruleData['rule_code']], $ruleData);
            $rule->conditions()->delete();
            foreach ($conditions as $cond) {
                $rule->conditions()->create($cond);
            }
        }
    }
}
