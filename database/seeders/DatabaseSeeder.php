<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TariffRateSeeder::class,
            ApplianceCategorySeeder::class,
            ExpertRuleSeeder::class,
            HouseholdSeeder::class,
        ]);
    }
}
