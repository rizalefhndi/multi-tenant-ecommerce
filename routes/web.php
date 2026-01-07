<?php

use App\Http\Controllers\Landlord\DashboardController;
use App\Http\Controllers\Landlord\MyStoresController;
use App\Http\Controllers\Landlord\PricingController;
use App\Http\Controllers\Landlord\SSOController;
use App\Http\Controllers\Landlord\StoreController;
use App\Http\Controllers\Landlord\TenantController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Landlord (Central) Routes
|--------------------------------------------------------------------------
|
| Routes for the platform owner (Super Admin).
| Accessed via central domain (e.g. localhost:8000).
|
*/

Route::domain('onyx.test')->group(function () {
    // Public landing page
    // Public landing page
    Route::get('/', function () {
        return Inertia::render('LandingPage', [
            'auth' => [
                'user' => auth()->user(),
            ],
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    })->name('home');

    // Pricing page (public)
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

    // Store management (requires auth)
    Route::middleware(['auth'])->group(function () {
        Route::get('/create-store', [StoreController::class, 'create'])->name('store.create');
        Route::post('/create-store', [StoreController::class, 'store'])->name('store.store');
        Route::get('/my-stores', [MyStoresController::class, 'index'])->name('my-stores');
        
        // SSO redirect to tenant
        Route::get('/sso/{tenant}', [SSOController::class, 'redirect'])->name('sso.redirect');
    });

    // Auth routes (Breeze)
    require __DIR__.'/auth.php';

    // Landlord protected routes - Only Super Admin can access
    Route::middleware(['auth', 'superadmin'])->prefix('landlord')->name('landlord.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Tenant Management
        Route::resource('tenants', TenantController::class);

        // Tenant Actions
        Route::post('tenants/{tenant}/migrate', [TenantController::class, 'migrate'])->name('tenants.migrate');
        Route::post('tenants/{tenant}/seed', [TenantController::class, 'seed'])->name('tenants.seed');
        Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');

        // Profile
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'edit'])->name('edit');
            Route::patch('/', [ProfileController::class, 'update'])->name('update');
            Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Tenant routes are defined in routes/tenant.php
| They use InitializeTenancyByDomain middleware for proper multi-tenancy.
|
*/
