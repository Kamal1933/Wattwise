<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add true forward chaining support:
     * - expert_rules.scope replaces the hard-coded category check (household vs appliance rules).
     * - expert_rules.conclusion_fact / conclusion_value let a rule assert a new fact into working memory.
     * - expert_rules.produces_recommendation allows intermediate rules that only derive facts.
     * - rule_conditions.condition_group replaces logical_operator: AND inside a group, OR between groups.
     */
    public function up(): void
    {
        Schema::table('expert_rules', function (Blueprint $table) {
            $table->string('scope', 20)->default('appliance')->after('category');
            $table->string('conclusion_fact', 80)->nullable()->after('actionable_steps');
            $table->string('conclusion_value', 100)->nullable()->after('conclusion_fact');
            $table->boolean('produces_recommendation')->default(true)->after('conclusion_value');
            $table->text('recommendation_template')->nullable()->change();
            $table->text('actionable_steps')->nullable()->change();
        });

        Schema::table('rule_conditions', function (Blueprint $table) {
            $table->unsignedSmallInteger('condition_group')->default(1)->after('rule_id');
        });

        DB::table('expert_rules')
            ->whereIn('category', ['Capacity', 'Tariff', 'Household'])
            ->update(['scope' => 'household']);

        $this->convertLogicalOperatorsToGroups();

        Schema::table('rule_conditions', function (Blueprint $table) {
            $table->dropColumn('logical_operator');
        });
    }

    public function down(): void
    {
        Schema::table('rule_conditions', function (Blueprint $table) {
            $table->enum('logical_operator', ['AND', 'OR'])->default('AND')->after('comparison_value');
        });

        Schema::table('rule_conditions', function (Blueprint $table) {
            $table->dropColumn('condition_group');
        });

        Schema::table('expert_rules', function (Blueprint $table) {
            $table->dropColumn(['scope', 'conclusion_fact', 'conclusion_value', 'produces_recommendation']);
        });
    }

    /**
     * Treat each legacy logical_operator as the connector to the next condition:
     * an "OR" closes the current AND-group and opens a new one.
     */
    protected function convertLogicalOperatorsToGroups(): void
    {
        $conditionsByRule = DB::table('rule_conditions')
            ->orderBy('rule_id')
            ->orderBy('id')
            ->get(['id', 'rule_id', 'logical_operator'])
            ->groupBy('rule_id');

        foreach ($conditionsByRule as $conditions) {
            $group = 1;

            foreach ($conditions as $condition) {
                DB::table('rule_conditions')->where('id', $condition->id)->update(['condition_group' => $group]);

                if ($condition->logical_operator === 'OR') {
                    $group++;
                }
            }
        }
    }
};
