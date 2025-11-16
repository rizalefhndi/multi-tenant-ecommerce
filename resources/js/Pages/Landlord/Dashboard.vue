<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentTenants: Array,
});
</script>

<template>
    <Head title="Landlord Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Landlord Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Total Tenants -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total Tenants</p>
                                <p class="text-3xl font-bold text-gray-900">{{ stats.total_tenants }}</p>
                            </div>
                            <div class="p-3 bg-indigo-100 rounded-full">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Active Tenants -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Active Tenants</p>
                                <p class="text-3xl font-bold text-green-600">{{ stats.active_tenants }}</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Domains -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total Domains</p>
                                <p class="text-3xl font-bold text-blue-600">{{ stats.total_domains }}</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <Link
                            :href="route('landlord.tenants.create')"
                            class="flex items-center gap-3 p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors"
                        >
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="font-medium text-gray-700">Create New Tenant</span>
                        </Link>

                        <Link
                            :href="route('landlord.tenants.index')"
                            class="flex items-center gap-3 p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors"
                        >
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <span class="font-medium text-gray-700">View All Tenants</span>
                        </Link>
                    </div>
                </div>

                <!-- Recent Tenants -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Tenants</h3>
                        <Link
                            :href="route('landlord.tenants.index')"
                            class="text-sm text-indigo-600 hover:text-indigo-800"
                        >
                            View All
                        </Link>
                    </div>

                    <div v-if="recentTenants.length > 0" class="space-y-3">
                        <div
                            v-for="tenant in recentTenants"
                            :key="tenant.id"
                            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                        >
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ tenant.name }}</h4>
                                <p class="text-sm text-gray-500">{{ tenant.email }}</p>
                                <div class="flex gap-2 mt-2">
                                    <span
                                        v-for="domain in tenant.domains"
                                        :key="domain.id"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded"
                                    >
                                        {{ domain.domain }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a
                                    :href="`http://${tenant.domains[0]?.domain}:8000`"
                                    target="_blank"
                                    class="px-3 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors"
                                >
                                    Open Store
                                </a>
                                <Link
                                    :href="route('landlord.tenants.show', tenant.id)"
                                    class="px-3 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                                >
                                    Details
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8 text-gray-500">
                        <p>No tenants created yet.</p>
                        <Link
                            :href="route('landlord.tenants.create')"
                            class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                        >
                            Create Your First Tenant
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
