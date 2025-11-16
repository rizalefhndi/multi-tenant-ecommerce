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
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Tenants</h2>
                <Link
                    :href="route('landlord.tenants.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors"
                >
                    Create New Tenant
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <!-- Tenants Table -->
                    <div v-if="tenants.data.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tenant
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Domain
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Created At
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="tenant in tenants.data" :key="tenant.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <span class="text-indigo-600 font-semibold text-sm">
                                                    {{ tenant.id.substring(0, 2).toUpperCase() }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ tenant.name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ID: {{ tenant.id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <a
                                                v-for="domain in tenant.domains"
                                                :key="domain.id"
                                                :href="`http://${domain.domain}:8000`"
                                                target="_blank"
                                                class="block text-sm text-blue-600 hover:text-blue-800"
                                            >
                                                {{ domain.domain }} ↗
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ tenant.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(tenant.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <!-- View Details -->
                                            <Link
                                                :href="route('landlord.tenants.show', tenant.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                title="View Details"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </Link>

                                            <!-- Edit -->
                                            <Link
                                                :href="route('landlord.tenants.edit', tenant.id)"
                                                class="text-blue-600 hover:text-blue-900"
                                                title="Edit"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </Link>

                                            <!-- Migrate -->
                                            <button
                                                @click="runMigrations(tenant)"
                                                class="text-green-600 hover:text-green-900"
                                                title="Run Migrations"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </button>

                                            <!-- Seed -->
                                            <button
                                                @click="seedData(tenant)"
                                                class="text-yellow-600 hover:text-yellow-900"
                                                title="Seed Data"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                </svg>
                                            </button>

                                            <!-- Delete -->
                                            <button
                                                @click="deleteTenant(tenant)"
                                                class="text-red-600 hover:text-red-900"
                                                title="Delete"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Showing {{ tenants.from }} to {{ tenants.to }} of {{ tenants.total }} tenants
                                </div>
                                <div class="flex gap-2">
                                    <template v-for="link in tenants.links" :key="link.label">
                                        <Link
                                            v-if="link.url"
                                            :href="link.url"
                                            :class="[
                                                'px-3 py-2 text-sm rounded-lg transition-colors',
                                                link.active
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                                            ]"
                                        >
                                            <span v-html="link.label"></span>
                                        </Link>
                                        <span
                                            v-else
                                            :class="[
                                                'px-3 py-2 text-sm rounded-lg transition-colors',
                                                'bg-white text-gray-400 border border-gray-300 opacity-50 cursor-not-allowed'
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
                    <div v-else class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No tenants</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new tenant.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('landlord.tenants.create')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Tenant
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
