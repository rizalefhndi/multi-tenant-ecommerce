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
    
    // Storefront (Buyer) - Public
    Route::get('/', function () {
        $tenant = app('tenant');
        return Inertia::render('Tenant/Storefront/Home', [
            'tenant' => [
                'id' => $tenant->id ?? 'unknown',
                'name' => $tenant->name ?? 'Store',
            ],
        ]);
    })->name('tenant.home');

    // Tenant Authentication Routes
    Route::middleware('guest')->group(function () {
        Route::get('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
            ->name('tenant.login');
        Route::post('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
            ->name('tenant.logout');
    });

    // Tenant Admin Routes - Only for tenant admins
    Route::middleware(['auth', 'tenant.admin'])->prefix('admin')->name('tenant.admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Tenant\DashboardController::class, 'index'])
            ->name('dashboard');
        
        // Products (coming soon)
        // Route::resource('products', \App\Http\Controllers\Tenant\ProductController::class);
        
        // Orders (coming soon)
        // Route::resource('orders', \App\Http\Controllers\Tenant\OrderController::class);
    });
});

