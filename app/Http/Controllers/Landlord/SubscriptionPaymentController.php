<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\SubscriptionTransaction;
use App\Models\Tenant;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionPaymentController extends Controller
{
    public function __construct(private readonly MidtransService $midtransService)
    {
    }

    /**
     * Show custom checkout page for a tenant.
     */
    public function checkoutPage(Tenant $tenant): Response
    {
        abort_if($tenant->owner_id !== Auth::id(), 403);

        $billingCycle = $tenant->billing_cycle === 'yearly' ? 'yearly' : 'monthly';

        $packages = Package::query()
            ->orderBy('price')
            ->get()
            ->map(function (Package $package) use ($billingCycle) {
                $monthlyPrice = (float) $package->price;
                $yearlyPrice = $monthlyPrice * 12;
                $selectedPrice = $billingCycle === 'yearly' ? $yearlyPrice : $monthlyPrice;

                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'price' => $selectedPrice,
                    'price_monthly' => $monthlyPrice,
                    'price_yearly' => $yearlyPrice,
                    'formatted_price' => 'Rp ' . number_format($selectedPrice, 0, ',', '.'),
                    'duration_in_days' => $package->duration_in_days,
                ];
            })
            ->values();

        return Inertia::render('Landlord/BillingCheckout', [
            'tenant' => [
                'id' => $tenant->id,
                'store_name' => $tenant->store_name,
                'status' => $tenant->status,
                'package_id' => $tenant->package_id,
                'billing_cycle' => $billingCycle,
            ],
            'packages' => $packages,
            'defaultPackageId' => $tenant->package_id,
            'snap' => [
                'url' => $this->midtransService->getSnapUrl(),
                'clientKey' => $this->midtransService->getClientKey(),
            ],
        ]);
    }

    /**
     * Show pending payment instruction page.
     */
    public function pendingPage(string $orderId): Response
    {
        $transaction = SubscriptionTransaction::query()
            ->where('order_id', $orderId)
            ->whereHas('tenant', function ($query) {
                $query->where('owner_id', Auth::id());
            })
            ->with(['tenant:id,owner_id,store_name,status,expired_at', 'package:id,name,duration_in_days,price'])
            ->firstOrFail();

        return Inertia::render('Landlord/BillingPending', [
            'transaction' => [
                'id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'status' => $transaction->status,
                'gross_amount' => (float) $transaction->gross_amount,
                'formatted_gross_amount' => 'Rp ' . number_format((float) $transaction->gross_amount, 0, ',', '.'),
                'payment_type' => $transaction->payment_type,
                'payment_provider' => $transaction->payment_provider,
                'payment_details' => $transaction->payment_details,
                'paid_at' => $transaction->paid_at,
                'tenant' => [
                    'id' => $transaction->tenant?->id,
                    'store_name' => $transaction->tenant?->store_name,
                    'status' => $transaction->tenant?->status,
                    'expired_at' => $transaction->tenant?->expired_at,
                ],
                'package' => [
                    'id' => $transaction->package?->id,
                    'name' => $transaction->package?->name,
                    'price' => (float) ($transaction->package?->price ?? 0),
                    'duration_in_days' => $transaction->package?->duration_in_days,
                ],
            ],
        ]);
    }

    /**
     * Create subscription checkout via Midtrans Core API.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'payment_type' => ['required', 'string', 'in:bank_transfer,ewallet,qris,gopay,shopeepay'],
            'payment_provider' => ['nullable', 'string', 'max:50'],
        ]);

        $tenant = Tenant::where('id', $validated['tenant_id'])
            ->where('owner_id', Auth::id())
            ->firstOrFail();

        $package = Package::findOrFail($validated['package_id']);

        $transaction = SubscriptionTransaction::create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'order_id' => $this->generateOrderId(),
            'gross_amount' => $this->resolveGrossAmount($package, $tenant->billing_cycle),
            'payment_type' => $validated['payment_type'],
            'payment_provider' => $validated['payment_provider'] ?? null,
            'status' => 'pending',
        ]);

        $midtransResponse = $this->midtransService->createSubscriptionCharge(
            $transaction,
            $tenant,
            $package,
            $validated['payment_type'],
            $validated['payment_provider'] ?? null,
        );

        if (!$midtransResponse) {
            $transaction->update(['status' => 'failed']);

            return response()->json([
                'message' => 'Failed to create payment transaction to Midtrans.',
            ], 500);
        }

        return response()->json([
            'message' => 'Transaction created successfully.',
            'transaction' => [
                'id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'status' => $transaction->status,
                'gross_amount' => $transaction->gross_amount,
                'payment_type' => $transaction->payment_type,
                'payment_provider' => $transaction->payment_provider,
                'payment_details' => $transaction->payment_details,
            ],
            'midtrans' => $midtransResponse,
        ]);
    }

    /**
     * Create subscription transaction and return Midtrans Snap token.
     */
    public function snapToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'payment_type' => ['required', 'string', 'in:bank_transfer,ewallet,qris,gopay,shopeepay'],
            'payment_provider' => ['nullable', 'string', 'max:50'],
        ]);

        $tenant = Tenant::where('id', $validated['tenant_id'])
            ->where('owner_id', Auth::id())
            ->firstOrFail();

        $package = Package::findOrFail($validated['package_id']);

        $transaction = SubscriptionTransaction::create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'order_id' => $this->generateOrderId(),
            'gross_amount' => $this->resolveGrossAmount($package, $tenant->billing_cycle),
            'payment_type' => $validated['payment_type'],
            'payment_provider' => $validated['payment_provider'] ?? null,
            'status' => 'pending',
        ]);

        $snapResponse = $this->midtransService->createSubscriptionSnapToken(
            $transaction,
            $tenant,
            $package,
            $validated['payment_type'],
            $validated['payment_provider'] ?? null,
        );

        if (!$snapResponse || empty($snapResponse['token'])) {
            $transaction->update(['status' => 'failed']);

            return response()->json([
                'message' => 'Failed to create Snap payment token.',
            ], 500);
        }

        return response()->json([
            'message' => 'Snap token created successfully.',
            'snap_token' => $snapResponse['token'],
            'redirect_url' => $snapResponse['redirect_url'] ?? null,
            'transaction' => [
                'id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'status' => $transaction->status,
                'gross_amount' => $transaction->gross_amount,
                'payment_type' => $transaction->payment_type,
                'payment_provider' => $transaction->payment_provider,
            ],
        ]);
    }

    /**
     * Get current status of a subscription transaction.
     */
    public function status(Request $request, string $orderId): JsonResponse
    {
        $transaction = SubscriptionTransaction::where('order_id', $orderId)
            ->whereHas('tenant', function ($query) {
                $query->where('owner_id', Auth::id());
            })
            ->with(['tenant:id,owner_id,store_name,status,expired_at', 'package:id,name,duration_in_days'])
            ->firstOrFail();

        $this->midtransService->syncSubscriptionStatusByOrderId($transaction->order_id);
        $transaction->refresh()->load(['tenant:id,owner_id,store_name,status,expired_at', 'package:id,name,duration_in_days']);

        return response()->json([
            'transaction' => [
                'id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'status' => $transaction->status,
                'gross_amount' => $transaction->gross_amount,
                'payment_type' => $transaction->payment_type,
                'payment_provider' => $transaction->payment_provider,
                'payment_details' => $transaction->payment_details,
                'paid_at' => $transaction->paid_at,
                'tenant' => [
                    'id' => $transaction->tenant?->id,
                    'store_name' => $transaction->tenant?->store_name,
                    'status' => $transaction->tenant?->status,
                    'expired_at' => $transaction->tenant?->expired_at,
                ],
                'package' => [
                    'id' => $transaction->package?->id,
                    'name' => $transaction->package?->name,
                    'duration_in_days' => $transaction->package?->duration_in_days,
                ],
            ],
        ]);
    }

    /**
     * Midtrans webhook endpoint for subscription transactions.
     */
    public function webhook(Request $request): JsonResponse
    {
        $notification = $request->all();

        $isHandled = $this->midtransService->handleSubscriptionNotification($notification);

        if (!$isHandled) {
            return response()->json(['status' => 'error'], 400);
        }

        return response()->json(['status' => 'ok']);
    }

    private function generateOrderId(): string
    {
        $date = now()->format('YmdHis');
        $rand = strtoupper(Str::random(6));

        return "INV-ONYX-{$date}-{$rand}";
    }

    private function resolveGrossAmount(Package $package, ?string $billingCycle): float
    {
        $monthlyPrice = (float) $package->price;
        return $billingCycle === 'yearly'
            ? $monthlyPrice * 12
            : $monthlyPrice;
    }
}
