<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Handle Midtrans notification/callback (webhook)
     * 
     * Route: POST /webhook/midtrans
     */
    public function notification(Request $request)
    {
        // Get raw notification data
        $notification = $request->all();

        Log::info('Midtrans Notification Received', [
            'order_id' => $notification['order_id'] ?? 'unknown',
            'transaction_status' => $notification['transaction_status'] ?? 'unknown',
        ]);

        // Process notification
        $result = $this->midtransService->handleNotification($notification);

        if ($result) {
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error'], 400);
    }

    /**
     * Handle finish redirect from Midtrans
     * 
     * Route: GET /payment/finish
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        $transactionStatus = $request->get('transaction_status');
        $statusCode = $request->get('status_code');

        Log::info('Midtrans Finish Redirect', [
            'order_id' => $orderId,
            'status' => $transactionStatus,
        ]);

        // Redirect based on status
        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            return redirect()->route('checkout.success', ['order' => $orderId])
                ->with('success', 'Pembayaran berhasil!');
        }

        if ($transactionStatus === 'pending') {
            return redirect()->route('orders.show', ['order' => $orderId])
                ->with('info', 'Menunggu konfirmasi pembayaran...');
        }

        // Failed or cancelled
        return redirect()->route('orders.show', ['order' => $orderId])
            ->with('error', 'Pembayaran tidak berhasil. Silakan coba lagi.');
    }

    /**
     * Handle unfinish redirect from Midtrans
     * 
     * Route: GET /payment/unfinish
     */
    public function unfinish(Request $request)
    {
        $orderId = $request->get('order_id');

        Log::info('Midtrans Unfinish Redirect', ['order_id' => $orderId]);

        return redirect()->route('orders.show', ['order' => $orderId])
            ->with('warning', 'Pembayaran belum selesai. Anda bisa melanjutkan pembayaran nanti.');
    }

    /**
     * Handle error redirect from Midtrans
     * 
     * Route: GET /payment/error
     */
    public function error(Request $request)
    {
        $orderId = $request->get('order_id');
        $statusMessage = $request->get('status_message', 'Terjadi kesalahan');

        Log::error('Midtrans Error Redirect', [
            'order_id' => $orderId,
            'message' => $statusMessage,
        ]);

        return redirect()->route('orders.show', ['order' => $orderId])
            ->with('error', 'Pembayaran gagal: ' . $statusMessage);
    }

    /**
     * Get snap token for order (API endpoint for frontend)
     * 
     * Route: POST /api/payment/snap-token
     */
    public function getSnapToken(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = \App\Models\Order::findOrFail($request->order_id);

        // Check ownership
        if ($order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if order is still pending
        if ($order->status !== \App\Models\Order::STATUS_PENDING_PAYMENT) {
            return response()->json([
                'error' => 'Order sudah dibayar atau tidak valid'
            ], 400);
        }

        // Get snap token
        $snapToken = $this->midtransService->createSnapToken($order);

        if (!$snapToken) {
            return response()->json([
                'error' => 'Gagal membuat token pembayaran'
            ], 500);
        }

        return response()->json([
            'snap_token' => $snapToken,
            'client_key' => $this->midtransService->getClientKey(),
        ]);
    }

    /**
     * Check transaction status (API endpoint)
     * 
     * Route: GET /api/payment/status/{orderNumber}
     */
    public function checkStatus(string $orderNumber)
    {
        $order = \App\Models\Order::where('order_number', $orderNumber)->firstOrFail();

        // Check ownership
        if ($order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $status = $this->midtransService->getTransactionStatus($orderNumber);

        if (!$status) {
            return response()->json([
                'error' => 'Status tidak ditemukan'
            ], 404);
        }

        return response()->json($status);
    }
}
