<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\SubscriptionTransaction;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    /**
     * Show the create store form
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $packageSlug = $request->query('plan');
        if (!$packageSlug) {
            return redirect()->route('pricing');
        }

        $billingCycle = $request->query('billing_cycle', 'monthly');
        if (!in_array($billingCycle, ['monthly', 'yearly'], true)) {
            $billingCycle = 'monthly';
        }

        $package = Package::query()
            ->get()
            ->first(fn ($item) => Str::slug($item->name) === $packageSlug);

        if (!$package) {
            return redirect()->route('pricing');
        }

        return Inertia::render('Landlord/CreateStore', [
            // Keep prop name `plan` to avoid large frontend refactor.
            'plan' => $package ? (function () use ($package, $billingCycle) {
                $monthlyPrice = (float) $package->price;
                $yearlyPrice = $monthlyPrice * 12;
                $selectedPrice = $billingCycle === 'yearly' ? $yearlyPrice : $monthlyPrice;

                return [
                    'id' => $package->id,
                    'slug' => Str::slug($package->name),
                    'name' => $package->name,
                    'description' => $package->description,
                    'billing_cycle' => $billingCycle,
                    'price_monthly' => $monthlyPrice,
                    'price_yearly' => $yearlyPrice,
                    'formatted_price_monthly' => 'Rp ' . number_format($monthlyPrice, 0, ',', '.'),
                    'formatted_price_selected' => 'Rp ' . number_format($selectedPrice, 0, ',', '.'),
                    'is_free' => $monthlyPrice <= 0,
                ];
            })() : null,
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    }

    /**
     * Handle store creation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                'not_in:www,admin,mail,api',
                Rule::unique('tenants', 'id'), // Tenant ID = subdomain
            ],
            // Keep request key `plan_id` to stay compatible with current UI payload.
            'plan_id' => ['required', 'exists:packages,id'],
            'billing_cycle' => ['required', 'string', 'in:monthly,yearly'],
        ], [
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
            'subdomain.unique' => 'This subdomain is already taken.',
            'subdomain.not_in' => 'This subdomain is reserved.',
        ]);

        $payload = $this->createTenantAndProvision($validated, Auth::user());

        return back()->with('success', $payload);
    }

    /**
     * API endpoint for realtime subdomain availability check.
     */
    public function checkSubdomain(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subdomain' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/'],
        ]);

        $subdomain = Str::slug($validated['subdomain']);
        $reserved = in_array($subdomain, ['www', 'admin', 'mail', 'api']);
        $exists = Tenant::where('id', $subdomain)->exists();

        return response()->json([
            'subdomain' => $subdomain,
            'available' => !$reserved && !$exists,
            'reason' => $reserved ? 'reserved' : ($exists ? 'taken' : null),
        ]);
    }

    /**
     * API endpoint for onboarding tenant creation.
     */
    public function storeApi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                'not_in:www,admin,mail,api',
                Rule::unique('tenants', 'id'),
            ],
            'package_id' => ['required', 'exists:packages,id'],
            'billing_cycle' => ['nullable', 'string', 'in:monthly,yearly'],
        ]);

        // Convert to current web payload shape for shared creator method.
        $payload = [
            'store_name' => $validated['store_name'],
            'subdomain' => $validated['subdomain'],
            'plan_id' => $validated['package_id'],
            'billing_cycle' => $validated['billing_cycle'] ?? 'monthly',
        ];

        return response()->json($this->createTenantAndProvision($payload, Auth::user()));
    }

    /**
     * Cancel pending store before payment is completed.
     */
    public function cancel(Tenant $tenant): RedirectResponse
    {
        abort_if($tenant->owner_id !== Auth::id(), 403);

        if ($tenant->status !== 'pending') {
            return back()->withErrors([
                'store' => 'Only pending stores can be cancelled.',
            ]);
        }

        $hasPaidTransaction = SubscriptionTransaction::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', ['settlement', 'capture'])
            ->exists();

        if ($hasPaidTransaction) {
            return back()->withErrors([
                'store' => 'This store already has a successful payment and cannot be cancelled.',
            ]);
        }

        $tenant->delete();

        return redirect()->route('my-stores')->with('success', [
            'message' => 'Pending store cancelled successfully.',
        ]);
    }

    private function createTenantAndProvision(array $validated, $user): array
    {
        $package = Package::findOrFail($validated['plan_id']);
        $billingCycle = in_array(($validated['billing_cycle'] ?? 'monthly'), ['monthly', 'yearly'], true)
            ? $validated['billing_cycle']
            : 'monthly';

        // Generate tenant ID (subdomain)
        $tenantId = Str::slug($validated['subdomain']);

        $requiresPayment = (float) $package->price > 0;
        $durationDays = (int) $package->duration_in_days * ($billingCycle === 'yearly' ? 12 : 1);
        $scheme = request()->isSecure() ? 'https' : 'http';
        $port = parse_url(config('app.url'), PHP_URL_PORT);

        // Create the tenant
        $tenant = Tenant::create([
            'id' => $tenantId,
            'owner_id' => $user->id,
            'store_name' => $validated['store_name'],
            'package_id' => $package->id,
            'plan_id' => null,
            'subscription_status' => $requiresPayment ? 'past_due' : 'active',
            'billing_cycle' => $billingCycle,
            'trial_ends_at' => null,
            'subscription_ends_at' => $requiresPayment ? null : now()->addDays($durationDays),
            'status' => $requiresPayment ? 'pending' : 'active',
            'expired_at' => $requiresPayment ? null : now()->addDays($durationDays),
        ]);

        // Create domain for tenant
        // Menambahkan prefix port pada URL bisa dilakukan jika di localhost,
        // tapi domain table hanya perlu domain-nya saja (tanpa port)
        $centralDomain = request()->getHost();
        if ($centralDomain === '127.0.0.1' || $centralDomain === 'localhost') {
            $centralDomain = env('CENTRAL_DOMAIN', 'onyx.127.0.0.1.nip.io');
        }
        $domain = $tenantId . '.' . $centralDomain;
        $tenant->domains()->create([
            'domain' => $domain,
        ]);

        // Create admin user in tenant database
        $tenant->run(function () use ($user) {
            \App\Models\User::create([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password, // Already hashed from central
                'email_verified_at' => now(),
                'role' => 'admin',
            ]);
        });

        $fullUrl = $scheme . '://' . $domain;
        if ($port && !in_array((int) $port, [80, 443], true)) {
            $fullUrl .= ':' . $port;
        }

        return [
            'store_name' => $validated['store_name'],
            'subdomain' => $tenantId,
            'domain' => $domain,
            'full_url' => $fullUrl,
            'plan_name' => $package->name,
            'status' => $tenant->status,
            'requires_payment' => $requiresPayment,
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'billing_cycle' => $billingCycle,
            'billing_checkout_endpoint' => route('api.billing.checkout'),
            'admin_email' => $user->email,
            'admin_note' => 'Use the same password as your central account',
        ];
    }
}

