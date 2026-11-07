<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('households')) {
            Schema::create('households', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tariff_id')->constrained('tariff_rates')->restrictOnDelete();
                $table->string('name', 150);
                $table->string('address', 255)->nullable();
                $table->unsignedInteger('occupant_count')->default(4);
                $table->timestamps();

                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
