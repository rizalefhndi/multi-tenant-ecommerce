<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Display checkout page.
     */
    public function index(): Response|RedirectResponse
    {
        $user = auth()->user();

        // Get active cart with items
        $cart = Cart::with(['items.product'])
            ->where('user_id', $user->id)
            ->active()
            ->first();

        // Redirect jika cart kosong
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang belanja kosong');
        }

        // Validate stock availability
        $stockIssues = [];
        foreach ($cart->items as $item) {
            if (!$item->product) {
                $stockIssues[] = "Produk tidak ditemukan";
            } elseif (!$item->product->isAvailable()) {
                $stockIssues[] = "{$item->product->name} tidak tersedia";
            } elseif ($item->product->stock < $item->quantity) {
                $stockIssues[] = "Stok {$item->product->name} hanya tersisa {$item->product->stock}";
            }
        }

        // Get user addresses
        $addresses = UserAddress::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->get()
            ->map(fn($address) => [
                'id' => $address->id,
                'label' => $address->label,
                'label_icon' => $address->label_icon,
                'recipient_name' => $address->recipient_name,
                'phone' => $address->phone,
                'full_address' => $address->full_address,
                'city' => $address->city,
                'city_id' => $address->city_id,
                'is_default' => $address->is_default,
            ]);

        // Calculate totals
        $subtotal = $cart->total_price;
        $totalWeight = $cart->items->sum(fn($item) => ($item->product->weight ?? 0) * $item->quantity);

        return Inertia::render('Checkout/Index', [
            'cart' => [
                'id' => $cart->id,
                'total_items' => $cart->total_items,
                'subtotal' => $subtotal,
                'formatted_subtotal' => $cart->formatted_total,
            ],
            'cartItems' => $cart->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_image' => $item->product->image_url,
                'price' => $item->price,
                'formatted_price' => 'Rp ' . number_format($item->price, 0, ',', '.'),
                'quantity' => $item->quantity,
                'subtotal' => $item->price * $item->quantity,
                'formatted_subtotal' => 'Rp ' . number_format($item->price * $item->quantity, 0, ',', '.'),
                'weight' => $item->product->weight ?? 0,
            ]),
            'addresses' => $addresses,
            'hasAddress' => $addresses->isNotEmpty(),
            'defaultAddressId' => $addresses->firstWhere('is_default', true)?->id ?? $addresses->first()?->id,
            'totalWeight' => $totalWeight,
            'stockIssues' => $stockIssues,
            'paymentMethods' => $this->getPaymentMethods(),
        ]);
    }

    /**
     * Process checkout and create order.
     */
    public function process(CheckoutRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validated();

        // Get active cart
        $cart = Cart::with(['items.product'])
            ->where('user_id', $user->id)
            ->active()
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Keranjang belanja kosong');
        }

        // Final stock validation
        foreach ($cart->items as $item) {
            if (!$item->product || !$item->product->hasStock($item->quantity)) {
                $productName = $item->product ? $item->product->name : 'produk';
                return back()->with('error', "Stok {$productName} tidak mencukupi");
            }
        }

        try {
            DB::beginTransaction();

            $address = UserAddress::findOrFail($validated['address_id']);

            // Calculate totals
            $subtotal = $cart->total_price;
            $shippingCost = $validated['shipping_cost'] ?? 0;
            $discount = 0;
            $tax = 0;
            $total = $subtotal + $shippingCost - $discount + $tax;

            // Calculate total weight
            $totalWeight = $cart->items->sum(fn($item) => ($item->product->weight ?? 0) * $item->quantity);

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
                'shipping_courier' => $validated['shipping_courier'] ?? null,
                'shipping_service' => $validated['shipping_service'] ?? null,
                'shipping_weight' => $totalWeight,
                'shipping_address_snapshot' => $address->toSnapshot(),
                'payment_method' => $validated['payment_method'],
                'customer_notes' => $validated['customer_notes'] ?? null,
            ]);

            // Create Order Items & Decrease Stock
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_sku' => $cartItem->product->sku,
                    'product_image' => $cartItem->product->image,
                    'product_weight' => $cartItem->product->weight ?? 0,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->price * $cartItem->quantity,
                ]);

                // Decrease stock
                $cartItem->product->decreaseStock($cartItem->quantity);
            }

            // Create Transaction
            $transaction = Transaction::createPayment($order, $validated['payment_method'], [
                'payment_channel' => $this->getPaymentChannel($validated['payment_method']),
            ]);

            // Set bank info for bank transfer
            if ($validated['payment_method'] === 'bank_transfer') {
                $bankInfo = $this->getBankTransferInfo();
                $transaction->update([
                    'bank_name' => $bankInfo['bank_name'],
                    'account_number' => $bankInfo['account_number'],
                    'account_holder' => $bankInfo['account_holder'],
                ]);
            }

            // Clear cart
            $cart->clear();
            $cart->markAsCompleted();

            DB::commit();

            return redirect()
                ->route('checkout.success', $order->id)
                ->with('success', 'Pesanan berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Display checkout success page.
     */
    public function success(Order $order): Response|RedirectResponse
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items', 'transactions']);

        $latestTransaction = $order->getLatestTransaction();

        return Inertia::render('Checkout/Success', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'total' => $order->total,
                'formatted_total' => $order->formatted_total,
                'shipping_cost' => $order->shipping_cost,
                'formatted_shipping_cost' => $order->formatted_shipping_cost,
                'subtotal' => $order->subtotal,
                'formatted_subtotal' => $order->formatted_subtotal,
                'payment_method' => $order->payment_method,
                'shipping_address' => $order->shipping_address_snapshot,
                'shipping_info' => $order->shipping_info,
                'created_at' => $order->created_at->format('d M Y H:i'),
                'items_count' => $order->items->count(),
            ],
            'items' => $order->items->map(fn($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'product_image' => $item->image_url,
                'price' => $item->price,
                'formatted_price' => $item->formatted_price,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
                'formatted_subtotal' => $item->formatted_subtotal,
            ]),
            'transaction' => $latestTransaction ? [
                'id' => $latestTransaction->id,
                'transaction_id' => $latestTransaction->transaction_id,
                'status' => $latestTransaction->status,
                'status_label' => $latestTransaction->status_label,
                'amount' => $latestTransaction->amount,
                'formatted_amount' => $latestTransaction->formatted_amount,
                'payment_method' => $latestTransaction->payment_method,
                'payment_method_label' => $latestTransaction->payment_method_label,
                'expires_at' => $latestTransaction->expires_at?->format('d M Y H:i'),
                'remaining_time' => $latestTransaction->remaining_time,
                'bank_transfer_info' => $latestTransaction->getBankTransferInfo(),
            ] : null,
        ]);
    }

    /**
     * Get available payment methods.
     */
    private function getPaymentMethods(): array
    {
        return [
            [
                'id' => 'bank_transfer',
                'name' => 'Transfer Bank Manual',
                'description' => 'Transfer ke rekening toko, konfirmasi manual',
                'icon' => 'bank',
                'is_available' => true,
            ],
            [
                'id' => 'virtual_account',
                'name' => 'Virtual Account',
                'description' => 'Pembayaran otomatis via Virtual Account',
                'icon' => 'credit-card',
                'is_available' => false, // TODO: Enable after Midtrans integration
            ],
            [
                'id' => 'gopay',
                'name' => 'GoPay',
                'description' => 'Bayar dengan saldo GoPay',
                'icon' => 'wallet',
                'is_available' => false,
            ],
            [
                'id' => 'shopeepay',
                'name' => 'ShopeePay',
                'description' => 'Bayar dengan saldo ShopeePay',
                'icon' => 'wallet',
                'is_available' => false,
            ],
            [
                'id' => 'qris',
                'name' => 'QRIS',
                'description' => 'Scan QR untuk bayar dari e-wallet manapun',
                'icon' => 'qr-code',
                'is_available' => false,
            ],
            [
                'id' => 'cod',
                'name' => 'Bayar di Tempat (COD)',
                'description' => 'Bayar saat barang diterima',
                'icon' => 'cash',
                'is_available' => false, // TODO: Enable based on store setting
            ],
        ];
    }

    /**
     * Get payment channel based on method.
     */
    private function getPaymentChannel(string $method): ?string
    {
        return match ($method) {
            'bank_transfer' => 'manual',
            'virtual_account' => 'midtrans_va',
            'gopay' => 'gopay',
            'shopeepay' => 'shopeepay',
            'qris' => 'qris',
            'cod' => 'cod',
            default => null,
        };
    }

    /**
     * Get bank transfer info (configurable per tenant).
     */
    private function getBankTransferInfo(): array
    {
        // TODO: Get from tenant settings
        return [
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Nama Toko',
        ];
    }
}
