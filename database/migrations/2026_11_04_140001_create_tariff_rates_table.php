<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tariff_rates')) {
            Schema::create('tariff_rates', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 150);
                $table->decimal('power_limit_va', 10, 2);
                $table->decimal('rate_per_kwh', 12, 4);
                $table->decimal('base_fee', 12, 2)->default(0.00);
                $table->date('valid_from');
                $table->date('valid_to')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['valid_from', 'valid_to', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tariff_rates');
    }
};
