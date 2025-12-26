<?php

declare(strict_types=1);

use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::get('/', function () {
        return redirect()->route('login');
    })->name('home');

    require __DIR__.'/auth.php';

    Route::middleware(['auth', 'verified'])->group(function () {

        // ==========================================
        // DASHBOARD
        // ==========================================
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

        // ==========================================
        // PROFILE
        // ==========================================
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'edit'])->name('edit');
            Route::patch('/', [ProfileController::class, 'update'])->name('update');
            Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        });

        // ==========================================
        // PRODUCTS
        // ==========================================
        Route::resource('products', ProductController::class);
        Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->name('products.toggle-status');

        // ==========================================
        // CART
        // ==========================================
        Route::prefix('cart')->name('cart.')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('index');
            Route::post('/add', [CartController::class, 'addItem'])->name('add');
            Route::patch('/update/{item}', [CartController::class, 'updateItem'])->name('update');
            Route::delete('/remove/{item}', [CartController::class, 'removeItem'])->name('remove');
            Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
            Route::get('/summary', [CartController::class, 'summary'])->name('summary');
        });

        Route::prefix('cart-items')->name('cart-items.')->group(function () {
            Route::post('/{cartItem}/increment', [CartItemController::class, 'increment'])->name('increment');
            Route::post('/{cartItem}/decrement', [CartItemController::class, 'decrement'])->name('decrement');
            Route::patch('/{cartItem}/quantity', [CartItemController::class, 'updateQuantity'])->name('update-quantity');
            Route::delete('/{cartItem}', [CartItemController::class, 'destroy'])->name('destroy');
            // AJAX endpoints
            Route::post('/{cartItem}/ajax/increment', [CartItemController::class, 'ajaxIncrement'])->name('ajax.increment');
            Route::post('/{cartItem}/ajax/decrement', [CartItemController::class, 'ajaxDecrement'])->name('ajax.decrement');
        });

        // ==========================================
        // ADDRESSES (User)
        // ==========================================
        Route::prefix('addresses')->name('addresses.')->group(function () {
            Route::get('/', [UserAddressController::class, 'index'])->name('index');
            Route::get('/create', [UserAddressController::class, 'create'])->name('create');
            Route::post('/', [UserAddressController::class, 'store'])->name('store');
            Route::get('/{address}', [UserAddressController::class, 'show'])->name('show');
            Route::get('/{address}/edit', [UserAddressController::class, 'edit'])->name('edit');
            Route::put('/{address}', [UserAddressController::class, 'update'])->name('update');
            Route::delete('/{address}', [UserAddressController::class, 'destroy'])->name('destroy');
            Route::post('/{address}/set-default', [UserAddressController::class, 'setDefault'])->name('set-default');
        });

        // API endpoints untuk AJAX (addresses)
        Route::prefix('api/addresses')->name('api.addresses.')->group(function () {
            Route::get('/', [UserAddressController::class, 'apiIndex'])->name('index');
            Route::post('/', [UserAddressController::class, 'apiStore'])->name('store');
        });

        // ==========================================
        // CHECKOUT
        // ==========================================
        Route::prefix('checkout')->name('checkout.')->group(function () {
            Route::get('/', [CheckoutController::class, 'index'])->name('index');
            Route::post('/', [CheckoutController::class, 'process'])->name('process');
            Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
        });

        // ==========================================
        // ORDERS (Customer View)
        // ==========================================
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::post('/{order}/upload-proof', [OrderController::class, 'uploadProof'])->name('upload-proof');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::post('/{order}/reorder', [OrderController::class, 'reorder'])->name('reorder');
            Route::get('/{order}/track', [OrderController::class, 'track'])->name('track');
            Route::post('/{order}/confirm-received', [OrderController::class, 'confirmReceived'])->name('confirm-received');
        });

        // ==========================================
        // ADMIN - ORDER MANAGEMENT
        // ==========================================
        Route::prefix('admin/orders')->name('admin.orders.')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
            Route::patch('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
            Route::patch('/{order}/tracking', [AdminOrderController::class, 'updateTracking'])->name('update-tracking');
            Route::post('/{order}/transactions/{transaction}/verify', [AdminOrderController::class, 'verifyPayment'])
                ->name('verify-payment');
            Route::post('/{order}/transactions/{transaction}/reject', [AdminOrderController::class, 'rejectPayment'])
                ->name('reject-payment');
            Route::get('/{order}/invoice', [AdminOrderController::class, 'printInvoice'])->name('invoice');
        });

    });
});

