<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rule_conditions')) {
            Schema::create('rule_conditions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rule_id')->constrained('expert_rules')->cascadeOnDelete();
                $table->string('parameter_name', 80);
                $table->enum('operator', ['>', '>=', '<', '<=', '==', '!=', 'IN', 'CONTAINS']);
                $table->string('comparison_value', 100);
                $table->enum('logical_operator', ['AND', 'OR'])->default('AND');
                $table->timestamps();

                $table->index('rule_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_conditions');
    }
};
