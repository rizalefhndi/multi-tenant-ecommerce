# 📋 Implementation Plan - Multi-Tenant E-Commerce SaaS

**Tanggal Dibuat**: 26 Desember 2024  
**Status**: Draft  
**Versi**: 1.0

---

## 📊 Executive Summary

Proyek ini saat ini berada di tahap **MVP (Minimum Viable Product) awal**. Fondasi multi-tenant dengan `stancl/tenancy` sudah solid, termasuk:
- ✅ Tenant management (CRUD)
- ✅ Domain-based tenancy
- ✅ Tenant database isolation
- ✅ Product management (tenant-scoped)
- ✅ Cart & CartItem system

**Kekurangan utama** yang perlu diimplementasi untuk menjadi e-commerce yang berfungsi penuh:
1. Order & Checkout System
2. Payment Gateway Integration  v

### Timeline: 2-3 Minggu

Tanpa fitur ini, aplikasi TIDAK bisa disebut e-commerce karena user tidak bisa melakukan pemesanan.

---

### 1.1 📦 Database Schema - Orders & Transactions

#### 1.1.1 Migration: User Addresses (Tenant)

**File**: `database/migrations/tenant/2025_12_26_000001_create_user_addresses_table.php`

```php
Schema::create('user_addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('label')->default('Home'); // Home, Office, etc
    $table->string('recipient_name');
    $table->string('phone', 20);
    $table->text('address_line_1');
    $table->text('address_line_2')->nullable();
    $table->string('city');
    $table->string('province');
    $table->string('postal_code', 10);
    $table->string('country')->default('Indonesia');
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->boolean('is_default')->default(false);
    $table->timestamps();
    
    $table->index(['user_id', 'is_default']);
});
```

#### 1.1.2 Migration: Orders (Tenant)

**File**: `database/migrations/tenant/2025_12_26_000002_create_orders_table.php`

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique(); // ORD-20251226-XXXXX
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('address_id')->nullable()->constrained('user_addresses')->nullOnDelete();
    
    // Order Status
    $table->enum('status', [
        'pending_payment',    // Menunggu pembayaran
        'payment_received',   // Pembayaran diterima
        'processing',         // Sedang diproses
        'shipped',            // Dikirim
        'delivered',          // Selesai
        'cancelled',          // Dibatalkan
        'refunded'            // Dikembalikan
    ])->default('pending_payment');
    
    // Pricing
    $table->decimal('subtotal', 15, 2);
    $table->decimal('shipping_cost', 15, 2)->default(0);
    $table->decimal('discount', 15, 2)->default(0);
    $table->decimal('tax', 15, 2)->default(0);
    $table->decimal('total', 15, 2);
    
    // Shipping Info (snapshot)
    $table->string('shipping_courier')->nullable(); // JNE, J&T, etc
    $table->string('shipping_service')->nullable(); // REG, YES, etc
    $table->string('shipping_tracking_number')->nullable();
    $table->json('shipping_address_snapshot')->nullable(); // Snapshot alamat saat order
    
    // Payment Info
    $table->string('payment_method')->nullable(); // bank_transfer, midtrans, cod
    $table->timestamp('paid_at')->nullable();
    
    // Notes
    $table->text('customer_notes')->nullable();
    $table->text('admin_notes')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['user_id', 'status']);
    $table->index('order_number');
    $table->index('created_at');
});
```

#### 1.1.3 Migration: Order Items (Tenant)

**File**: `database/migrations/tenant/2025_12_26_000003_create_order_items_table.php`

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
    
    // Product Snapshot (karena product bisa berubah/dihapus)
    $table->string('product_name');
    $table->string('product_image')->nullable();
    
    $table->decimal('price', 15, 2);
    $table->integer('quantity');
    $table->decimal('subtotal', 15, 2);
    
    $table->timestamps();
    
    $table->index('order_id');
});
```

#### 1.1.4 Migration: Transactions/Payments (Tenant)

