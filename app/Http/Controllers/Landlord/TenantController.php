<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants
     */
    public function index(): Response
    {
        $tenants = Tenant::with('domains')
            ->latest()
            ->paginate(10);

        return Inertia::render('Landlord/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create(): Response
    {
        return Inertia::render('Landlord/Tenants/Create');
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'required|string|max:255|unique:tenants,id|alpha_dash',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'domain' => 'required|string|max:255|unique:domains,domain',
        ]);

        try {
            // Create tenant
            $tenant = Tenant::create([
                'id' => $validated['id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            // Create domain
            $tenant->domains()->create([
                'domain' => $validated['domain'],
            ]);

            // Run migrations untuk tenant baru
            \Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
            ]);

            // Optional: Seed data untuk tenant baru
            \Artisan::call('tenants:seed', [
                '--tenants' => [$tenant->id],
                '--class' => 'TenantUserSeeder',
            ]);

            return redirect()
                ->route('landlord.tenants.index')
                ->with('success', "Tenant '{$tenant->name}' created successfully!");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create tenant: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant): Response
    {
        $tenant->load('domains');

        // Get tenant database info
        $databaseName = "tenant{$tenant->id}";

        // Check if database exists
        $databaseExists = \DB::connection('pgsql')
            ->select("SELECT 1 FROM pg_database WHERE datname = ?", [$databaseName]);

        return Inertia::render('Landlord/Tenants/Show', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'created_at' => $tenant->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $tenant->updated_at->format('Y-m-d H:i:s'),
                'domains' => $tenant->domains,
                'database_name' => $databaseName,
                'database_exists' => !empty($databaseExists),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified tenant
     */
    public function edit(Tenant $tenant): Response
    {
        $tenant->load('domains');

        return Inertia::render('Landlord/Tenants/Edit', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $tenant->update($validated);

        return redirect()
            ->route('landlord.tenants.index')
            ->with('success', 'Tenant updated successfully!');
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        try {
            $tenantName = $tenant->name;

            // Delete tenant (akan trigger event untuk delete database)
            $tenant->delete();

            return redirect()
                ->route('landlord.tenants.index')
                ->with('success', "Tenant '{$tenantName}' deleted successfully!");

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete tenant: ' . $e->getMessage());
        }
    }

    /**
     * Run migrations for specific tenant
     */
    public function migrate(Tenant $tenant): RedirectResponse
    {
        try {
            \Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
            ]);

            return back()->with('success', 'Migrations run successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }

    /**
     * Seed data for specific tenant
     */
    public function seed(Tenant $tenant): RedirectResponse
    {
        try {
            \Artisan::call('tenants:seed', [
                '--tenants' => [$tenant->id],
            ]);

            return back()->with('success', 'Seeding completed successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Seeding failed: ' . $e->getMessage());
        }
    }

    /**
     * Suspend a tenant
     */
    public function suspend(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $tenant->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'suspended_reason' => $validated['reason'] ?? null,
            ]);

            return back()->with('success', "Tenant '{$tenant->id}' has been suspended.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to suspend tenant: ' . $e->getMessage());
        }
    }

    /**
     * Activate a suspended tenant
     */
    public function activate(Tenant $tenant): RedirectResponse
    {
        try {
            $tenant->update([
                'status' => 'active',
                'suspended_at' => null,
                'suspended_reason' => null,
            ]);

            return back()->with('success', "Tenant '{$tenant->id}' has been activated.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to activate tenant: ' . $e->getMessage());
        }
    }
}
