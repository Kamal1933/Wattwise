<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recommendation_history')) {
            Schema::create('recommendation_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('calculation_period_id')->constrained()->cascadeOnDelete();
                $table->foreignId('rule_id')->constrained('expert_rules')->restrictOnDelete();
                $table->string('rule_code', 50);
                $table->string('title', 150);
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->text('explanation_evidence');
                $table->text('recommendation_text');
                $table->text('actionable_steps');
                $table->decimal('estimated_kwh_saving', 10, 2)->default(0.00);
                $table->decimal('estimated_cost_saving', 14, 2)->default(0.00);
                $table->enum('status', ['active', 'acknowledged', 'implemented', 'dismissed'])->default('active');
                $table->timestamps();

                $table->index('calculation_period_id');
                $table->index('rule_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_history');
    }
};
