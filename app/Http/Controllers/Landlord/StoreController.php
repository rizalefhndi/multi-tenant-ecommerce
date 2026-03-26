<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Package;
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

        $package = Package::query()
            ->get()
            ->first(fn ($item) => Str::slug($item->name) === $packageSlug);

        if (!$package) {
            return redirect()->route('pricing');
        }

        return Inertia::render('Landlord/CreateStore', [
            // Keep prop name `plan` to avoid large frontend refactor.
            'plan' => $package ? [
                'id' => $package->id,
                'slug' => Str::slug($package->name),
                'name' => $package->name,
                'description' => $package->description,
                'price_monthly' => (float) $package->price,
                'formatted_price_monthly' => 'Rp ' . number_format((float) $package->price, 0, ',', '.'),
                'is_free' => (float) $package->price <= 0,
            ] : null,
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
        ]);

        // Convert to current web payload shape for shared creator method.
        $payload = [
            'store_name' => $validated['store_name'],
            'subdomain' => $validated['subdomain'],
            'plan_id' => $validated['package_id'],
        ];

        return response()->json($this->createTenantAndProvision($payload, Auth::user()));
    }

    private function createTenantAndProvision(array $validated, $user): array
    {
        $package = Package::findOrFail($validated['plan_id']);

        // Generate tenant ID (subdomain)
        $tenantId = Str::slug($validated['subdomain']);

        $requiresPayment = (float) $package->price > 0;
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
            'billing_cycle' => 'monthly',
            'trial_ends_at' => null,
            'subscription_ends_at' => $requiresPayment ? null : now()->addDays((int) $package->duration_in_days),
            'status' => $requiresPayment ? 'pending' : 'active',
            'expired_at' => $requiresPayment ? null : now()->addDays((int) $package->duration_in_days),
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
            'billing_checkout_endpoint' => route('api.billing.checkout'),
            'admin_email' => $user->email,
            'admin_note' => 'Use the same password as your central account',
        ];
    }
}

