<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MyStoresController extends Controller
{
    /**
     * Display user's stores
     */
    public function index(): Response
    {
        $user = Auth::user();
        $scheme = request()->isSecure() ? 'https' : 'http';
        $port = parse_url(config('app.url'), PHP_URL_PORT);

        // Get all stores owned by the user
        $stores = Tenant::where('owner_id', $user->id)
            ->with(['plan', 'package', 'domains'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($tenant) use ($scheme, $port) {
                $domain = $tenant->domains->first();
                $fullUrl = null;
                if ($domain) {
                    $fullUrl = $scheme . '://' . $domain->domain;
                    if ($port && !in_array((int) $port, [80, 443], true)) {
                        $fullUrl .= ':' . $port;
                    }
                }

                return [
                    'id' => $tenant->id,
                    'store_name' => $tenant->store_name,
                    'subdomain' => $tenant->id,
                    'domain' => $domain ? $domain->domain : null,
                    'full_url' => $fullUrl,
                    'plan' => $tenant->plan ? [
                        'name' => $tenant->plan->name,
                        'slug' => $tenant->plan->slug,
                    ] : ($tenant->package ? [
                        'name' => $tenant->package->name,
                        'slug' => null,
                    ] : null),
                    'status' => $tenant->status,
                    'subscription_status' => $tenant->subscription_status,
                    'needs_payment' => $tenant->status === 'pending',
                    'billing_checkout_url' => route('billing.checkout.page', ['tenant' => $tenant->id]),
                    'sso_url' => route('sso.redirect', ['tenant' => $tenant->id]),
                    'primary_action' => match ($tenant->status) {
                        'pending' => [
                            'label' => 'Continue Payment',
                            'url' => route('billing.checkout.page', ['tenant' => $tenant->id]),
                            'kind' => 'warning',
                            'disabled' => false,
                        ],
                        'expired' => [
                            'label' => 'Renew Now',
                            'url' => route('billing.checkout.page', ['tenant' => $tenant->id]),
                            'kind' => 'warning',
                            'disabled' => false,
                        ],
                        'active' => [
                            'label' => 'Enter Store',
                            'url' => route('sso.redirect', ['tenant' => $tenant->id]),
                            'kind' => 'primary',
                            'disabled' => false,
                        ],
                        'suspended' => [
                            'label' => 'Temporarily Suspended',
                            'url' => null,
                            'kind' => 'danger',
                            'disabled' => true,
                        ],
                        default => null,
                    },
                    'cancel_action' => $tenant->status === 'pending' ? [
                        'label' => 'Cancel Store',
                        'url' => route('store.cancel', ['tenant' => $tenant->id]),
                    ] : null,
                    'status_note' => match ($tenant->status) {
                        'pending' => 'Complete payment to activate your store.',
                        'expired' => 'Subscription expired. Renew your package to reactivate.',
                        'suspended' => $tenant->suspended_reason ?: 'Your store is temporarily suspended.',
                        default => null,
                    },
                    'created_at' => $tenant->created_at->format('M d, Y'),
                ];
            });

        return Inertia::render('Landlord/MyStores', [
            'stores' => $stores,
            'auth' => [
                'user' => $user,
            ],
        ]);
    }
}
