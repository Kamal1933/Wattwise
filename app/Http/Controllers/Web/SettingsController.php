<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesHouseholdContext;
use App\Models\Household;
use App\Models\TariffRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Pengaturan: manage households (name, PLN tariff, address, occupants) and the active one.
 */
class SettingsController extends Controller
{
    use ResolvesHouseholdContext;

    public function index(Request $request): View
    {
        $context = $this->householdContext($request);
        $context['households']->loadCount('appliances');

        return view('settings.index', $context + [
            'tariffs' => TariffRate::where('is_active', true)->orderBy('power_limit_va')->get(),
        ]);
    }

    public function storeHousehold(Request $request): RedirectResponse
    {
        $household = $request->user()->households()->create($this->validateHousehold($request));
        $this->rememberHousehold($household);

        return redirect()->route('dashboard')
            ->with('status', "Rumah tangga {$household->name} ditambahkan. Mulai dengan menambahkan peralatan listrik.");
    }

    public function updateHousehold(Request $request, Household $household): RedirectResponse
    {
        Gate::authorize('update', $household);

        $tariffChanged = (int) $request->input('tariff_id') !== (int) $household->tariff_id;
        $household->update($this->validateHousehold($request));
        $this->rememberHousehold($household);

        if ($tariffChanged && $household->appliances()->where('is_active', true)->exists()) {
            $this->refreshAnalysis($household->fresh());
        }

        return redirect()->route('settings.index')->with('status', "Pengaturan {$household->name} disimpan.");
    }

    public function destroyHousehold(Request $request, Household $household): RedirectResponse
    {
        Gate::authorize('update', $household);

        $household->appliances->each->deletePhoto();
        $name = $household->name;
        $household->delete();

        if ((int) $request->session()->get(self::ACTIVE_HOUSEHOLD_SESSION_KEY) === $household->id) {
            $request->session()->forget(self::ACTIVE_HOUSEHOLD_SESSION_KEY);
        }

        return redirect()->route('settings.index')->with('status', "Rumah tangga {$name} beserta datanya dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHousehold(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'tariff_id' => ['required', 'exists:tariff_rates,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'occupant_count' => ['required', 'integer', 'min:1', 'max:50'],
        ]);
    }
}
