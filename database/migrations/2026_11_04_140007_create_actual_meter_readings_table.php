<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('actual_meter_readings')) {
            Schema::create('actual_meter_readings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('household_id')->constrained()->cascadeOnDelete();
                $table->foreignId('calculation_period_id')->nullable()->constrained()->nullOnDelete();
                $table->date('reading_date');
                $table->decimal('meter_start_kwh', 10, 2)->default(0.00);
                $table->decimal('meter_end_kwh', 10, 2);
                $table->decimal('actual_kwh_consumed', 10, 2);
                $table->decimal('actual_cost_billed', 14, 2)->nullable();
                $table->decimal('variance_kwh', 10, 2)->nullable();
                $table->decimal('variance_pct', 6, 2)->nullable();
                $table->string('receipt_image_url', 255)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['household_id', 'reading_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('actual_meter_readings');
    }
};
