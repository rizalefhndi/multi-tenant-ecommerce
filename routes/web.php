<?php

use App\Http\Controllers\Landlord\DashboardController;
use App\Http\Controllers\Landlord\PricingController;
use App\Http\Controllers\Landlord\TenantController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Landlord (Central) Routes
|--------------------------------------------------------------------------
*/

// Public landing page
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Pricing page (public)
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

// Auth routes (Breeze)
require __DIR__.'/auth.php';

// Landlord protected routes
Route::middleware(['auth'])->prefix('landlord')->name('landlord.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Tenant Management
    Route::resource('tenants', TenantController::class);

    // Additional tenant actions (harus sebelum resource atau pakai explicit route name)
    Route::post('tenants/{tenant}/migrate', [TenantController::class, 'migrate'])
        ->name('tenants.migrate');
    Route::post('tenants/{tenant}/seed', [TenantController::class, 'seed'])
        ->name('tenants.seed');

    // Profile routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});
