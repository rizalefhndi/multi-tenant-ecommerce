<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    tenant: Object,
});

const runMigrations = () => {
    if (confirm('Run migrations for this tenant?')) {
        router.post(route('landlord.tenants.migrate', props.tenant.id), {}, {
            preserveScroll: true,
        });
    }
};

const seedData = () => {
    if (confirm('Seed data for this tenant?')) {
        router.post(route('landlord.tenants.seed', props.tenant.id), {}, {
            preserveScroll: true,
        });
    }
};

const deleteTenant = () => {
    if (confirm(`Are you sure you want to delete tenant "${props.tenant.name}"? This will also delete the tenant's database!`)) {
        router.delete(route('landlord.tenants.destroy', props.tenant.id));
    }
};
</script>

<template>
    <Head :title="`Tenant: ${tenant.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tenant Details: {{ tenant.name }}
                </h2>
                <Link
                    :href="route('landlord.tenants.index')"
                    class="text-gray-600 hover:text-gray-800"
                >
                    ← Back to Tenants
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Tenant Info Card -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ tenant.name }}</h3>
                            <p class="text-gray-600">ID: <span class="font-mono">{{ tenant.id }}</span></p>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                :href="route('landlord.tenants.edit', tenant.id)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Edit
                            </Link>
                            <button
                                @click="deleteTenant"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Email</dt>
                            <dd class="text-base text-gray-900">{{ tenant.email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Created At</dt>
                            <dd class="text-base text-gray-900">{{ tenant.created_at }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Last Updated</dt>
                            <dd class="text-base text-gray-900">{{ tenant.updated_at }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Database Status</dt>
                            <dd>
                                <span :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    tenant.database_exists
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800'
                                ]">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <circle cx="10" cy="10" r="4" />
                                    </svg>
                                    {{ tenant.database_exists ? 'Active' : 'Not Found' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Database Info -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Information</h3>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Database Name:</dt>
                                <dd class="text-sm font-mono font-medium text-gray-900">{{ tenant.database_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Connection:</dt>
                                <dd class="text-sm font-medium text-gray-900">PostgreSQL</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Status:</dt>
                                <dd>
                                    <span :class="[
                                        'text-sm font-medium',
                                        tenant.database_exists ? 'text-green-600' : 'text-red-600'
                                    ]">
                                        {{ tenant.database_exists ? '✓ Connected' : '✗ Not Found' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Database Actions -->
                    <div class="mt-4 flex gap-3">
                        <button
                            @click="runMigrations"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Run Migrations
                        </button>
                        <button
                            @click="seedData"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Seed Data
                        </button>
                    </div>
                </div>

                <!-- Domains -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Domains</h3>
                    <div class="space-y-3">
                        <div
                            v-for="domain in tenant.domains"
                            :key="domain.id"
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                <span class="font-medium text-gray-900">{{ domain.domain }}</span>
                            </div>
                            <a
                                :href="`http://${domain.domain}:8000`"
                                target="_blank"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                            >
                                Open Store
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats (Optional) -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="text-sm text-blue-600 mb-1">Database Size</p>
                            <p class="text-2xl font-bold text-blue-900">-</p>
                            <p class="text-xs text-blue-600 mt-1">Coming soon</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="text-sm text-green-600 mb-1">Total Users</p>
                            <p class="text-2xl font-bold text-green-900">-</p>
                            <p class="text-xs text-green-600 mt-1">Coming soon</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <p class="text-sm text-purple-600 mb-1">Total Products</p>
                            <p class="text-2xl font-bold text-purple-900">-</p>
                            <p class="text-xs text-purple-600 mt-1">Coming soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
