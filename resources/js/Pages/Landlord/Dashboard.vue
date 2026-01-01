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
        <div class="py-12 bg-black min-h-screen text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-12">
                    <div>
                        <h2 class="text-4xl font-black text-white tracking-tighter uppercase mb-2">Build Your Empire</h2>
                        <p class="text-zinc-400 font-medium">Overview of your multi-tenant platform.</p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('landlord.tenants.create')" class="px-6 py-3 bg-white text-black font-black uppercase tracking-widest text-sm rounded-full hover:scale-105 transition-transform">
                            + New Tenant
                        </Link>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <!-- Total Tenants -->
                    <div class="relative group bg-zinc-900 border border-zinc-800 p-8 hover:border-white transition-colors duration-300">
                        <div class="absolute top-0 right-0 p-6 opacity-20">
                            <svg class="w-16 h-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-4">Total Tenants</p>
                            <p class="text-5xl font-black text-white">{{ stats.total_tenants }}</p>
                        </div>
                    </div>

                    <!-- Active Tenants -->
                    <div class="relative group bg-zinc-900 border border-zinc-800 p-8 hover:border-white transition-colors duration-300">
                        <div class="absolute top-0 right-0 p-6 opacity-20">
                             <svg class="w-16 h-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-4">Active Tenants</p>
                            <p class="text-5xl font-black text-white">{{ stats.active_tenants }}</p>
                        </div>
                    </div>

                    <!-- Total Domains -->
                    <div class="relative group bg-zinc-900 border border-zinc-800 p-8 hover:border-white transition-colors duration-300">
                        <div class="absolute top-0 right-0 p-6 opacity-20">
                            <svg class="w-16 h-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-4">Connected Domains</p>
                            <p class="text-5xl font-black text-white">{{ stats.total_domains }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Tenants Table -->
                <div class="bg-zinc-900 border border-zinc-800">
                    <div class="p-8 border-b border-zinc-800 flex justify-between items-center">
                        <h3 class="text-xl font-black text-white uppercase tracking-widest">Recent Deployments</h3>
                        <Link :href="route('landlord.tenants.index')" class="text-xs font-bold text-zinc-500 hover:text-white uppercase tracking-widest transition-colors">View All</Link>
                    </div>

                    <div v-if="recentTenants.length > 0" class="divide-y divide-zinc-800">
                        <div v-for="tenant in recentTenants" :key="tenant.id" class="p-6 hover:bg-zinc-800 transition-colors flex items-center justify-between group">
                            <div class="flex items-center gap-6">
                                <div class="w-12 h-12 bg-black border border-zinc-700 flex items-center justify-center text-white font-black text-lg">
                                    {{ tenant.name.charAt(0) }}
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white uppercase tracking-wide group-hover:underline decoration-2 underline-offset-4">{{ tenant.name }}</h4>
                                    <p class="text-sm text-zinc-500 font-mono">{{ tenant.email }}</p>
                                </div>
                            </div>
                            
                            <div class="hidden sm:flex items-center gap-4">
                                <span v-if="tenant.domains && tenant.domains.length > 0" class="text-xs font-bold bg-zinc-800 text-zinc-400 px-3 py-1 rounded-full uppercase tracking-wider">
                                    {{ tenant.domains[0].domain }}
                                </span>
                                 <a v-if="tenant.domains && tenant.domains.length > 0" :href="`http://${tenant.domains[0].domain}:8000`" target="_blank" class="px-4 py-2 border border-zinc-600 text-white text-xs font-bold uppercase tracking-widest hover:bg-white hover:text-black transition-colors">
                                    Visit
                                </a>
                                 <Link :href="route('landlord.tenants.show', tenant.id)" class="p-2 text-zinc-500 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else class="p-16 text-center">
                        <div class="w-20 h-20 bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        </div>
                        <h3 class="text-xl font-black text-white uppercase tracking-widest mb-2">No Activations Yet</h3>
                        <p class="text-zinc-500 text-sm mb-8 font-medium">Initialize your first tenant to begin operations.</p>
                        <Link :href="route('landlord.tenants.create')" class="px-8 py-4 bg-white text-black font-black uppercase tracking-widest text-sm rounded-full hover:scale-105 transition-transform">
                            Initialize Tenant
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
