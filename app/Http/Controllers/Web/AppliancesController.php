<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesHouseholdContext;
use App\Models\Appliance;
use App\Models\CalculationItem;
use App\Models\Household;
use App\Services\Calculation\PeriodSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Data Peralatan Elektronik: list, detail, add, edit and delete appliances.
 */
class AppliancesController extends Controller
{
    use ResolvesHouseholdContext;

    public function index(Request $request): View
    {
        return view('appliances.index', $this->pageData($request));
    }

    public function show(Request $request, Appliance $appliance): View
    {
        Gate::authorize('view', $appliance->household);

        $request->query->set('household_id', (string) $appliance->household_id);
        $data = $this->pageData($request);
        $appliance->loadMissing('category');

        /** @var PeriodSummary $summary */
        $summary = $data['summary'];
        $item = $summary->itemsByAppliance[$appliance->id] ?? null;
        $relatedRecommendations = $summary->recommendations
            ->filter(fn ($rec): bool => str_contains((string) $rec->explanation_evidence, "'{$appliance->name}'"))
            ->values();

        $history = CalculationItem::with('calculationPeriod')
            ->where('appliance_id', $appliance->id)
            ->get()
            ->sortByDesc(fn (CalculationItem $row) => $row->calculationPeriod?->start_date)
            ->values();

        return view('appliances.show', $data + [
            'appliance' => $appliance,
            'item' => $item,
            'relatedRecommendations' => $relatedRecommendations,
            'history' => $history,
        ]);
    }

    public function store(Request $request, Household $household): RedirectResponse
    {
        Gate::authorize('update', $household);

        $validated = $this->validateAppliance($request);
        $attributes = Arr::except($validated, ['photo', 'remove_photo']);
        $attributes['is_active'] = true;

        if ($request->hasFile('photo')) {
            $attributes['photo_path'] = $request->file('photo')->store('appliances', 'public');
        }

        $appliance = $household->appliances()->create($attributes);

        $this->rememberHousehold($household);
        $this->refreshAnalysis($household);

        return $this->backWithStatus("{$appliance->name} ditambahkan. Analisis sudah diperbarui.", 'appliances.index');
    }

    public function update(Request $request, Appliance $appliance): RedirectResponse
    {
        Gate::authorize('update', $appliance->household);

        $validated = $this->validateAppliance($request);
        $attributes = Arr::except($validated, ['photo', 'remove_photo']);

        if ($request->hasFile('photo') || $request->boolean('remove_photo')) {
            $appliance->deletePhoto();
            $attributes['photo_path'] = $request->hasFile('photo')
                ? $request->file('photo')->store('appliances', 'public')
                : null;
        }

        $appliance->update($attributes);
        $this->refreshAnalysis($appliance->household);

        return $this->backWithStatus("Perubahan {$appliance->name} disimpan. Analisis sudah diperbarui.", 'appliances.index');
    }

    public function destroy(Appliance $appliance): RedirectResponse
    {
        $household = $appliance->household;
        Gate::authorize('update', $household);

        $detailUrl = route('appliances.show', $appliance);
        $appliance->deletePhoto();
        $appliance->delete();
        $this->refreshAnalysis($household);

        $message = "{$appliance->name} dihapus. Analisis sudah diperbarui.";

        return url()->previous() === $detailUrl
            ? redirect()->route('appliances.index')->with('status', $message)
            : $this->backWithStatus($message, 'appliances.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateAppliance(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:appliance_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'power_watt' => ['required', 'numeric', 'min:0.5', 'max:25000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'daily_hours' => ['required', 'numeric', 'min:0.1', 'max:24.0'],
            'days_per_month' => ['required', 'integer', 'min:1', 'max:31'],
            'usage_factor' => ['required', 'numeric', 'min:0.05', 'max:1.0'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
        ]);
    }
}