**File**: `database/migrations/tenant/2025_12_26_000004_create_transactions_table.php`

```php
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->string('transaction_id')->unique(); // TRX-xxxxx atau dari payment gateway
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    
    $table->enum('type', ['payment', 'refund']);
    $table->enum('status', [
        'pending',
        'success',
        'failed',
        'expired',
        'cancelled'
    ])->default('pending');
    
    $table->decimal('amount', 15, 2);
    $table->string('payment_method');
    $table->string('payment_channel')->nullable(); // BCA, Mandiri, GoPay, etc
    
    // Gateway Response Storage
    $table->json('gateway_response')->nullable();
    $table->string('gateway_transaction_id')->nullable();
    
    // Bank Transfer Info (untuk manual)
    $table->string('bank_name')->nullable();
    $table->string('account_number')->nullable();
    $table->string('account_holder')->nullable();
    $table->string('transfer_proof')->nullable(); // Upload bukti transfer
    
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    
    $table->timestamps();
    
    $table->index(['order_id', 'status']);
});
```

#### 1.1.5 Migration: Add Weight to Products (Tenant)

**File**: `database/migrations/tenant/2025_12_26_000005_add_weight_to_products_table.php`

```php
Schema::table('products', function (Blueprint $table) {
    $table->integer('weight')->default(0)->after('stock'); // dalam gram
    $table->string('sku')->nullable()->after('id');
    
    $table->index('sku');
});
```

---

### 1.2 🏗️ Models

#### 1.2.1 UserAddress Model

**File**: `app/Models/UserAddress.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);
        
        return implode(', ', $parts);
    }

    // Set address sebagai default, unset yang lain
    public function setAsDefault(): void
    {
        $this->user->addresses()->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }
}
```

#### 1.2.2 Order Model

**File**: `app/Models/Order.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'status',
        'subtotal',
        'shipping_cost',
        'discount',
        'tax',
        'total',
        'shipping_courier',
        'shipping_service',
        'shipping_tracking_number',
        'shipping_address_snapshot',
        'payment_method',
        'paid_at',
        'customer_notes',
        'admin_notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address_snapshot' => 'array',
        'paid_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_PAYMENT_RECEIVED = 'payment_received';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::STATUS_PAYMENT_RECEIVED => 'Pembayaran Diterima',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_SHIPPED => 'Dikirim',
            self::STATUS_DELIVERED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_REFUNDED => 'Dikembalikan',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Generate Order Number
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -5));
        return "ORD-{$date}-{$random}";
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING_PAYMENT);
    }

    public function scopePaid($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_CANCELLED,
        ]);
    }

    // Helpers
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_PAYMENT,
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_PAYMENT_RECEIVED,
            'paid_at' => now(),
        ]);
    }
}
```

#### 1.2.3 OrderItem Model

**File**: `app/Models/OrderItem.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_image',
        'price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }
}
```

#### 1.2.4 Transaction Model

**File**: `app/Models/Transaction.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'order_id',
        'type',
        'status',
        'amount',
        'payment_method',
        'payment_channel',
        'gateway_response',
        'gateway_transaction_id',
        'bank_name',
        'account_number',
        'account_holder',
        'transfer_proof',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function generateTransactionId(): string
    {
        return 'TRX-' . strtoupper(uniqid());
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function markAsSuccess(): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESS,
            'paid_at' => now(),
        ]);
    }
}
```

---

### 1.3 🎮 Controllers

#### 1.3.1 CheckoutController

