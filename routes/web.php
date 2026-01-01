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
|
| Routes for the platform owner (Super Admin).
| Accessed via central domain (e.g. localhost:8000).
|
*/

Route::domain('localhost')->group(function () {
    // Public landing page
    Route::get('/', function () {
        return Inertia::render('LandingPage');
    })->name('home');

    // Pricing page (public)
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

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
| Routes for individual stores/tenants.
| Accessed via subdomain (e.g. demo.localhost).
|
*/

Route::domain('{tenant}.localhost')->middleware(['tenant.identify'])->group(function () {
    
    // Storefront (Buyer)
    Route::get('/', function () {
        // Temporary placeholder
        return "Welcome to " . request()->getHost();
    })->name('tenant.home');

    // Store Admin Authentication (separate from central?)
    // Uses the same Login logic but scoped? 
    // For now, let's reuse auth routes but ensure they work on subdomain
    
    // Tenant Admin Routes
    Route::middleware(['auth'])->prefix('admin')->name('tenant.admin.')->group(function () {
        Route::get('/', function () {
             return "Tenant Admin Dashboard";
        })->name('dashboard');
    });
});
