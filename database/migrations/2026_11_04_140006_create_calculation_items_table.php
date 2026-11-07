<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('calculation_items')) {
            Schema::create('calculation_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('calculation_period_id')->constrained()->cascadeOnDelete();
                $table->foreignId('appliance_id')->constrained()->cascadeOnDelete();
                $table->decimal('power_watt', 8, 2);
                $table->unsignedInteger('quantity');
                $table->decimal('daily_hours', 4, 2);
                $table->unsignedTinyInteger('days_per_month');
                $table->decimal('usage_factor', 4, 3);
                $table->decimal('monthly_kwh', 10, 3);
                $table->decimal('monthly_cost', 12, 2);
                $table->decimal('percentage_of_total', 5, 2);
                $table->timestamps();

                $table->index('calculation_period_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calculation_items');
    }
};