**File**: `app/Http/Controllers/CheckoutController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Show checkout page
     */
    public function index(): Response
    {
        $user = auth()->user();
        $cart = Cart::with(['items.product'])
            ->where('user_id', $user->id)
            ->active()
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja kosong');
        }

        $addresses = UserAddress::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->get();

        return Inertia::render('Checkout/Index', [
            'cart' => $cart,
            'cartItems' => $cart->items,
            'addresses' => $addresses,
            'paymentMethods' => $this->getPaymentMethods(),
        ]);
    }

    /**
     * Process checkout
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'payment_method' => 'required|string',
            'customer_notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $cart = Cart::with(['items.product'])
            ->where('user_id', $user->id)
            ->active()
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Keranjang belanja kosong');
        }

        // Validate stock availability
        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                return back()->with('error', 
                    "Stok {$item->product->name} tidak mencukupi"
                );
            }
        }

        try {
            DB::beginTransaction();

            $address = UserAddress::findOrFail($validated['address_id']);

            // Calculate totals
            $subtotal = $cart->total_price;
            $shippingCost = 0; // TODO: Integrate shipping API
            $discount = 0;
            $tax = 0;
            $total = $subtotal + $shippingCost - $discount + $tax;

            // Create Order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'shipping_address_snapshot' => [
                    'recipient_name' => $address->recipient_name,
                    'phone' => $address->phone,
                    'address' => $address->full_address,
                ],
                'payment_method' => $validated['payment_method'],
                'customer_notes' => $validated['customer_notes'],
            ]);

            // Create Order Items & Decrease Stock
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_image' => $cartItem->product->image,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->price * $cartItem->quantity,
                ]);

                // Decrease stock
                $cartItem->product->decreaseStock($cartItem->quantity);
            }

            // Create Transaction
            $transaction = Transaction::create([
                'transaction_id' => Transaction::generateTransactionId(),
                'order_id' => $order->id,
                'type' => Transaction::TYPE_PAYMENT,
                'status' => Transaction::STATUS_PENDING,
                'amount' => $total,
                'payment_method' => $validated['payment_method'],
                'expires_at' => now()->addHours(24),
            ]);

            // Clear cart
            $cart->clear();
            $cart->markAsCompleted();

            DB::commit();

            return redirect()->route('checkout.success', $order->id)
                ->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Checkout success page
     */
    public function success(Order $order): Response
    {
        $order->load(['items', 'transactions']);

        return Inertia::render('Checkout/Success', [
            'order' => $order,
        ]);
    }

    /**
     * Available payment methods
     */
    private function getPaymentMethods(): array
    {
        return [
            [
                'id' => 'bank_transfer',
                'name' => 'Transfer Bank Manual',
                'description' => 'Transfer ke rekening toko',
                'banks' => [
                    ['name' => 'BCA', 'account' => '1234567890', 'holder' => 'Nama Toko'],
                    ['name' => 'Mandiri', 'account' => '0987654321', 'holder' => 'Nama Toko'],
                ],
            ],
            // TODO: Add Midtrans payment methods
        ];
    }
}
```

#### 1.3.2 OrderController

**File**: `app/Http/Controllers/OrderController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * List user's orders
     */
    public function index(): Response
    {
        $orders = Order::with(['items'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show order detail
     */
    public function show(Order $order): Response
    {
        // Ensure user can only see their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items', 'transactions', 'address']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Upload transfer proof (for manual bank transfer)
     */
    public function uploadProof(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'transfer_proof' => 'required|image|max:2048',
        ]);

        $path = $request->file('transfer_proof')
            ->store('transfer-proofs', 'public');

        // Update latest pending transaction
        $transaction = $order->transactions()
            ->where('status', Transaction::STATUS_PENDING)
            ->latest()
            ->first();

        if ($transaction) {
            $transaction->update([
                'transfer_proof' => $path,
            ]);
        }

        return back()->with('success', 'Bukti transfer berhasil diupload');
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan');
        }

        // Restore stock
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        // Cancel pending transactions
        $order->transactions()
            ->where('status', Transaction::STATUS_PENDING)
            ->update(['status' => Transaction::STATUS_CANCELLED]);

        return back()->with('success', 'Pesanan berhasil dibatalkan');
    }
}
```

#### 1.3.3 UserAddressController

