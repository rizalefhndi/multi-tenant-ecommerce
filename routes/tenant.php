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
    'tenant.active', // Check if tenant is not suspended
])->group(function () {

    Route::get('/', function () {
        // If user is logged in, go to dashboard
        if (auth()->check()) {
            return redirect('/dashboard');
        }
        // Otherwise show login page
        return redirect('/login');
    })->name('tenant.home');

    // SSO route for cross-domain login (from central after store creation)
    Route::get('/sso', function (\Illuminate\Http\Request $request) {
        \Log::info('Tenant SSO: Start', ['token_exists' => $request->has('token')]);
        
        $token = $request->query('token');
        
        if (!$token) {
            \Log::error('Tenant SSO: No token provided');
            return redirect('/login')->with('error', 'Invalid login token.');
        }

        \Log::info('Tenant SSO: Looking up token', ['token_prefix' => substr($token, 0, 10) . '...']);

        // Find and validate token from central database
        try {
            $loginToken = \App\Models\TenantLoginToken::findValid($token);
            \Log::info('Tenant SSO: Token lookup result', ['found' => $loginToken ? true : false]);
        } catch (\Exception $e) {
            \Log::error('Tenant SSO: Token lookup error', ['error' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Token lookup failed: ' . $e->getMessage());
        }
        
        if (!$loginToken) {
            \Log::error('Tenant SSO: Token not found or expired');
            return redirect()->route('login')->with('error', 'Login token expired or invalid.');
        }

        \Log::info('Tenant SSO: Token valid, user_id=' . $loginToken->user_id);

        // Get user from central database directly
        try {
            $centralUser = \Illuminate\Support\Facades\DB::connection('pgsql')
                ->table('users')
                ->where('id', $loginToken->user_id)
                ->first();
            \Log::info('Tenant SSO: Central user lookup', ['found' => $centralUser ? true : false]);
        } catch (\Exception $e) {
            \Log::error('Tenant SSO: Central user lookup error', ['error' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'User lookup failed: ' . $e->getMessage());
        }
        
        if (!$centralUser) {
            $loginToken->consume();
            \Log::error('Tenant SSO: Central user not found');
            return redirect()->route('login')->with('error', 'User not found.');
        }

        \Log::info('Tenant SSO: Found central user', ['email' => $centralUser->email]);

        // Consume the token (one-time use)
        $loginToken->consume();

        // Find or create user in tenant database
        try {
            $tenantUser = \App\Models\User::firstOrCreate(
                ['email' => $centralUser->email],
                [
                    'name' => $centralUser->name,
                    'password' => $centralUser->password,
                    'email_verified_at' => $centralUser->email_verified_at ?? now(), // Ensure verified
                    'role' => 'admin', // Store owner gets admin role in tenant
                ]
            );
            
            // Ensure email is verified (in case user already existed without verification)
            if (!$tenantUser->email_verified_at) {
                $tenantUser->email_verified_at = now();
                $tenantUser->save();
            }
            
            \Log::info('Tenant SSO: Tenant user created/found', ['id' => $tenantUser->id, 'email' => $tenantUser->email]);
        } catch (\Exception $e) {
            \Log::error('Tenant SSO: Tenant user creation error', ['error' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'User creation failed: ' . $e->getMessage());
        }

        // Log the user in on tenant domain with remember
        auth()->login($tenantUser, true);
        
        // Ensure session is saved before redirect
        session()->save();
        
        \Log::info('Tenant SSO: User logged in, session saved, redirecting to admin');

        return redirect('/admin')->with('success', 'Welcome to your store!');
    })->name('sso.login');

    require __DIR__.'/auth.php';

    Route::middleware(['auth', 'verified'])->group(function () {

        // ==========================================
        // DASHBOARD - Redirect based on role
        // ==========================================
        Route::get('/dashboard', function () {
            $user = auth()->user();
            if ($user->isAdmin()) {
                return Inertia::render('Dashboard');
            }
            // Customer goes to storefront/shop
            return redirect()->route('customer.home');
        })->name('dashboard');

        // ==========================================
        // ADMIN DASHBOARD (Admin Only)
        // ==========================================
        
        // Redirect /admin to /admin/dashboard
        Route::get('/admin', function () {
            return redirect()->route('admin.dashboard');
        })->middleware('role:admin')->name('admin.index');
        
        Route::get('/admin/dashboard', function () {
            return Inertia::render('Dashboard');
        })->middleware('role:admin')->name('admin.dashboard');

        // ==========================================
        // CUSTOMER HOME (Customer View)
        // ==========================================
        Route::get('/shop', function () {
            $products = \App\Models\Product::where('is_active', true)
                ->latest()
                ->paginate(12);
            return Inertia::render('Customer/Shop', [
                'products' => $products,
            ]);
        })->name('customer.home');

        // ==========================================
        // PROFILE
        // ==========================================
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'edit'])->name('edit');
            Route::patch('/', [ProfileController::class, 'update'])->name('update');
            Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        });

        // ==========================================
        // PRODUCTS - Admin Management (Admin Only, except show)
        // ==========================================
        Route::middleware(['role:admin'])->group(function () {
            Route::resource('products', ProductController::class)->except(['show']);
            Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
                ->name('products.toggle-status');
        });

        // Product show route - accessible by all authenticated users
        // Must be defined AFTER resource routes to avoid matching 'create' as product ID
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');

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
        // SUBSCRIPTION MANAGEMENT
        // ==========================================
        Route::prefix('subscription')->name('subscription.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SubscriptionController::class, 'index'])->name('index');
            Route::get('/plans', [\App\Http\Controllers\SubscriptionController::class, 'plans'])->name('plans');
            Route::post('/change', [\App\Http\Controllers\SubscriptionController::class, 'changePlan'])->name('change');
            Route::get('/invoices', [\App\Http\Controllers\SubscriptionController::class, 'invoices'])->name('invoices');
            Route::get('/invoices/{invoice}', [\App\Http\Controllers\SubscriptionController::class, 'showInvoice'])->name('invoice');
            Route::get('/expired', [\App\Http\Controllers\SubscriptionController::class, 'expired'])->name('expired');
        });

        // ==========================================
        // SETTINGS MANAGEMENT
        // ==========================================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SettingsController::class, 'index'])->name('index');

            // Theme settings
            Route::get('/theme', [\App\Http\Controllers\SettingsController::class, 'theme'])->name('theme');
            Route::post('/theme', [\App\Http\Controllers\SettingsController::class, 'updateTheme'])->name('theme.update');
            Route::post('/upload/logo', [\App\Http\Controllers\SettingsController::class, 'uploadLogo'])->name('upload.logo');
            Route::post('/upload/favicon', [\App\Http\Controllers\SettingsController::class, 'uploadFavicon'])->name('upload.favicon');
            Route::post('/upload/banner', [\App\Http\Controllers\SettingsController::class, 'uploadBanner'])->name('upload.banner');

            // Store settings
            Route::get('/store', [\App\Http\Controllers\SettingsController::class, 'store'])->name('store');
            Route::post('/store', [\App\Http\Controllers\SettingsController::class, 'updateStore'])->name('store.update');

            // Payment settings
            Route::get('/payment', [\App\Http\Controllers\SettingsController::class, 'payment'])->name('payment');
            Route::post('/payment', [\App\Http\Controllers\SettingsController::class, 'updatePayment'])->name('payment.update');

            // Shipping settings
            Route::get('/shipping', [\App\Http\Controllers\SettingsController::class, 'shipping'])->name('shipping');
            Route::post('/shipping', [\App\Http\Controllers\SettingsController::class, 'updateShipping'])->name('shipping.update');

            // Theme CSS/JSON API
            Route::get('/css', [\App\Http\Controllers\SettingsController::class, 'getCss'])->name('css');
            Route::get('/theme.json', [\App\Http\Controllers\SettingsController::class, 'getThemeJson'])->name('theme.json');
        });

        // ==========================================
        // ADMIN - ORDER MANAGEMENT (Admin Only)
        // ==========================================
        Route::middleware(['role:admin'])->group(function () {
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

            // ==========================================
            // ADMIN - ANALYTICS (Admin Only)
            // ==========================================
            Route::prefix('admin/analytics')->name('admin.analytics.')->group(function () {
                Route::get('/', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('index');
                Route::get('/stats', [\App\Http\Controllers\AnalyticsController::class, 'stats'])->name('stats');
                Route::get('/revenue-chart', [\App\Http\Controllers\AnalyticsController::class, 'revenueChart'])->name('revenue-chart');
                Route::get('/orders-chart', [\App\Http\Controllers\AnalyticsController::class, 'ordersChart'])->name('orders-chart');
                Route::get('/top-products', [\App\Http\Controllers\AnalyticsController::class, 'topProducts'])->name('top-products');
            });
        });

        // ==========================================
        // PAYMENT API (Midtrans)
        // ==========================================
        Route::prefix('api/payment')->name('api.payment.')->group(function () {
            Route::post('/snap-token', [\App\Http\Controllers\MidtransController::class, 'getSnapToken'])->name('snap-token');
            Route::get('/status/{orderNumber}', [\App\Http\Controllers\MidtransController::class, 'checkStatus'])->name('status');
        });

        // ==========================================
        // SHIPPING API (RajaOngkir)
        // ==========================================
        Route::prefix('api/shipping')->name('api.shipping.')->group(function () {
            Route::get('/provinces', [\App\Http\Controllers\ShippingController::class, 'provinces'])->name('provinces');
            Route::get('/cities/{provinceId?}', [\App\Http\Controllers\ShippingController::class, 'cities'])->name('cities');
            Route::get('/subdistricts/{cityId}', [\App\Http\Controllers\ShippingController::class, 'subdistricts'])->name('subdistricts');
            Route::get('/search-city', [\App\Http\Controllers\ShippingController::class, 'searchCity'])->name('search-city');
            Route::get('/couriers', [\App\Http\Controllers\ShippingController::class, 'couriers'])->name('couriers');
            Route::post('/cost', [\App\Http\Controllers\ShippingController::class, 'cost'])->name('cost');
            Route::post('/options', [\App\Http\Controllers\ShippingController::class, 'options'])->name('options');
            Route::post('/track', [\App\Http\Controllers\ShippingController::class, 'track'])->name('track');
        });

    });

    // ==========================================
    // PAYMENT WEBHOOKS (No Auth Required)
    // ==========================================
    Route::post('/webhook/midtrans', [\App\Http\Controllers\MidtransController::class, 'notification'])
        ->name('webhook.midtrans');

    Route::get('/payment/finish', [\App\Http\Controllers\MidtransController::class, 'finish'])
        ->name('payment.finish');
    Route::get('/payment/unfinish', [\App\Http\Controllers\MidtransController::class, 'unfinish'])
        ->name('payment.unfinish');
    Route::get('/payment/error', [\App\Http\Controllers\MidtransController::class, 'error'])
        ->name('payment.error');
});
