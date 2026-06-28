<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BlacklistController;
use App\Http\Controllers\Admin\SettingController;

// Auth routes (guest)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Blacklist CRUD
    Route::get('/blacklist', [BlacklistController::class, 'index'])->name('blacklist.index');
    Route::post('/blacklist', [BlacklistController::class, 'store'])->name('blacklist.store');
    Route::put('/blacklist/{id}', [BlacklistController::class, 'update'])->name('blacklist.update');
    Route::delete('/blacklist/{id}', [BlacklistController::class, 'destroy'])->name('blacklist.destroy');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Export
    Route::get('/export', function () { return view('export'); })->name('export.index');
    Route::get('/export/pdf', [DashboardController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/export/csv', [DashboardController::class, 'exportCsv'])->name('export.csv');

    // Maps
    Route::get('/maps', [DashboardController::class, 'maps'])->name('maps');
    Route::get('/api/locations', [DashboardController::class, 'locations'])->name('api.locations');
});