**File**: `app/Http/Controllers/UserAddressController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserAddressController extends Controller
{
    public function index(): Response
    {
        $addresses = UserAddress::where('user_id', auth()->id())
            ->orderBy('is_default', 'desc')
            ->get();

        return Inertia::render('Addresses/Index', [
            'addresses' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
        ]);

        $validated['user_id'] = auth()->id();

        // If first address, set as default
        if (!UserAddress::where('user_id', auth()->id())->exists()) {
            $validated['is_default'] = true;
        }

        UserAddress::create($validated);

        return back()->with('success', 'Alamat berhasil ditambahkan');
    }

    public function update(Request $request, UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
        ]);

        $address->update($validated);

        return back()->with('success', 'Alamat berhasil diperbarui');
    }

    public function destroy(UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Alamat berhasil dihapus');
    }

    public function setDefault(UserAddress $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->setAsDefault();

        return back()->with('success', 'Alamat default berhasil diubah');
    }
}
```

---

### 1.4 🛣️ Routes Update

**Update**: `routes/tenant.php`

```php
// Checkout routes
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
    Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
});

// Order routes
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    Route::post('/{order}/upload-proof', [OrderController::class, 'uploadProof'])->name('upload-proof');
    Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
});

// Address routes
Route::prefix('addresses')->name('addresses.')->group(function () {
    Route::get('/', [UserAddressController::class, 'index'])->name('index');
    Route::post('/', [UserAddressController::class, 'store'])->name('store');
    Route::put('/{address}', [UserAddressController::class, 'update'])->name('update');
    Route::delete('/{address}', [UserAddressController::class, 'destroy'])->name('destroy');
    Route::post('/{address}/set-default', [UserAddressController::class, 'setDefault'])->name('set-default');
});
```

---

### 1.5 🎨 Frontend Components

#### Vue Pages to Create:

| Page | Path | Description |
|------|------|-------------|
| Checkout/Index.vue | `/checkout` | Form checkout dengan pilihan alamat & pembayaran |
| Checkout/Success.vue | `/checkout/success/{id}` | Konfirmasi order berhasil |
| Orders/Index.vue | `/orders` | Daftar pesanan user |
| Orders/Show.vue | `/orders/{id}` | Detail pesanan |
| Addresses/Index.vue | `/addresses` | Kelola alamat pengiriman |

---

## 🎯 Phase 2: SaaS Business Model (Prioritas HIGH)

### Timeline: 1-2 Minggu

Fitur ini memungkinkan Landlord menghasilkan uang dari platform.

---

### 2.1 📦 Database Schema - Subscription

#### 2.1.1 Migration: Plans Table (Central/Landlord)

**File**: `database/migrations/2025_12_26_100001_create_plans_table.php`

```php
Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Basic, Pro, Enterprise
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    
    // Pricing
    $table->decimal('price_monthly', 10, 2)->default(0);
    $table->decimal('price_yearly', 10, 2)->default(0);
    
    // Limits
    $table->integer('max_products')->default(10);
    $table->integer('max_orders_per_month')->default(50);
    $table->integer('max_storage_mb')->default(100);
    $table->integer('max_domains')->default(1);
    
    // Features (JSON untuk flexibility)
    $table->json('features')->nullable();
    /*
    {
        "analytics": false,
        "custom_domain": false,
        "remove_branding": false,
        "priority_support": false,
        "api_access": false
    }
    */
    
    $table->boolean('is_active')->default(true);
    $table->boolean('is_featured')->default(false);
    $table->integer('sort_order')->default(0);
    
    $table->timestamps();
});
```

#### 2.1.2 Migration: Update Tenants Table

**File**: `database/migrations/2025_12_26_100002_add_subscription_to_tenants_table.php`

