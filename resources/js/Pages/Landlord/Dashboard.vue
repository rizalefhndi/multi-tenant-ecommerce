<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentTenants: Array,
});

const getTenantName = (tenant) => {
    return tenant.store_name || tenant.name || tenant.id || 'Unknown Tenant';
};

const getTenantInitials = (tenant) => {
    const name = getTenantName(tenant);
    return name.charAt(0).toUpperCase();
};

const getTenantUrl = (domainObj) => {
    if (!domainObj || !domainObj.domain) return '#';
    
    // In local development, we need to preserve the port (e.g. 8000)
    if (typeof window !== 'undefined' && window.location.port) {
        return `http://${domainObj.domain}:${window.location.port}`;
    }
    
    // Fallback if port isn't available but we suspect local env
    if (domainObj.domain.includes('localhost') || domainObj.domain.includes('127.0.0.1') || domainObj.domain.includes('.test') || domainObj.domain.includes('.local')) {
        return `http://${domainObj.domain}:8000`;
    }
    
    // Production / Default
    return `http://${domainObj.domain}`;
};
</script>

<template>
    <Head title="Command Center" />

    <AuthenticatedLayout>
        <div class="bg-black min-h-screen text-white font-sans selection:bg-white selection:text-black">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16 border-b border-zinc-800 pb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                             <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse shadow-[0_0_10px_rgba(34,197,94,0.5)]"></span>
                             <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">System Operational</span>
                        </div>
                        <h2 class="text-5xl md:text-6xl font-black text-white tracking-tighter uppercase leading-[0.9]">
                            Command <br/> <span class="text-zinc-500">Center</span>
                        </h2>
                    </div>
                    <div class="flex gap-4">
                        <Link :href="route('landlord.tenants.create')" class="group relative px-8 py-4 bg-white text-black font-black uppercase tracking-widest text-sm rounded-none hover:bg-zinc-200 transition-colors">
                            <span class="relative z-10">Initialize Tenant</span>
                            <div class="absolute inset-0 bg-white blur-md opacity-0 group-hover:opacity-50 transition-opacity"></div>
                        </Link>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <!-- Metric 1 -->
                    <div class="bg-zinc-900/50 border border-zinc-800 p-6 flex flex-col justify-between h-40 hover:border-zinc-600 transition-colors cursor-crosshair group">
                        <div class="flex justify-between items-start">
                            <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Total Realms</h3>
                            <svg class="w-5 h-5 text-zinc-700 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <div>
                            <span class="text-4xl font-black text-white tracking-tighter">{{ stats.total_tenants }}</span>
                            <span class="text-xs font-bold text-zinc-500 ml-2">/ UNLIMITED</span>
                        </div>
                    </div>

                    <!-- Metric 2 -->
                    <div class="bg-zinc-900/50 border border-zinc-800 p-6 flex flex-col justify-between h-40 hover:border-zinc-600 transition-colors cursor-crosshair group">
                        <div class="flex justify-between items-start">
                            <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Active Nodes</h3>
                            <svg class="w-5 h-5 text-zinc-700 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <div>
                            <span class="text-4xl font-black text-white tracking-tighter">{{ stats.active_tenants }}</span>
                             <span class="text-xs font-bold text-green-500 ml-2">ONLINE</span>
                        </div>
                    </div>

                    <!-- Metric 3 -->
                    <div class="bg-zinc-900/50 border border-zinc-800 p-6 flex flex-col justify-between h-40 hover:border-zinc-600 transition-colors cursor-crosshair group">
                        <div class="flex justify-between items-start">
                            <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Domains</h3>
                            <svg class="w-5 h-5 text-zinc-700 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                        </div>
                        <div>
                            <span class="text-4xl font-black text-white tracking-tighter">{{ stats.total_domains }}</span>
                             <span class="text-xs font-bold text-zinc-500 ml-2">MAPPED</span>
                        </div>
                    </div>

                     <!-- Metric 4 (Mock) -->
                    <div class="bg-zinc-900/50 border border-zinc-800 p-6 flex flex-col justify-between h-40 hover:border-zinc-600 transition-colors cursor-crosshair group">
                        <div class="flex justify-between items-start">
                            <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Platform MRR</h3>
                            <svg class="w-5 h-5 text-zinc-700 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <span class="text-4xl font-black text-white tracking-tighter">0.00</span>
                             <span class="text-xs font-bold text-zinc-500 ml-2">IDR</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                     <!-- Tenant List -->
                    <div class="lg:col-span-2 bg-zinc-900/30 border border-zinc-800">
                        <div class="p-6 border-b border-zinc-800 flex justify-between items-center bg-zinc-900/50">
                            <h3 class="text-lg font-black text-white uppercase tracking-widest flex items-center gap-3">
                                <span class="w-2 h-2 bg-white rounded-full"></span>
                                Recent Deployments
                            </h3>
                            <Link :href="route('landlord.tenants.index')" class="text-xs font-bold text-zinc-500 hover:text-white uppercase tracking-widest transition-colors">View All Archive</Link>
                        </div>

                        <div v-if="recentTenants.length > 0" class="divide-y divide-zinc-800">
                            <div v-for="tenant in recentTenants" :key="tenant.id" class="p-6 hover:bg-zinc-900 transition-colors flex items-center justify-between group">
                                <div class="flex items-center gap-6">
                                    <div class="w-12 h-12 bg-black border border-zinc-700 flex items-center justify-center text-white font-black text-lg group-hover:border-white transition-colors">
                                        {{ getTenantInitials(tenant) }}
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-white uppercase tracking-wide group-hover:underline decoration-2 underline-offset-4">{{ getTenantName(tenant) }}</h4>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                            <p class="text-xs text-zinc-500 font-mono tracking-wide">{{ tenant.email }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="hidden sm:flex items-center gap-4">
                                     <a v-if="tenant.domains && tenant.domains.length > 0" :href="getTenantUrl(tenant.domains[0])" target="_blank" class="px-4 py-2 border border-zinc-600 text-zinc-400 text-xs font-bold uppercase tracking-widest hover:bg-white hover:text-black hover:border-white transition-all">
                                        Access Node
                                    </a>
                                     <Link :href="route('landlord.tenants.show', tenant.id)" class="p-2 text-zinc-600 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div v-else class="p-20 text-center">
                            <div class="w-24 h-24 bg-zinc-900 border border-zinc-800 rounded-full flex items-center justify-center mx-auto mb-8 animate-pulse">
                                <svg class="w-10 h-10 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            </div>
                            <h3 class="text-2xl font-black text-white uppercase tracking-widest mb-3">Void Detected</h3>
                            <p class="text-zinc-500 text-sm mb-8 font-medium max-w-sm mx-auto">No commercial entities found in the registry. Production lines are halted.</p>
                            <Link :href="route('landlord.tenants.create')" class="px-10 py-5 bg-white text-black font-black uppercase tracking-widest text-sm hover:scale-105 transition-transform inline-block">
                                Initiate First Tenant
                            </Link>
                        </div>
                    </div>

                    <!-- Side Panel -->
                    <div class="space-y-8">
                         <!-- System Status Mock -->
                        <div class="bg-zinc-900/30 border border-zinc-800 p-6">
                            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-6 border-b border-zinc-800 pb-4">System Status</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-zinc-500 uppercase">Database</span>
                                    <span class="text-xs font-bold text-green-500 uppercase tracking-widest">Operational</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-zinc-500 uppercase">Storage</span>
                                    <span class="text-xs font-bold text-green-500 uppercase tracking-widest">Operational</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-zinc-500 uppercase">Queue</span>
                                    <span class="text-xs font-bold text-green-500 uppercase tracking-widest">Operational</span>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Log Mock -->
                        <div class="bg-zinc-900/30 border border-zinc-800 p-6">
                            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-6 border-b border-zinc-800 pb-4">Activity Log</h3>
                            <div class="space-y-6">
                                <div class="relative pl-6 border-l border-zinc-800">
                                    <span class="absolute -left-[5px] top-1 w-2.5 h-2.5 bg-zinc-600 rounded-full border-2 border-black"></span>
                                    <p class="text-xs text-zinc-300">System initialized successfully.</p>
                                    <p class="text-[10px] text-zinc-600 font-mono mt-1">JUST NOW</p>
                                </div>
                                <div class="relative pl-6 border-l border-zinc-800">
                                    <span class="absolute -left-[5px] top-1 w-2.5 h-2.5 bg-zinc-800 rounded-full border-2 border-black"></span>
                                    <p class="text-xs text-zinc-500">Super Admin login detected.</p>
                                    <p class="text-[10px] text-zinc-700 font-mono mt-1">2 MINS AGO</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
