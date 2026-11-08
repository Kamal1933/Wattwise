<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Merge duplicate calculation periods (same household and date range) into the newest one,
     * then enforce uniqueness so recalculation can never create duplicates again.
     */
    public function up(): void
    {
        $duplicateGroups = DB::table('calculation_periods')
            ->select('household_id', 'start_date', 'end_date', DB::raw('MAX(id) as keep_id'))
            ->groupBy('household_id', 'start_date', 'end_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $staleIds = DB::table('calculation_periods')
                ->where('household_id', $group->household_id)
                ->where('start_date', $group->start_date)
                ->where('end_date', $group->end_date)
                ->where('id', '!=', $group->keep_id)
                ->pluck('id');

            DB::table('actual_meter_readings')
                ->whereIn('calculation_period_id', $staleIds)
                ->update(['calculation_period_id' => $group->keep_id]);

            DB::table('recommendation_history')->whereIn('calculation_period_id', $staleIds)->delete();
            DB::table('calculation_items')->whereIn('calculation_period_id', $staleIds)->delete();
            DB::table('calculation_periods')->whereIn('id', $staleIds)->delete();
        }

        Schema::table('calculation_periods', function (Blueprint $table) {
            $table->unique(['household_id', 'start_date', 'end_date'], 'calculation_periods_household_range_unique');
        });
    }

    public function down(): void
    {
        Schema::table('calculation_periods', function (Blueprint $table) {
            $table->dropUnique('calculation_periods_household_range_unique');
        });
    }
};