```php
Schema::table('tenants', function (Blueprint $table) {
    $table->foreignId('plan_id')->nullable()->after('id');
    $table->enum('subscription_status', [
        'active',
        'trial',
        'cancelled',
        'expired',
        'suspended'
    ])->default('trial')->after('plan_id');
    $table->date('trial_ends_at')->nullable();
    $table->date('subscription_ends_at')->nullable();
    $table->integer('current_products_count')->default(0);
    $table->integer('current_orders_count')->default(0);
    $table->integer('current_storage_mb')->default(0);
    
    $table->index(['plan_id', 'subscription_status']);
});
```

#### 2.1.3 Migration: Tenant Invoices (Central)

**File**: `database/migrations/2025_12_26_100003_create_tenant_invoices_table.php`

```php
Schema::create('tenant_invoices', function (Blueprint $table) {
    $table->id();
    $table->string('invoice_number')->unique();
    $table->string('tenant_id');
    $table->foreignId('plan_id');
    
    $table->enum('billing_period', ['monthly', 'yearly']);
    $table->date('period_start');
    $table->date('period_end');
    
    $table->decimal('amount', 10, 2);
    $table->decimal('tax', 10, 2)->default(0);
    $table->decimal('total', 10, 2);
    
    $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled']);
    $table->date('due_date');
    $table->timestamp('paid_at')->nullable();
    
    $table->string('payment_method')->nullable();
    $table->json('payment_details')->nullable();
    
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->index(['tenant_id', 'status']);
});
```

---

### 2.2 🔒 Tenant Quota Middleware

**File**: `app/Http/Middleware/CheckTenantQuota.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTenantQuota
{
    public function handle(Request $request, Closure $next, string $type)
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return $next($request);
        }

        $plan = $tenant->plan;
        
        if (!$plan) {
            // Default limits jika belum ada plan
            $plan = (object) [
                'max_products' => 10,
                'max_orders_per_month' => 50,
                'max_storage_mb' => 100,
            ];
        }

        switch ($type) {
            case 'product':
                if ($tenant->current_products_count >= $plan->max_products) {
                    return back()->with('error', 
                        "Batas produk tercapai ({$plan->max_products}). Upgrade plan untuk menambah lebih banyak."
                    );
                }
                break;
                
            case 'order':
                if ($tenant->current_orders_count >= $plan->max_orders_per_month) {
                    return back()->with('error', 
                        "Batas order bulanan tercapai ({$plan->max_orders_per_month}). Upgrade plan Anda."
                    );
                }
                break;
        }

        return $next($request);
    }
}
```

---

### 2.3 📊 Seed Plans

**File**: `database/seeders/PlanSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Cocok untuk pemula yang ingin mencoba platform',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_products' => 10,
                'max_orders_per_month' => 20,
                'max_storage_mb' => 50,
                'max_domains' => 1,
                'features' => [
                    'analytics' => false,
                    'custom_domain' => false,
                    'remove_branding' => false,
                    'priority_support' => false,
                    'api_access' => false,
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Untuk bisnis kecil yang sedang berkembang',
                'price_monthly' => 99000,
                'price_yearly' => 999000,
                'max_products' => 100,
                'max_orders_per_month' => 200,
                'max_storage_mb' => 500,
                'max_domains' => 1,
                'features' => [
                    'analytics' => true,
                    'custom_domain' => false,
                    'remove_branding' => false,
                    'priority_support' => false,
                    'api_access' => false,
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Untuk bisnis yang serius dengan fitur lengkap',
                'price_monthly' => 249000,
                'price_yearly' => 2499000,
                'max_products' => 1000,
                'max_orders_per_month' => 1000,
                'max_storage_mb' => 2000,
                'max_domains' => 3,
                'features' => [
                    'analytics' => true,
                    'custom_domain' => true,
                    'remove_branding' => true,
                    'priority_support' => false,
                    'api_access' => true,
                ],
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Solusi enterprise dengan dukungan prioritas',
                'price_monthly' => 999000,
                'price_yearly' => 9999000,
                'max_products' => -1, // Unlimited
                'max_orders_per_month' => -1,
                'max_storage_mb' => 10000,
                'max_domains' => 10,
                'features' => [
                    'analytics' => true,
                    'custom_domain' => true,
                    'remove_branding' => true,
                    'priority_support' => true,
                    'api_access' => true,
                ],
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            $plan['features'] = json_encode($plan['features']);
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
```

