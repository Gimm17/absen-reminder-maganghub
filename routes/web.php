<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\SubscribeController;
use App\Http\Controllers\Api\CheckinController;
use App\Http\Controllers\Api\VapidController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('app'))->name('home');
Route::get('/register', fn () => view('app'))->name('register');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin');

// API
Route::prefix('api')->group(function () {
    Route::get('/vapid-public-key', [VapidController::class, 'show'])->name('api.vapid');
    Route::post('/subscribe', [SubscribeController::class, 'store'])->name('api.subscribe');
    Route::post('/checkin', [CheckinController::class, 'store'])->name('api.checkin');
    Route::get('/checkin/today', [CheckinController::class, 'today'])->name('api.checkin.today');

    // Admin (gate di controller kalau perlu; SPA shell handle)
    Route::middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->prefix('admin')->group(function () {
        Route::get('/summary', [AdminController::class, 'summary'])->name('api.admin.summary');
        Route::get('/export.csv', [AdminController::class, 'exportCsv'])->name('api.admin.export');
    });

    Route::get('/health', fn () => response()->json([
        'ok' => true,
        'time' => now()->toIso8601String(),
        'last_cron' => cache()->get('last_reminder_run'),
    ]))->name('api.health');
});