<?php

declare(strict_types=1);

use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // Public routes (tidak perlu login)
    Route::get('/', function () {
        return Inertia::render('Welcome');
    })->name('home');

    // Auth routes (Breeze default)
    require __DIR__.'/auth.php';

    // Protected routes (perlu login)
    Route::middleware(['auth', 'verified'])->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

        // Profile routes (dari Breeze)
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'edit'])->name('edit');
            Route::patch('/', [ProfileController::class, 'update'])->name('update');
            Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        });

        // Product routes
        Route::resource('products', ProductController::class);

        // Toggle product status
        Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->name('products.toggle-status');

        // Cart routes
        Route::prefix('cart')->name('cart.')->group(function () {
            // Main cart page
            Route::get('/', [CartController::class, 'index'])->name('index');

            // Cart actions
            Route::post('/add', [CartController::class, 'addItem'])->name('add');
            Route::patch('/update/{item}', [CartController::class, 'updateItem'])->name('update');
            Route::delete('/remove/{item}', [CartController::class, 'removeItem'])->name('remove');
            Route::delete('/clear', [CartController::class, 'clear'])->name('clear');

            // Checkout
            Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');

            // Cart summary (AJAX)
            Route::get('/summary', [CartController::class, 'summary'])->name('summary');
        });

        // Cart Item routes (granular control)
        Route::prefix('cart-items')->name('cart-items.')->group(function () {
            // Increment/Decrement
            Route::post('/{cartItem}/increment', [CartItemController::class, 'increment'])
                ->name('increment');
            Route::post('/{cartItem}/decrement', [CartItemController::class, 'decrement'])
                ->name('decrement');

            // Update quantity
            Route::patch('/{cartItem}/quantity', [CartItemController::class, 'updateQuantity'])
                ->name('update-quantity');

            // Delete
            Route::delete('/{cartItem}', [CartItemController::class, 'destroy'])
                ->name('destroy');

            // AJAX endpoints
            Route::post('/{cartItem}/ajax/increment', [CartItemController::class, 'ajaxIncrement'])
                ->name('ajax.increment');
            Route::post('/{cartItem}/ajax/decrement', [CartItemController::class, 'ajaxDecrement'])
                ->name('ajax.decrement');
        });
    });
});