---

## 🎯 Phase 3: Payment & Shipping Integration (Prioritas MEDIUM)

### Timeline: 2-3 Minggu

---

### 3.1 💳 Midtrans Integration

#### 3.1.1 Install Package

```bash
composer require midtrans/midtrans-php
```

#### 3.1.2 Config

**File**: `config/midtrans.php`

```php
<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => true,
    'is_3ds' => true,
];
```

#### 3.1.3 MidtransService

**File**: `app/Services/MidtransService.php`

```php
<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createSnapToken(Order $order): string
    {
        $items = $order->items->map(fn($item) => [
            'id' => $item->product_id,
            'name' => $item->product_name,
            'price' => (int) $item->price,
            'quantity' => $item->quantity,
        ])->toArray();

        // Add shipping if exists
        if ($order->shipping_cost > 0) {
            $items[] = [
                'id' => 'SHIPPING',
                'name' => 'Biaya Pengiriman',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->shipping_address_snapshot['phone'] ?? '',
                'shipping_address' => [
                    'address' => $order->shipping_address_snapshot['address'] ?? '',
                ],
            ],
        ];

        return Snap::getSnapToken($params);
    }
}
```

---

### 3.2 🚚 Raja Ongkir Integration

#### 3.2.1 RajaOngkirService

**File**: `app/Services/RajaOngkirService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RajaOngkirService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter');
        $this->apiKey = config('services.rajaongkir.api_key');
    }

    /**
     * Get all provinces
     */
    public function getProvinces(): array
    {
        return Cache::remember('rajaongkir_provinces', 86400, function () {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/province");

            return $response->json('rajaongkir.results', []);
        });
    }

    /**
     * Get cities by province
     */
    public function getCities(int $provinceId): array
    {
        return Cache::remember("rajaongkir_cities_{$provinceId}", 86400, function () use ($provinceId) {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/city", [
                'province' => $provinceId,
            ]);

            return $response->json('rajaongkir.results', []);
        });
    }

    /**
     * Calculate shipping cost
     */
    public function calculateCost(int $originCity, int $destinationCity, int $weight, string $courier): array
    {
        $response = Http::withHeaders([
            'key' => $this->apiKey,
        ])->post("{$this->baseUrl}/cost", [
            'origin' => $originCity,
            'destination' => $destinationCity,
            'weight' => $weight,
            'courier' => $courier, // jne, tiki, pos
        ]);

        return $response->json('rajaongkir.results', []);
    }
}
```

---

## 🎯 Phase 4: Tenant Customization (Prioritas LOW)

### Timeline: 1-2 Minggu

---

### 4.1 📦 Database Schema

#### 4.1.1 Migration: Tenant Settings (Tenant)

**File**: `database/migrations/tenant/2025_12_26_200001_create_tenant_settings_table.php`

```php
Schema::create('tenant_settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->timestamps();
});
```

#### 4.1.2 Settings Keys

