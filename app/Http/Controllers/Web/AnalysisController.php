<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesHouseholdContext;
use App\Models\CalculationPeriod;
use App\Models\ExpertRule;
use App\Models\Household;
use App\Services\ExpertSystem\ExplanationGenerator;
use App\Services\ExpertSystem\ForwardChainingEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Analisis Konsumsi Energi: appliance input, step-by-step calculation, and the inference trace.
 */
class AnalysisController extends Controller
{
    use ResolvesHouseholdContext;

    public function __construct(
        protected ForwardChainingEngine $expertEngine,
        protected ExplanationGenerator $explanationGenerator
    ) {}

    public function index(Request $request): View
    {
        $data = $this->pageData($request);

        return view('analysis.index', $data + [
            'inferenceSteps' => $this->inferenceSteps($data['currentPeriod']),
        ]);
    }

    public function reanalyze(Household $household): RedirectResponse
    {
        Gate::authorize('update', $household);

        $this->rememberHousehold($household);
        $this->refreshAnalysis($household);

        return $this->backWithStatus('Analisis diperbarui dengan data peralatan terbaru.', 'analysis.index');
    }

    /**
     * Replay forward chaining (without saving) to show which rule fired in which cycle.
     *
     * @return list<array{cycle: int, rule_code: string, title: string, subject: string, derived: string|null, used: list<string>, recommends: bool}>
     */
    protected function inferenceSteps(?CalculationPeriod $period): array
    {
        if (! $period) {
            return [];
        }

        $rules = ExpertRule::with('conditions')->where('is_active', true)->orderBy('priority')->orderBy('id')->get();
        $result = $this->expertEngine->infer($this->expertEngine->extractWorkingMemory($period), $rules);

        return array_map(function (array $firing): array {
            $rule = $firing['rule'];
            $derived = collect($firing['asserted'])
                ->map(fn (string $value, string $fact): string => $this->explanationGenerator->labelFor($fact).' = '.$this->explanationGenerator->formatFact($fact, $value))
                ->first();

            return [
                'cycle' => $firing['cycle'],
                'rule_code' => $rule->rule_code,
                'title' => $rule->title,
                'subject' => $rule->isHouseholdScoped() ? 'Rumah tangga' : ($firing['facts']['appliance_name'] ?? 'Peralatan'),
                'derived' => $derived,
                'used' => array_values(array_unique($firing['derived_from'])),
                'recommends' => (bool) $rule->produces_recommendation,
            ];
        }, $result['firings']);
    }
}
