<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    tenant: Object,
});

const getTenantName = () => props.tenant.store_name || props.tenant.name || props.tenant.id;

const getPort = () => {
    return typeof window !== 'undefined' && window.location.port ? ':' + window.location.port : ':8000';
};

// Modal State
const showModal = ref(false);
const actionType = ref('');

const confirmAction = (type) => {
    actionType.value = type;
    showModal.value = true;
};

const executeAction = () => {
    const id = props.tenant.id;
    
    if (actionType.value === 'delete') {
        router.delete(route('landlord.tenants.destroy', id));
    } else if (actionType.value === 'migrate') {
        router.post(route('landlord.tenants.migrate', id), {}, { preserveScroll: true });
    } else if (actionType.value === 'seed') {
        router.post(route('landlord.tenants.seed', id), {}, { preserveScroll: true });
    }
    
    showModal.value = false;
    actionType.value = '';
};

const cancelAction = () => {
    showModal.value = false;
    actionType.value = '';
};
</script>

<template>
    <Head :title="`Tenant: ${tenant.name || tenant.id}`" />

    <AuthenticatedLayout>
        <div class="bg-black min-h-screen text-white font-sans selection:bg-white selection:text-black relative">
            
            <!-- Custom Confirmation Modal -->
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
                <div class="bg-zinc-900 border border-zinc-800 p-8 max-w-md w-full shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-white to-transparent opacity-50"></div>
                    
                    <h3 class="text-xl font-black text-white uppercase tracking-widest mb-4 flex items-center gap-3">
                        <svg v-if="actionType === 'delete'" class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <svg v-else class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <template v-if="actionType === 'delete'">System Purge</template>
                        <template v-else-if="actionType === 'migrate'">Execute Migrations</template>
                        <template v-else-if="actionType === 'seed'">Inject Genesis Data</template>
                    </h3>
                    
                    <p class="text-sm font-mono text-zinc-400 mb-8 leading-relaxed">
                        <template v-if="actionType === 'delete'">
                            WARNING: You are about to permanently delete the identity, network mappings, and entire database for node <span class="text-red-400 font-bold">"{{ getTenantName() }}"</span>. This sector will be unrecoverable. 
                            <br><br>Proceed with purge?
                        </template>
                        <template v-else-if="actionType === 'migrate'">
                            Initialize and cascade all database schema migrations for node <span class="text-white font-bold">"{{ getTenantName() }}"</span>?
                        </template>
                        <template v-else-if="actionType === 'seed'">
                            Inject initial system parameters and genesis data via seeders for node <span class="text-white font-bold">"{{ getTenantName() }}"</span>?
                        </template>
                    </p>

                    <div class="flex justify-end gap-4 border-t border-zinc-800 pt-6">
                        <button @click="cancelAction" class="px-6 py-3 border border-zinc-700 text-zinc-400 text-xs font-bold uppercase tracking-widest hover:bg-zinc-800 hover:text-white transition-colors">
                            Abort
                        </button>
                        <button @click="executeAction" :class="[
                            'px-6 py-3 text-xs font-black uppercase tracking-widest transition-colors',
                            actionType === 'delete' ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-white text-black hover:bg-zinc-300'
                        ]">
                            Confirm Execution
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16 border-b border-zinc-800 pb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                             <span class="w-3 h-3 bg-zinc-500 rounded-full"></span>
                             <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Node Inspector</span>
                        </div>
                        <h2 class="text-5xl md:text-6xl font-black text-white tracking-tighter uppercase leading-[0.9]">
                            {{ tenant.store_name || tenant.name || tenant.id }} <br/>
                            <span class="text-zinc-500 text-3xl">Profile</span>
                        </h2>
                    </div>
                    <div class="flex gap-4 items-center">
                        <Link
                            :href="route('landlord.tenants.index')"
                            class="text-xs font-bold text-zinc-500 hover:text-white uppercase tracking-widest transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Registry
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content Panel 1 -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Identity Card -->
                        <div class="bg-zinc-900/30 border border-zinc-800">
                            <div class="p-6 border-b border-zinc-800 flex justify-between items-center bg-zinc-900/50">
                                <h3 class="text-lg font-black text-white uppercase tracking-widest flex items-center gap-3">
                                    <span class="w-2 h-2 bg-white rounded-full"></span>
                                    Node Identity
                                </h3>
                                <div class="flex gap-3">
                                    <Link
                                        :href="route('landlord.tenants.edit', tenant.id)"
                                        class="px-4 py-2 border border-zinc-600 text-zinc-400 text-xs font-bold uppercase tracking-widest hover:bg-white hover:text-black hover:border-white transition-all"
                                    >
                                        Edit Node
                                    </Link>
                                    <button
                                        type="button"
                                        @click="confirmAction('delete')"
                                        class="px-4 py-2 bg-red-600 text-white text-xs font-bold uppercase tracking-widest hover:bg-red-700 cursor-pointer transition-colors"
                                    >
                                        Purge
                                    </button>
                                </div>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8 divide-y md:divide-y-0 md:divide-x divide-zinc-800">
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-1">Registration ID</p>
                                        <p class="font-mono text-white text-sm tracking-widest">{{ tenant.id }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-1">Contact Relocator</p>
                                        <p class="font-mono text-zinc-300 text-sm tracking-wide">{{ tenant.email || 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="space-y-4 pt-4 md:pt-0 md:pl-8">
                                    <div>
                                        <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-1">Genesis Date</p>
                                        <p class="font-mono text-zinc-300 text-sm">{{ new Date(tenant.created_at).toLocaleDateString() }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-1">Last Sync</p>
                                        <p class="font-mono text-zinc-300 text-sm">{{ new Date(tenant.updated_at).toLocaleDateString() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Database Info -->
                        <div class="bg-zinc-900/30 border border-zinc-800">
                            <div class="p-6 border-b border-zinc-800 flex justify-between items-center bg-zinc-900/50">
                                <h3 class="text-lg font-black text-white uppercase tracking-widest flex items-center gap-3">
                                    <span class="w-2 h-2 bg-zinc-500 rounded-full"></span>
                                    Infrastructure
                                </h3>
                                
                                <span :class="[
                                    'px-3 py-1 text-[10px] font-bold uppercase tracking-widest border',
                                    tenant.database_exists
                                        ? 'bg-green-500/10 text-green-500 border-green-500/20'
                                        : 'bg-red-500/10 text-red-500 border-red-500/20'
                                ]">
                                    {{ tenant.database_exists ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4 font-mono text-sm mb-8">
                                    <div class="flex justify-between items-center border-b border-zinc-800/50 pb-2">
                                        <span class="text-zinc-500 text-xs tracking-widest uppercase">Database Name</span>
                                        <span class="text-white">{{ tenant.database_name }}</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-zinc-800/50 pb-2">
                                        <span class="text-zinc-500 text-xs tracking-widest uppercase">Engine</span>
                                        <span class="text-zinc-300">PostgreSQL</span>
                                    </div>
                                    <div class="flex justify-between items-center pb-2">
                                        <span class="text-zinc-500 text-xs tracking-widest uppercase">State</span>
                                        <span :class="tenant.database_exists ? 'text-green-500' : 'text-red-500'">
                                            {{ tenant.database_exists ? 'Connected' : 'Missing' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex gap-4 flex-col sm:flex-row">
                                    <button
                                        type="button"
                                        @click="confirmAction('migrate')"
                                        class="flex-1 flex items-center justify-center gap-3 px-4 py-3 border border-zinc-700 bg-zinc-900 text-white text-xs font-bold uppercase tracking-widest hover:border-zinc-400 hover:text-green-400 cursor-pointer transition-all group"
                                    >
                                        <svg class="w-4 h-4 text-zinc-500 group-hover:text-green-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Execute Migrations
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmAction('seed')"
                                        class="flex-1 flex items-center justify-center gap-3 px-4 py-3 border border-zinc-700 bg-zinc-900 text-white text-xs font-bold uppercase tracking-widest hover:border-zinc-400 hover:text-yellow-400 cursor-pointer transition-all group"
                                    >
                                        <svg class="w-4 h-4 text-zinc-500 group-hover:text-yellow-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        Inject Genesis Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Side Panel -->
                    <div class="space-y-8">
                        <!-- Domains List -->
                        <div class="bg-zinc-900/30 border border-zinc-800">
                            <div class="p-6 border-b border-zinc-800 bg-zinc-900/50">
                                <h3 class="text-sm font-black text-white uppercase tracking-widest">Network Mappings</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div
                                        v-for="domain in tenant.domains"
                                        :key="domain.id"
                                        class="p-4 bg-black border border-zinc-800 group hover:border-zinc-600 transition-colors flex flex-col items-center"
                                    >
                                        <div class="flex items-center gap-3 mb-4 w-full justify-center">
                                            <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                            </svg>
                                            <span class="font-mono text-sm text-zinc-300 truncate max-w-full" :title="domain.domain">{{ domain.domain }}</span>
                                        </div>
                                        <a
                                            :href="`http://${domain.domain}${getPort()}`"
                                            target="_blank"
                                            class="block w-full text-center px-4 py-3 bg-white text-black text-xs font-black uppercase tracking-widest hover:bg-zinc-200 transition-colors"
                                        >
                                            Access Node Interface
                                        </a>
                                    </div>
                                    <div v-if="!tenant.domains || tenant.domains.length === 0" class="text-center py-6">
                                        <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">No Mappings Configured</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Mock -->
                        <div class="bg-zinc-900/30 border border-zinc-800">
                            <div class="p-6 border-b border-zinc-800 bg-zinc-900/50">
                                <h3 class="text-sm font-black text-white uppercase tracking-widest">Analytics Telemetry</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center border-b border-zinc-800/50 pb-3">
                                    <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Users Count</span>
                                    <span class="text-lg font-black font-mono text-zinc-300">-</span>
                                </div>
                                <div class="flex justify-between items-center border-b border-zinc-800/50 pb-3">
                                    <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Products Sync</span>
                                    <span class="text-lg font-black font-mono text-zinc-300">-</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Data Vol</span>
                                    <span class="text-lg font-black font-mono text-zinc-300">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
