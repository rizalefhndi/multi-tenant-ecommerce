<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    tenants: Object,
});

const deleteTenant = (tenant) => {
    if (confirm(`Are you sure you want to delete tenant "${tenant.name}"? This will also delete the tenant's database!`)) {
        router.delete(route('landlord.tenants.destroy', tenant.id));
    }
};

const runMigrations = (tenant) => {
    if (confirm(`Run migrations for tenant "${tenant.name}"?`)) {
        router.post(route('landlord.tenants.migrate', tenant.id), {}, {
            preserveScroll: true,
        });
    }
};

const seedData = (tenant) => {
    if (confirm(`Seed data for tenant "${tenant.name}"?`)) {
        router.post(route('landlord.tenants.seed', tenant.id), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Manage Tenants" />

    <AuthenticatedLayout>
        <div class="bg-black min-h-screen text-white font-sans selection:bg-white selection:text-black">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16 border-b border-zinc-800 pb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                             <span class="w-3 h-3 bg-zinc-500 rounded-full"></span>
                             <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Registry</span>
                        </div>
                        <h2 class="text-5xl md:text-6xl font-black text-white tracking-tighter uppercase leading-[0.9]">
                            Manage <br/> <span class="text-zinc-500">Tenants</span>
                        </h2>
                    </div>
                    <div class="flex gap-4">
                        <Link :href="route('landlord.tenants.create')" class="group relative px-8 py-4 bg-white text-black font-black uppercase tracking-widest text-sm rounded-none hover:bg-zinc-200 transition-colors">
                            <span class="relative z-10">Initialize Tenant</span>
                            <div class="absolute inset-0 bg-white blur-md opacity-0 group-hover:opacity-50 transition-opacity"></div>
                        </Link>
                    </div>
                </div>

                <div class="bg-zinc-900/30 border border-zinc-800">
                    <div class="p-6 border-b border-zinc-800 flex justify-between items-center bg-zinc-900/50">
                        <h3 class="text-lg font-black text-white uppercase tracking-widest flex items-center gap-3">
                            <span class="w-2 h-2 bg-white rounded-full"></span>
                            Tenant Archives
                        </h3>
                    </div>

                    <!-- Tenants Table -->
                    <div v-if="tenants.data.length > 0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-zinc-800 text-xs font-bold text-zinc-500 uppercase tracking-widest bg-zinc-900/80">
                                        <th class="p-4">Tenant</th>
                                        <th class="p-4">Domain</th>
                                        <th class="p-4">Email</th>
                                        <th class="p-4">Created At</th>
                                        <th class="p-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800">
                                    <tr v-for="tenant in tenants.data" :key="tenant.id" class="hover:bg-zinc-900 transition-colors group">
                                        <td class="p-4">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 bg-black border border-zinc-700 flex items-center justify-center text-white font-black text-sm group-hover:border-white transition-colors">
                                                    {{ tenant.id.substring(0, 2).toUpperCase() }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-white uppercase tracking-wide group-hover:underline decoration-2 underline-offset-4">
                                                        {{ tenant.store_name || tenant.name || 'Unknown Tenant' }}
                                                    </div>
                                                    <div class="text-xs text-zinc-500 font-mono mt-1">
                                                        ID: {{ tenant.id }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4">
                                            <div class="space-y-1">
                                                <a
                                                    v-for="domain in tenant.domains"
                                                    :key="domain.id"
                                                    :href="`http://${domain.domain}${window?.location?.port ? ':' + window.location.port : ':8000'}`"
                                                    target="_blank"
                                                    class="block text-xs font-bold text-zinc-400 hover:text-white uppercase tracking-widest transition-colors"
                                                >
                                                    {{ domain.domain }} ↗
                                                </a>
                                            </div>
                                        </td>
                                        <td class="p-4 text-xs font-mono text-zinc-400 tracking-wide">
                                            {{ tenant.email }}
                                        </td>
                                        <td class="p-4 text-xs font-mono text-zinc-500">
                                            {{ new Date(tenant.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="p-4 text-right">
                                            <div class="flex justify-end gap-3 items-center">
                                                <!-- View Details -->
                                                <Link
                                                    :href="route('landlord.tenants.show', tenant.id)"
                                                    class="text-zinc-500 hover:text-white transition-colors"
                                                    title="View Details"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </Link>

                                                <!-- Edit -->
                                                <Link
                                                    :href="route('landlord.tenants.edit', tenant.id)"
                                                    class="text-zinc-500 hover:text-blue-400 transition-colors"
                                                    title="Edit"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </Link>

                                                <!-- Migrate -->
                                                <button
                                                    @click="runMigrations(tenant)"
                                                    class="text-zinc-500 hover:text-green-400 transition-colors"
                                                    title="Run Migrations"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                </button>

                                                <!-- Seed -->
                                                <button
                                                    @click="seedData(tenant)"
                                                    class="text-zinc-500 hover:text-yellow-400 transition-colors"
                                                    title="Seed Data"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                    </svg>
                                                </button>

                                                <!-- Delete -->
                                                <button
                                                    @click="deleteTenant(tenant)"
                                                    class="text-zinc-500 hover:text-red-500 transition-colors"
                                                    title="Delete"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="px-6 py-4 border-t border-zinc-800 bg-zinc-900/50">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-bold text-zinc-500 uppercase tracking-widest">
                                    Showing {{ tenants.from }} to {{ tenants.to }} of {{ tenants.total }}
                                </div>
                                <div class="flex gap-2">
                                    <template v-for="link in tenants.links" :key="link.label">
                                        <Link
                                            v-if="link.url"
                                            :href="link.url"
                                            :class="[
                                                'px-3 py-1 text-xs font-bold uppercase tracking-widest border transition-colors',
                                                link.active
                                                    ? 'bg-white text-black border-white'
                                                    : 'bg-transparent text-zinc-500 border-zinc-700 hover:bg-zinc-800 hover:text-white hover:border-zinc-500'
                                            ]"
                                        >
                                            <span v-html="link.label"></span>
                                        </Link>
                                        <span
                                            v-else
                                            :class="[
                                                'px-3 py-1 text-xs font-bold uppercase tracking-widest border border-zinc-800 text-zinc-700 opacity-50 cursor-not-allowed'
                                            ]"
                                        >
                                            <span v-html="link.label"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-20 px-6">
                        <div class="w-24 h-24 bg-zinc-900 border border-zinc-800 rounded-full flex items-center justify-center mx-auto mb-8 animate-pulse">
                            <svg class="w-10 h-10 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        </div>
                        <h3 class="text-2xl font-black text-white uppercase tracking-widest mb-3">Void Detected</h3>
                        <p class="text-zinc-500 text-sm mb-8 font-medium max-w-sm mx-auto">No commercial entities found in the registry. Production lines are halted.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('landlord.tenants.create')"
                                class="px-10 py-5 bg-white text-black font-black uppercase tracking-widest text-sm hover:scale-105 transition-transform inline-block"
                            >
                                Initiate First Tenant
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
