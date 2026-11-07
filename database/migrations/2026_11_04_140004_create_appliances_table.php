<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('appliances')) {
            Schema::create('appliances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('household_id')->constrained()->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('appliance_categories')->restrictOnDelete();
                $table->string('name', 150);
                $table->decimal('power_watt', 8, 2);
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('daily_hours', 4, 2);
                $table->unsignedTinyInteger('days_per_month')->default(30);
                $table->decimal('usage_factor', 4, 3)->default(1.000);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('household_id');
                $table->index('category_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('appliances');
    }
};
