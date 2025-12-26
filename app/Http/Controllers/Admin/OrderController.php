<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of all orders (admin view).
     */
    public function index(Request $request): Response
    {
        $query = Order::with(['user', 'items'])
            ->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(15)->through(fn($order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->user->name ?? 'Unknown',
            'customer_email' => $order->user->email ?? '',
            'status' => $order->status,
            'status_label' => $order->status_label,
            'status_color' => $order->status_color,
            'total' => $order->total,
            'formatted_total' => $order->formatted_total,
            'total_items' => $order->total_items,
            'payment_method' => $order->payment_method,
            'is_paid' => $order->isPaid(),
            'created_at' => $order->created_at->format('d M Y H:i'),
        ]);

        // Order statistics
        $stats = [
            'pending_payment' => Order::pending()->count(),
            'needs_processing' => Order::needsProcessing()->count(),
            'needs_shipping' => Order::needsShipping()->count(),
            'shipped' => Order::shipped()->count(),
            'total_today' => Order::whereDate('created_at', today())->count(),
            'revenue_today' => Order::paid()->whereDate('created_at', today())->sum('total'),
        ];

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'statuses' => Order::getStatuses(),
            'currentStatus' => $request->status ?? 'all',
            'search' => $request->search ?? '',
            'stats' => $stats,
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): Response
    {
        $order->load(['user', 'items', 'transactions', 'address']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ] : null,
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
                'shipping_weight' => $order->shipping_weight,
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
                'product_weight' => $item->product_weight,
                'price' => $item->price,
                'formatted_price' => $item->formatted_price,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
                'formatted_subtotal' => $item->formatted_subtotal,
            ]),
            'transactions' => $order->transactions->map(fn($trx) => [
                'id' => $trx->id,
                'transaction_id' => $trx->transaction_id,
                'type' => $trx->type,
                'status' => $trx->status,
                'status_label' => $trx->status_label,
                'status_color' => $trx->status_color,
                'amount' => $trx->amount,
                'formatted_amount' => $trx->formatted_amount,
                'payment_method' => $trx->payment_method,
                'payment_method_label' => $trx->payment_method_label,
                'has_proof' => $trx->transfer_proof !== null,
                'transfer_proof_url' => $trx->transfer_proof_url,
                'can_verify' => $trx->canBeVerified(),
                'created_at' => $trx->created_at->format('d M Y H:i'),
            ]),
            'allowedTransitions' => $this->getAllowedTransitions($order),
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = $validated['status'];

        if (!$order->canTransitionTo($newStatus)) {
            return back()->with('error', 'Tidak dapat mengubah status ke ' . Order::getStatuses()[$newStatus]);
        }

        $updateData = ['status' => $newStatus];

        // Set paid_at if transitioning to payment_received
        if ($newStatus === Order::STATUS_PAYMENT_RECEIVED && !$order->paid_at) {
            $updateData['paid_at'] = now();
        }

        // Set tracking number if transitioning to shipped
        if ($newStatus === Order::STATUS_SHIPPED && !empty($validated['tracking_number'])) {
            $updateData['shipping_tracking_number'] = $validated['tracking_number'];
        }

        // Add admin notes
        if (!empty($validated['admin_notes'])) {
            $currentNotes = $order->admin_notes ?? '';
            $timestamp = now()->format('d/m/Y H:i');
            $newNote = "[{$timestamp}] " . $validated['admin_notes'];
            $updateData['admin_notes'] = $currentNotes ? "{$currentNotes}\n{$newNote}" : $newNote;
        }

        $order->update($updateData);

        return back()->with('success', 'Status pesanan berhasil diperbarui');
    }

    /**
     * Verify bank transfer payment.
     */
    public function verifyPayment(Order $order, Transaction $transaction): RedirectResponse
    {
        if ($transaction->order_id !== $order->id) {
            abort(403);
        }

        if (!$transaction->canBeVerified()) {
            return back()->with('error', 'Transaksi tidak dapat diverifikasi');
        }

        $transaction->verify();

        return back()->with('success', 'Pembayaran berhasil diverifikasi');
    }

    /**
     * Reject bank transfer payment.
     */
    public function rejectPayment(Request $request, Order $order, Transaction $transaction): RedirectResponse
    {
        if ($transaction->order_id !== $order->id) {
            abort(403);
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $transaction->update([
            'status' => Transaction::STATUS_FAILED,
        ]);

        // Add admin note
        $timestamp = now()->format('d/m/Y H:i');
        $note = "[{$timestamp}] Pembayaran ditolak: " . $request->reason;
        $order->update([
            'admin_notes' => $order->admin_notes ? "{$order->admin_notes}\n{$note}" : $note,
        ]);

        return back()->with('success', 'Pembayaran ditolak');
    }

    /**
     * Update tracking number.
     */
    public function updateTracking(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_courier' => ['required', 'string', 'max:50'],
            'shipping_service' => ['nullable', 'string', 'max:50'],
            'shipping_tracking_number' => ['required', 'string', 'max:100'],
        ], [
            'shipping_courier.required' => 'Pilih kurir pengiriman',
            'shipping_tracking_number.required' => 'Masukkan nomor resi',
        ]);

        $order->update($validated);

        // Auto update status to shipped if currently processing
        if ($order->status === Order::STATUS_PROCESSING) {
            $order->markAsShipped($validated['shipping_tracking_number']);
        }

        return back()->with('success', 'Nomor resi berhasil diperbarui');
    }

    /**
     * Print invoice/packing slip.
     */
    public function printInvoice(Order $order)
    {
        $order->load(['user', 'items']);

        return Inertia::render('Admin/Orders/Invoice', [
            'order' => $order,
            'items' => $order->items,
        ]);
    }

    /**
     * Get allowed status transitions for order.
     */
    private function getAllowedTransitions(Order $order): array
    {
        $allStatuses = Order::getStatuses();
        $allowed = [];

        foreach ($allStatuses as $status => $label) {
            if ($order->canTransitionTo($status)) {
                $allowed[] = [
                    'value' => $status,
                    'label' => $label,
                ];
            }
        }

        return $allowed;
    }
}
