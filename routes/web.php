<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/households', [DashboardController::class, 'storeHousehold'])->name('dashboard.households.store');
    Route::post('/households/{household}/reanalyze', [DashboardController::class, 'reanalyze'])->name('households.reanalyze');
    Route::post('/households/{household}/appliances', [DashboardController::class, 'storeAppliance'])->name('households.appliances.store');
    Route::put('/appliances/{appliance}', [DashboardController::class, 'updateAppliance'])->name('appliances.update');
    Route::delete('/appliances/{appliance}', [DashboardController::class, 'destroyAppliance'])->name('appliances.destroy');
    Route::post('/households/{household}/meter-readings', [DashboardController::class, 'storeMeterReading'])->name('households.meter.store');
});
