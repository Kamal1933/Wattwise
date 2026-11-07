<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expert_rules')) {
            Schema::create('expert_rules', function (Blueprint $table) {
                $table->id();
                $table->string('rule_code', 50)->unique();
                $table->string('category', 50);
                $table->string('title', 150);
                $table->text('description');
                $table->text('recommendation_template');
                $table->text('actionable_steps');
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->integer('priority')->default(100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'priority']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_rules');
    }
};
