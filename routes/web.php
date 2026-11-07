<?php

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/households/{household}/reanalyze', [DashboardController::class, 'reanalyze'])->name('households.reanalyze');
Route::post('/households/{household}/appliances', [DashboardController::class, 'storeAppliance'])->name('households.appliances.store');
Route::put('/appliances/{appliance}', [DashboardController::class, 'updateAppliance'])->name('appliances.update');
Route::delete('/appliances/{appliance}', [DashboardController::class, 'destroyAppliance'])->name('appliances.destroy');
Route::post('/households/{household}/meter-readings', [DashboardController::class, 'storeMeterReading'])->name('households.meter.store');
