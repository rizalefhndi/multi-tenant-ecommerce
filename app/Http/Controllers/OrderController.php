<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of user orders.
     */
    public function index(Request $request): Response
    {
        $query = Order::with(['items'])
            ->where('user_id', auth()->id())
            ->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(10)->through(fn($order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'status_color' => $order->status_color,
            'total' => $order->total,
            'formatted_total' => $order->formatted_total,
            'total_items' => $order->total_items,
            'payment_method' => $order->payment_method,
            'shipping_info' => $order->shipping_info,
            'created_at' => $order->created_at->format('d M Y H:i'),
            'first_item' => $order->items->first() ? [
                'product_name' => $order->items->first()->product_name,
                'product_image' => $order->items->first()->image_url,
            ] : null,
        ]);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'statuses' => Order::getStatuses(),
            'currentStatus' => $request->status ?? 'all',
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): Response
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items', 'transactions', 'address']);

        $latestTransaction = $order->getPendingTransaction() ?? $order->getLatestTransaction();

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'status_color' => $order->status_color,
                'subtotal' => $order->subtotal,
                'formatted_subtotal' => $order->formatted_subtotal,
                'shipping_cost' => $order->shipping_cost,
                'formatted_shipping_cost' => $order->formatted_shipping_cost,
                'discount' => $order->discount,
                'tax' => $order->tax,
                'total' => $order->total,
                'formatted_total' => $order->formatted_total,
                'payment_method' => $order->payment_method,
                'shipping_courier' => $order->shipping_courier,
                'shipping_service' => $order->shipping_service,
                'shipping_tracking_number' => $order->shipping_tracking_number,
                'shipping_info' => $order->shipping_info,
                'shipping_address' => $order->shipping_address_snapshot,
                'customer_notes' => $order->customer_notes,
                'admin_notes' => $order->admin_notes,
                'is_paid' => $order->isPaid(),
                'can_cancel' => $order->canBeCancelled(),
                'paid_at' => $order->paid_at?->format('d M Y H:i'),
                'created_at' => $order->created_at->format('d M Y H:i'),
                'updated_at' => $order->updated_at->format('d M Y H:i'),
            ],
            'items' => $order->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_sku' => $item->product_sku,
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
                'status_color' => $latestTransaction->status_color,
                'amount' => $latestTransaction->amount,
                'formatted_amount' => $latestTransaction->formatted_amount,
                'payment_method' => $latestTransaction->payment_method,
                'payment_method_label' => $latestTransaction->payment_method_label,
                'expires_at' => $latestTransaction->expires_at?->format('d M Y H:i'),
                'remaining_time' => $latestTransaction->remaining_time,
                'is_expired' => $latestTransaction->is_expired,
                'has_proof' => $latestTransaction->transfer_proof !== null,
                'transfer_proof_url' => $latestTransaction->transfer_proof_url,
                'bank_transfer_info' => $latestTransaction->getBankTransferInfo(),
            ] : null,
            'statusTimeline' => $this->getStatusTimeline($order),
        ]);
    }

    /**
     * Upload transfer proof for bank transfer payment.
     */
    public function uploadProof(Request $request, Order $order): RedirectResponse
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Validate
        $request->validate([
            'transfer_proof' => ['required', 'image', 'max:2048'], // max 2MB
        ], [
            'transfer_proof.required' => 'Pilih file bukti transfer',
            'transfer_proof.image' => 'File harus berupa gambar',
            'transfer_proof.max' => 'Ukuran file maksimal 2MB',
        ]);

        // Get pending transaction
        $transaction = $order->getPendingTransaction();

        if (!$transaction) {
            return back()->with('error', 'Tidak ada transaksi yang menunggu pembayaran');
        }

        if ($transaction->payment_method !== Transaction::METHOD_BANK_TRANSFER) {
            return back()->with('error', 'Upload bukti transfer hanya untuk pembayaran transfer bank');
        }

        // Delete old proof if exists
        if ($transaction->transfer_proof) {
            Storage::disk('public')->delete($transaction->transfer_proof);
        }

        // Store new proof
        $path = $request->file('transfer_proof')->store('transfer-proofs', 'public');

        $transaction->update([
            'transfer_proof' => $path,
        ]);

        return back()->with('success', 'Bukti transfer berhasil diupload. Menunggu verifikasi admin.');
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order): RedirectResponse
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan');
        }

        $order->cancel();

        return back()->with('success', 'Pesanan berhasil dibatalkan');
    }

    /**
     * Reorder - add items back to cart.
     */
    public function reorder(Order $order): RedirectResponse
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $cart = auth()->user()->getOrCreateCart();
        $addedCount = 0;
        $unavailableItems = [];

        foreach ($order->items as $item) {
            if ($item->product && $item->product->isAvailable()) {
                $cart->addProduct($item->product, $item->quantity);
                $addedCount++;
            } else {
                $unavailableItems[] = $item->product_name;
            }
        }

        if ($addedCount === 0) {
            return back()->with('error', 'Semua produk tidak tersedia');
        }

        $message = "{$addedCount} produk ditambahkan ke keranjang";
        if (!empty($unavailableItems)) {
            $message .= '. Produk tidak tersedia: ' . implode(', ', $unavailableItems);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }

    /**
     * Track order shipment.
     */
    public function track(Order $order): Response
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // TODO: Integrate with courier API for tracking

        return Inertia::render('Orders/Track', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'shipping_courier' => $order->shipping_courier,
                'shipping_service' => $order->shipping_service,
                'shipping_tracking_number' => $order->shipping_tracking_number,
            ],
            'tracking' => null, // TODO: Real tracking data
        ]);
    }

    /**
     * Confirm order received (mark as delivered).
     */
    public function confirmReceived(Order $order): RedirectResponse
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== Order::STATUS_SHIPPED) {
            return back()->with('error', 'Pesanan belum dikirim');
        }

        $order->markAsDelivered();

        return back()->with('success', 'Pesanan dikonfirmasi telah diterima. Terima kasih!');
    }

    /**
     * Get status timeline for order.
     */
    private function getStatusTimeline(Order $order): array
    {
        $statuses = [
            Order::STATUS_PENDING_PAYMENT => [
                'label' => 'Menunggu Pembayaran',
                'icon' => 'clock',
            ],
            Order::STATUS_PAYMENT_RECEIVED => [
                'label' => 'Pembayaran Diterima',
                'icon' => 'check-circle',
            ],
            Order::STATUS_PROCESSING => [
                'label' => 'Sedang Diproses',
                'icon' => 'package',
            ],
            Order::STATUS_SHIPPED => [
                'label' => 'Dikirim',
                'icon' => 'truck',
            ],
            Order::STATUS_DELIVERED => [
                'label' => 'Selesai',
                'icon' => 'check-double',
            ],
        ];

        $timeline = [];
        $currentStatusReached = false;

        foreach ($statuses as $status => $info) {
            $isCompleted = false;
            $isCurrent = false;

            if ($order->status === $status) {
                $isCurrent = true;
                $currentStatusReached = true;
            } elseif (!$currentStatusReached) {
                $isCompleted = true;
            }

            // Skip if order is cancelled or refunded
            if (in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED])) {
                if ($status === $order->status) {
                    $isCurrent = true;
                }
            }

            $timeline[] = [
                'status' => $status,
                'label' => $info['label'],
                'icon' => $info['icon'],
                'is_completed' => $isCompleted,
                'is_current' => $isCurrent,
            ];
        }

        return $timeline;
    }
}
