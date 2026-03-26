<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Order;
use App\Models\SubscriptionTransaction;
use App\Models\Tenant;
use App\Models\Transaction;
use Carbon\Carbon;
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
     * Create Snap transaction token for subscription checkout.
     */
    public function createSubscriptionSnapToken(
        SubscriptionTransaction $subscriptionTransaction,
        Tenant $tenant,
        Package $package,
        string $paymentType,
        ?string $paymentProvider = null
    ): ?array {
        $payload = $this->buildSubscriptionSnapPayload(
            $subscriptionTransaction,
            $tenant,
            $package,
            $paymentType,
            $paymentProvider
        );

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post($this->baseUrl . '/snap/v1/transactions', $payload);

            if (!$response->successful()) {
                Log::error('Midtrans Subscription Snap Token Error', [
                    'order_id' => $subscriptionTransaction->order_id,
                    'payload' => $payload,
                    'response' => $response->json(),
                    'status' => $response->status(),
                ]);

                return null;
            }

            $result = $response->json();

            $subscriptionTransaction->update([
                'payment_type' => $paymentType,
                'payment_provider' => $paymentProvider,
                'status' => 'pending',
            ]);

            return $result;
        } catch (\Throwable $e) {
            Log::error('Midtrans Subscription Snap Token Exception', [
                'order_id' => $subscriptionTransaction->order_id,
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

    /**
     * Create Midtrans Core charge for tenant subscription transaction
     */
    public function createSubscriptionCharge(
        SubscriptionTransaction $subscriptionTransaction,
        Tenant $tenant,
        Package $package,
        string $paymentType,
        ?string $paymentProvider = null
    ): ?array {
        $payload = $this->buildSubscriptionChargePayload(
            $subscriptionTransaction,
            $tenant,
            $package,
            $paymentType,
            $paymentProvider
        );

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post($this->baseUrl . '/v2/charge', $payload);

            if (!$response->successful()) {
                Log::error('Midtrans Subscription Charge Error', [
                    'order_id' => $subscriptionTransaction->order_id,
                    'payload' => $payload,
                    'response' => $response->json(),
                    'status' => $response->status(),
                ]);

                return null;
            }

            $result = $response->json();

            $subscriptionTransaction->update([
                'payment_type' => $result['payment_type'] ?? $paymentType,
                'payment_provider' => $this->extractPaymentProviderFromResponse($result, $paymentType, $paymentProvider),
                'status' => $result['transaction_status'] ?? 'pending',
                'midtrans_transaction_id' => $result['transaction_id'] ?? null,
                'payment_details' => $this->extractPaymentDetails($result),
            ]);

            return $result;
        } catch (\Throwable $e) {
            Log::error('Midtrans Subscription Charge Exception', [
                'order_id' => $subscriptionTransaction->order_id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Handle Midtrans webhook for subscription transactions
     */
    public function handleSubscriptionNotification(array $notification): bool
    {
        if (!$this->verifySignature($notification)) {
            Log::warning('Midtrans Subscription Invalid Signature', $notification);
            return false;
        }

        $orderId = $notification['order_id'] ?? null;
        if (!$orderId) {
            return false;
        }

        $subscriptionTransaction = SubscriptionTransaction::where('order_id', $orderId)->first();
        if (!$subscriptionTransaction) {
            Log::warning('Subscription transaction not found for Midtrans webhook', [
                'order_id' => $orderId,
            ]);
            return false;
        }

        return $this->applySubscriptionTransactionStatus($subscriptionTransaction, $notification);
    }

    /**
     * Sync a subscription transaction status from Midtrans API by order ID.
     */
    public function syncSubscriptionStatusByOrderId(string $orderId): bool
    {
        $subscriptionTransaction = SubscriptionTransaction::where('order_id', $orderId)->first();
        if (!$subscriptionTransaction) {
            return false;
        }

        $statusResponse = $this->getTransactionStatus($orderId);
        if (!$statusResponse) {
            return false;
        }

        return $this->applySubscriptionTransactionStatus($subscriptionTransaction, $statusResponse);
    }

    /**
     * Apply transaction status payload from Midtrans to subscription transaction and tenant.
     */
    protected function applySubscriptionTransactionStatus(SubscriptionTransaction $subscriptionTransaction, array $payload): bool
    {
        $transactionStatus = $payload['transaction_status'] ?? 'pending';
        $fraudStatus = $payload['fraud_status'] ?? null;

        $subscriptionTransaction->update([
            'status' => $transactionStatus,
            'payment_type' => $payload['payment_type'] ?? $subscriptionTransaction->payment_type,
            'payment_provider' => $this->extractPaymentProviderFromResponse(
                $payload,
                $payload['payment_type'] ?? ($subscriptionTransaction->payment_type ?? ''),
                $subscriptionTransaction->payment_provider
            ),
            'midtrans_transaction_id' => $payload['transaction_id'] ?? $subscriptionTransaction->midtrans_transaction_id,
            'payment_details' => $this->extractPaymentDetails($payload),
            'paid_at' => in_array($transactionStatus, ['settlement', 'capture']) ? now() : $subscriptionTransaction->paid_at,
        ]);

        if ($transactionStatus === 'capture' && $fraudStatus !== null && $fraudStatus !== 'accept') {
            return true;
        }

        if (!in_array($transactionStatus, ['settlement', 'capture'])) {
            return true;
        }

        $tenant = Tenant::find($subscriptionTransaction->tenant_id);
        if (!$tenant) {
            return false;
        }

        $package = $subscriptionTransaction->package;
        if (!$package) {
            return false;
        }

        $baseDate = $tenant->expired_at && Carbon::parse($tenant->expired_at)->isFuture()
            ? Carbon::parse($tenant->expired_at)
            : now();

        $durationDays = (int) $package->duration_in_days * ($tenant->billing_cycle === 'yearly' ? 12 : 1);

        $tenant->update([
            'status' => 'active',
            'subscription_status' => 'active',
            'package_id' => $package->id,
            'subscription_ends_at' => $baseDate->copy()->addDays($durationDays),
            'expired_at' => $baseDate->copy()->addDays($durationDays),
        ]);

        return true;
    }

    /**
     * Build payload for Midtrans Core charge (v2/charge)
     */
    protected function buildSubscriptionChargePayload(
        SubscriptionTransaction $subscriptionTransaction,
        Tenant $tenant,
        Package $package,
        string $paymentType,
        ?string $paymentProvider = null
    ): array {
        $type = strtolower($paymentType);
        $payload = [
            'transaction_details' => [
                'order_id' => $subscriptionTransaction->order_id,
                'gross_amount' => (int) $subscriptionTransaction->gross_amount,
            ],
            'item_details' => [[
                'id' => 'PKG-' . $package->id,
                'price' => (int) $subscriptionTransaction->gross_amount,
                'quantity' => 1,
                'name' => substr($package->name, 0, 50),
            ]],
            'customer_details' => [
                'first_name' => $tenant->store_name ?? ('Tenant ' . $tenant->id),
                'email' => $tenant->owner?->email,
                'phone' => $tenant->owner?->phone ?? '',
            ],
            'custom_expiry' => [
                'order_time' => now()->format('Y-m-d H:i:s O'),
                'expiry_duration' => (int) config('midtrans.expiry_duration', 1440),
                'unit' => 'minute',
            ],
        ];

        if (in_array($type, ['bank_transfer', 'va'])) {
            $payload['payment_type'] = 'bank_transfer';
            $payload['bank_transfer'] = [
                'bank' => strtolower((string) ($paymentProvider ?: 'bca')),
            ];

            return $payload;
        }

        if (in_array($type, ['ewallet', 'gopay', 'shopeepay'])) {
            $provider = strtolower((string) ($paymentProvider ?: 'gopay'));
            $payload['payment_type'] = in_array($provider, ['gopay', 'shopeepay']) ? $provider : 'gopay';
            return $payload;
        }

        if (in_array($type, ['qris', 'qr'])) {
            $payload['payment_type'] = 'qris';
            return $payload;
        }

        $payload['payment_type'] = $type;
        return $payload;
    }

    /**
     * Build payload for Midtrans Snap transaction.
     */
    protected function buildSubscriptionSnapPayload(
        SubscriptionTransaction $subscriptionTransaction,
        Tenant $tenant,
        Package $package,
        string $paymentType,
        ?string $paymentProvider = null
    ): array {
        $enabledPayments = $this->mapSubscriptionEnabledPayments($paymentType, $paymentProvider);

        return [
            'transaction_details' => [
                'order_id' => $subscriptionTransaction->order_id,
                'gross_amount' => (int) $subscriptionTransaction->gross_amount,
            ],
            'item_details' => [[
                'id' => 'PKG-' . $package->id,
                'price' => (int) $subscriptionTransaction->gross_amount,
                'quantity' => 1,
                'name' => substr($package->name, 0, 50),
            ]],
            'customer_details' => [
                'first_name' => $tenant->store_name ?? ('Tenant ' . $tenant->id),
                'email' => $tenant->owner?->email,
                'phone' => $tenant->owner?->phone ?? '',
            ],
            'enabled_payments' => $enabledPayments,
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minutes',
                'duration' => (int) config('midtrans.expiry_duration', 1440),
            ],
        ];
    }

    /**
     * Map onboarding selected payment to Midtrans enabled_payments for Snap.
     */
    protected function mapSubscriptionEnabledPayments(string $paymentType, ?string $paymentProvider = null): array
    {
        $defaultChannels = [
            'bca_va',
            'bni_va',
            'bri_va',
            'permata_va',
            'other_va',
            'qris',
            'other_qris',
            'gopay',
            'shopeepay',
        ];

        $configuredChannels = config('midtrans.enabled_payments', $defaultChannels);
        $type = strtolower($paymentType);
        $provider = strtolower((string) ($paymentProvider ?: ''));

        $selectedChannels = [];

        if ($type === 'bank_transfer') {
            if ($provider === 'bca') {
                $selectedChannels = ['bca_va'];
            }
            elseif ($provider === 'bni') {
                $selectedChannels = ['bni_va'];
            }
            elseif ($provider === 'bri') {
                $selectedChannels = ['bri_va'];
            }
            elseif ($provider === 'permata') {
                $selectedChannels = ['permata_va'];
            } else {
                $selectedChannels = ['other_va'];
            }
        }
        elseif ($type === 'qris') {
            $selectedChannels = ['qris', 'other_qris'];
        }
        elseif ($type === 'gopay') {
            $selectedChannels = ['gopay'];
        }
        elseif ($type === 'shopeepay') {
            $selectedChannels = ['shopeepay'];
        }

        $enabledSelectedChannels = array_values(array_intersect($selectedChannels, $configuredChannels));

        if (!empty($enabledSelectedChannels)) {
            return $enabledSelectedChannels;
        }

        if (!empty($selectedChannels)) {
            return $selectedChannels;
        }

        return $configuredChannels;
    }

    /**
     * Extract provider/channel from Midtrans payload/response
     */
    protected function extractPaymentProviderFromResponse(array $response, string $paymentType, ?string $fallback = null): ?string
    {
        if (($response['payment_type'] ?? '') === 'bank_transfer') {
            if (!empty($response['va_numbers'][0]['bank'])) {
                return strtolower((string) $response['va_numbers'][0]['bank']);
            }

            if (!empty($response['permata_va_number'])) {
                return 'permata';
            }
        }

        if (!empty($response['payment_type'])) {
            return strtolower((string) $response['payment_type']);
        }

        return $fallback ?: (strtolower($paymentType) ?: null);
    }

    /**
     * Keep only useful payment instruction fields for storage
     */
    protected function extractPaymentDetails(array $response): array
    {
        $details = [];

        if (!empty($response['va_numbers'])) {
            $details['va_numbers'] = $response['va_numbers'];
        }

        if (!empty($response['permata_va_number'])) {
            $details['permata_va_number'] = $response['permata_va_number'];
        }

        if (!empty($response['bill_key'])) {
            $details['bill_key'] = $response['bill_key'];
        }

        if (!empty($response['biller_code'])) {
            $details['biller_code'] = $response['biller_code'];
        }

        if (!empty($response['actions'])) {
            $details['actions'] = $response['actions'];
        }

        if (!empty($response['expiry_time'])) {
            $details['expiry_time'] = $response['expiry_time'];
        }

        if (!empty($response['transaction_status'])) {
            $details['transaction_status'] = $response['transaction_status'];
        }

        if (!empty($response['status_message'])) {
            $details['status_message'] = $response['status_message'];
        }

        return $details;
    }
}
