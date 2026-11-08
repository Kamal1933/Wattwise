<?php

namespace App\Providers;

use App\Models\ApplianceCategory;
use App\Models\TariffRate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share('rupiah', fn ($value, int $decimals = 0): string => 'Rp'.number_format((float) $value, $decimals, ',', '.'));
        View::share('number', fn ($value, int $decimals = 1): string => number_format((float) $value, $decimals, ',', '.'));
        View::share('severityMeta', [
            'critical' => ['label' => 'Kritis', 'pill' => 'bg-red-100 text-red-800', 'rail' => 'bg-red-500'],
            'high' => ['label' => 'Tinggi', 'pill' => 'bg-amber-100 text-amber-900', 'rail' => 'bg-amber-500'],
            'medium' => ['label' => 'Sedang', 'pill' => 'bg-electric-100 text-electric-600', 'rail' => 'bg-electric'],
            'low' => ['label' => 'Rendah', 'pill' => 'bg-slate-100 text-slate-700', 'rail' => 'bg-slate-400'],
        ]);

        // Shared add/edit dialogs need reference data on every dashboard page.
        View::composer('layouts.partials.dialogs', function ($view): void {
            $data = $view->getData();
            $household = $data['household'] ?? null;

            $view->with([
                'categories' => $data['categories'] ?? ApplianceCategory::orderBy('name')->get(),
                'tariffs' => $data['tariffs'] ?? TariffRate::where('is_active', true)->orderBy('power_limit_va')->get(),
                'currentPeriod' => $data['currentPeriod'] ?? null,
                'lastMeterEnd' => $data['lastMeterEnd'] ?? $household?->actualMeterReadings()->latest('reading_date')->value('meter_end_kwh'),
            ]);
        });

        View::composer('shared.appliance-fields', function ($view): void {
            if (! isset($view->getData()['categories'])) {
                $view->with('categories', ApplianceCategory::orderBy('name')->get());
            }
        });
    }
}
