<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DashboardController; // <-- Tambahkan ini
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FuelEntryController;
use App\Http\Controllers\AiScanController; // Jangan lupa import di paling atas!
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Auth\GoogleAuthController;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google.login');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callback']);

// Ubah route dashboard menjadi seperti ini:
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // Rute Menu Utama Aplikasi
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/history', [DashboardController::class, 'history'])->name('history');
Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');
Route::get('/account', [DashboardController::class, 'account'])->name('account');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');

    Route::post('/switch-vehicle', [DashboardController::class, 'switchVehicle'])->name('dashboard.switch_vehicle');

    Route::post('/fuel-entries', [FuelEntryController::class, 'store'])->name('fuel.store');
        Route::get('/fuel/create', [FuelEntryController::class, 'create'])->name('fuel.create');
    Route::put('/fuel-entries/{fuelEntry}', [FuelEntryController::class, 'update'])->name('fuel.update');
    Route::delete('/fuel-entries/{fuelEntry}', [FuelEntryController::class, 'destroy'])->name('fuel.destroy');


    Route::post('/scan-receipt', [AiScanController::class, 'scan'])->name('ai.scan');

     Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');


    Route::get('/money', [ExpenseController::class, 'index'])->name('money.index');
    Route::get('/money/create', [ExpenseController::class, 'create'])->name('money.create');
    Route::get('/money/history', [ExpenseController::class, 'history'])->name('money.history');
    Route::post('/money/scan', [ExpenseController::class, 'scan'])->name('money.scan');
    Route::post('/money', [ExpenseController::class, 'store'])->name('money.store');


});

require __DIR__.'/auth.php';
