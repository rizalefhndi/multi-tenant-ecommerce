<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected bool $isProduction;
    protected string $baseUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = config('midtrans.is_production', false);
        $this->baseUrl = $this->isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    /**
     * Get Snap URL for JavaScript
     */
    public function getSnapUrl(): string
    {
        return config('midtrans.snap_url');
    }

    /**
     * Get Client Key for frontend
     */
    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    /**
     * Create Snap Transaction Token
     */
    public function createSnapToken(Order $order): ?string
    {
        $params = $this->buildSnapParams($order);

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post($this->baseUrl . '/snap/v1/transactions', $params);

            if ($response->successful()) {
                $data = $response->json();
                return $data['token'] ?? null;
            }

            Log::error('Midtrans Snap Token Error', [
                'order_id' => $order->id,
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Exception', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create Snap Redirect URL
     */
    public function createSnapRedirectUrl(Order $order): ?string
    {
        $params = $this->buildSnapParams($order);

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post($this->baseUrl . '/snap/v1/transactions', $params);

            if ($response->successful()) {
                $data = $response->json();
                return $data['redirect_url'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans Redirect URL Exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Build Snap Parameters
     */
    protected function buildSnapParams(Order $order): array
    {
        $items = $order->items->map(function ($item) {
            return [
                'id' => (string) $item->product_id,
                'name' => substr($item->product_name, 0, 50), // Max 50 chars
                'price' => (int) $item->price,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // Add shipping as item if exists
        if ($order->shipping_cost > 0) {
            $items[] = [
                'id' => 'shipping',
                'name' => 'Ongkos Kirim (' . ($order->shipping_info ?? 'Standard') . ')',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
            ];
        }

        // Add discount as negative item if exists
        if ($order->discount > 0) {
            $items[] = [
                'id' => 'discount',
                'name' => 'Diskon',
                'price' => (int) -$order->discount,
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
                'billing_address' => [
                    'first_name' => $order->shipping_address_snapshot['recipient_name'] ?? '',
                    'phone' => $order->shipping_address_snapshot['phone'] ?? '',
                    'address' => $order->shipping_address_snapshot['address_line_1'] ?? '',
                    'city' => $order->shipping_address_snapshot['city'] ?? '',
                    'postal_code' => $order->shipping_address_snapshot['postal_code'] ?? '',
                    'country_code' => 'IDN',
                ],
                'shipping_address' => [
                    'first_name' => $order->shipping_address_snapshot['recipient_name'] ?? '',
                    'phone' => $order->shipping_address_snapshot['phone'] ?? '',
                    'address' => $order->shipping_address_snapshot['address_line_1'] ?? '',
                    'city' => $order->shipping_address_snapshot['city'] ?? '',
                    'postal_code' => $order->shipping_address_snapshot['postal_code'] ?? '',
                    'country_code' => 'IDN',
                ],
            ],
            'enabled_payments' => config('midtrans.enabled_payments'),
            'callbacks' => [
                'finish' => config('midtrans.finish_url') ?: route('checkout.success', $order->id),
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minutes',
                'duration' => config('midtrans.expiry_duration', 1440),
            ],
        ];

        // Add credit card options
        if (in_array('credit_card', config('midtrans.enabled_payments', []))) {
            $params['credit_card'] = [
                'secure' => config('midtrans.credit_card.secure', true),
            ];
        }

        return $params;
    }

    /**
     * Verify notification signature
     */
    public function verifySignature(array $notification): bool
    {
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';

        $expectedSignature = hash('sha512',
            $orderId . $statusCode . $grossAmount . $this->serverKey
        );

        return $signatureKey === $expectedSignature;
    }

    /**
     * Handle notification from Midtrans
     */
    public function handleNotification(array $notification): bool
    {
        // Verify signature
        if (!$this->verifySignature($notification)) {
            Log::warning('Midtrans Invalid Signature', $notification);
            return false;
        }

        $orderId = $notification['order_id'] ?? '';
        $transactionStatus = $notification['transaction_status'] ?? '';
        $fraudStatus = $notification['fraud_status'] ?? '';
        $paymentType = $notification['payment_type'] ?? '';

        // Find order by order_number
        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            Log::error('Midtrans Order Not Found', ['order_id' => $orderId]);
            return false;
        }

        // Find or create transaction
        $transaction = $order->transactions()
            ->where('type', Transaction::TYPE_PAYMENT)
            ->latest()
            ->first();

        if (!$transaction) {
            $transaction = Transaction::createPayment($order, $paymentType, [
                'payment_channel' => 'midtrans',
            ]);
        }

        // Update transaction with Midtrans data
        $transaction->update([
            'gateway_response' => $notification,
            'gateway_transaction_id' => $notification['transaction_id'] ?? null,
            'payment_method' => $this->mapPaymentType($paymentType),
            'payment_channel' => $paymentType,
        ]);

        // Process based on status
        return $this->processTransactionStatus(
            $order,
            $transaction,
            $transactionStatus,
            $fraudStatus
        );
    }

    /**
     * Process transaction status
     */
    protected function processTransactionStatus(
        Order $order,
        Transaction $transaction,
        string $transactionStatus,
        string $fraudStatus
    ): bool {
        switch ($transactionStatus) {
            case 'capture':
                // For credit card, check fraud status
                if ($fraudStatus === 'accept') {
                    $transaction->markAsSuccess();
                } elseif ($fraudStatus === 'challenge') {
                    // Challenge - need manual review
                    Log::info('Midtrans Challenge', ['order' => $order->order_number]);
                }
                break;

            case 'settlement':
                // Payment settled
                $transaction->markAsSuccess();
                break;

            case 'pending':
                // Still pending
                $transaction->update(['status' => Transaction::STATUS_PENDING]);
                break;

            case 'deny':
            case 'cancel':
                // Payment denied/cancelled
                $transaction->markAsFailed();
                break;

            case 'expire':
                // Payment expired
                $transaction->markAsExpired();
                break;

            case 'refund':
            case 'partial_refund':
                // Handle refund
                Transaction::createRefund(
                    $order,
                    (float) ($notification['refund_amount'] ?? $order->total),
                    'Midtrans Refund'
                );
                break;

            default:
                Log::warning('Midtrans Unknown Status', [
                    'status' => $transactionStatus,
                    'order' => $order->order_number,
                ]);
                return false;
        }

        return true;
    }

    /**
     * Map Midtrans payment type to our payment method
     */
    protected function mapPaymentType(string $paymentType): string
    {
        $map = [
            'credit_card' => Transaction::METHOD_CREDIT_CARD,
            'bank_transfer' => Transaction::METHOD_VIRTUAL_ACCOUNT,
            'echannel' => Transaction::METHOD_VIRTUAL_ACCOUNT,
            'bca_va' => Transaction::METHOD_VIRTUAL_ACCOUNT,
            'bni_va' => Transaction::METHOD_VIRTUAL_ACCOUNT,
            'bri_va' => Transaction::METHOD_VIRTUAL_ACCOUNT,
            'permata_va' => Transaction::METHOD_VIRTUAL_ACCOUNT,
            'other_va' => Transaction::METHOD_VIRTUAL_ACCOUNT,
            'gopay' => Transaction::METHOD_GOPAY,
            'shopeepay' => Transaction::METHOD_SHOPEEPAY,
            'qris' => Transaction::METHOD_QRIS,
            'cstore' => 'convenience_store',
            'akulaku' => 'paylater',
            'kredivo' => 'paylater',
        ];

        return $map[$paymentType] ?? $paymentType;
    }

    /**
     * Get transaction status from Midtrans
     */
    public function getTransactionStatus(string $orderId): ?array
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->get($this->baseUrl . '/v2/' . $orderId . '/status');

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans Get Status Exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Cancel transaction
     */
    public function cancelTransaction(string $orderId): bool
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post($this->baseUrl . '/v2/' . $orderId . '/cancel');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Midtrans Cancel Exception', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Refund transaction (for credit card)
     */
    public function refundTransaction(string $orderId, float $amount, string $reason = ''): bool
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post($this->baseUrl . '/v2/' . $orderId . '/refund', [
                    'refund_key' => 'refund-' . $orderId . '-' . time(),
                    'amount' => (int) $amount,
                    'reason' => $reason,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Midtrans Refund Exception', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
