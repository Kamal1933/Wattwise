<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('calculation_periods')) {
            Schema::create('calculation_periods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('household_id')->constrained()->cascadeOnDelete();
                $table->string('period_name', 100);
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('total_estimated_kwh', 12, 3);
                $table->decimal('total_estimated_cost', 14, 2);
                $table->decimal('applied_rate_per_kwh', 12, 4);
                $table->json('calculation_assumptions')->nullable();
                $table->timestamps();

                $table->index(['household_id', 'start_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calculation_periods');
    }
};