| Key | Type | Description |
|-----|------|-------------|
| `store_name` | string | Nama toko |
| `store_description` | text | Deskripsi toko |
| `store_logo` | string | Path ke logo |
| `store_banner` | string | Path ke banner |
| `primary_color` | string | Warna utama (#hex) |
| `secondary_color` | string | Warna sekunder (#hex) |
| `accent_color` | string | Warna aksen (#hex) |
| `font_family` | string | Font keluarga |
| `social_instagram` | string | Link Instagram |
| `social_facebook` | string | Link Facebook |
| `social_whatsapp` | string | Nomor WhatsApp |
| `shipping_origin_city_id` | int | ID kota asal pengiriman |

---

### 4.2 🎨 Dynamic Theme System

**File**: `app/Services/TenantThemeService.php`

```php
<?php

namespace App\Services;

use App\Models\TenantSetting;

class TenantThemeService
{
    public function getTheme(): array
    {
        return [
            'store_name' => $this->get('store_name', tenant()->name ?? 'Store'),
            'store_logo' => $this->get('store_logo'),
            'store_banner' => $this->get('store_banner'),
            'colors' => [
                'primary' => $this->get('primary_color', '#3B82F6'),
                'secondary' => $this->get('secondary_color', '#1E40AF'),
                'accent' => $this->get('accent_color', '#F59E0B'),
            ],
            'font_family' => $this->get('font_family', 'Inter'),
            'socials' => [
                'instagram' => $this->get('social_instagram'),
                'facebook' => $this->get('social_facebook'),
                'whatsapp' => $this->get('social_whatsapp'),
            ],
        ];
    }

    protected function get(string $key, $default = null)
    {
        $setting = TenantSetting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
```

---

## 📋 Implementation Checklist

### Phase 1: Core E-Commerce ⬜
- [ ] Create user_addresses migration
- [ ] Create orders migration
- [ ] Create order_items migration
- [ ] Create transactions migration
- [ ] Add weight to products
- [ ] Create UserAddress model
- [ ] Create Order model
- [ ] Create OrderItem model
- [ ] Create Transaction model
- [ ] Update User model relationships
- [ ] Create CheckoutController
- [ ] Create OrderController
- [ ] Create UserAddressController
- [ ] Update tenant routes
- [ ] Create Checkout/Index.vue
- [ ] Create Checkout/Success.vue
- [ ] Create Orders/Index.vue
- [ ] Create Orders/Show.vue
- [ ] Create Addresses/Index.vue
- [ ] Test checkout flow end-to-end

### Phase 2: SaaS Business ⬜
- [ ] Create plans migration
- [ ] Update tenants migration with subscription fields
- [ ] Create tenant_invoices migration
- [ ] Create Plan model
- [ ] Create TenantInvoice model
- [ ] Update Tenant model
- [ ] Create CheckTenantQuota middleware
- [ ] Create PlanSeeder
- [ ] Apply middleware to ProductController@store
- [ ] Create Landlord Pricing page
- [ ] Create Tenant subscription management page

### Phase 3: Payment & Shipping ⬜
- [ ] Install midtrans/midtrans-php
- [ ] Configure Midtrans
- [ ] Create MidtransService
- [ ] Create payment callback handler
- [ ] Configure RajaOngkir
- [ ] Create RajaOngkirService
- [ ] Create ShippingController
- [ ] Integrate shipping calculation in checkout

### Phase 4: Tenant Customization ⬜
- [ ] Create tenant_settings migration
- [ ] Create TenantSetting model
- [ ] Create TenantThemeService
- [ ] Create SettingsController
- [ ] Create Settings/Theme.vue page
- [ ] Implement dynamic CSS injection
- [ ] Add logo/banner upload functionality

---

## 🚀 Quick Start Commands

```bash
# Phase 1 - Setelah membuat migrations
php artisan migrate --path=database/migrations/tenant --database=tenant
php artisan tenants:migrate

# Run seeder untuk plans
php artisan db:seed --class=PlanSeeder

# Install payment gateway
composer require midtrans/midtrans-php

# Clear cache setelah update config
php artisan config:clear
php artisan cache:clear
```

---

## 📝 Notes

1. **Testing**: Setiap phase harus dilengkapi dengan unit test & feature test
2. **Security**: Selalu validasi ownership (user hanya bisa akses data miliknya sendiri)
3. **Performance**: Gunakan eager loading untuk menghindari N+1 query
4. **UX**: Tampilkan loading state dan error handling yang baik di frontend

---

*Document created: 26 December 2024*  
*Last updated: 26 December 2024*
