<?php

use App\Http\Controllers\Web\AnalysisController;
use App\Http\Controllers\Web\AppliancesController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HistoryController;
use App\Http\Controllers\Web\LandingController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\RecommendationsController;
use App\Http\Controllers\Web\ReportsController;
use App\Http\Controllers\Web\RuleController;
use App\Http\Controllers\Web\SettingsController;
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

    // Dashboard: summary and KPIs only
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Analisis Konsumsi Energi
    Route::get('/analysis', [AnalysisController::class, 'index'])->name('analysis.index');
    Route::post('/households/{household}/reanalyze', [AnalysisController::class, 'reanalyze'])->name('households.reanalyze');

    // Rekomendasi Penghematan Energi
    Route::get('/recommendations', [RecommendationsController::class, 'index'])->name('recommendations.index');

    // Data Peralatan Elektronik
    Route::get('/appliances', [AppliancesController::class, 'index'])->name('appliances.index');
    Route::get('/appliances/{appliance}', [AppliancesController::class, 'show'])->name('appliances.show');
    Route::post('/households/{household}/appliances', [AppliancesController::class, 'store'])->name('households.appliances.store');
    Route::put('/appliances/{appliance}', [AppliancesController::class, 'update'])->name('appliances.update');
    Route::delete('/appliances/{appliance}', [AppliancesController::class, 'destroy'])->name('appliances.destroy');

    // Riwayat Analisis
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/{period}', [HistoryController::class, 'show'])->name('history.show');

    // Laporan Konsumsi Energi
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::post('/households/{household}/meter-readings', [ReportsController::class, 'storeMeterReading'])->name('households.meter.store');

    // Profil Pengguna
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Pengaturan (rumah tangga)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/households', [SettingsController::class, 'storeHousehold'])->name('dashboard.households.store');
    Route::put('/households/{household}', [SettingsController::class, 'updateHousehold'])->name('settings.households.update');
    Route::delete('/households/{household}', [SettingsController::class, 'destroyHousehold'])->name('settings.households.destroy');

    // Administrator
    Route::get('/dashboard/aturan', [RuleController::class, 'index'])->name('dashboard.rules');
});
