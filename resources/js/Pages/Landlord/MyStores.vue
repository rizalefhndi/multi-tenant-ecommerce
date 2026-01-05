<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stores: {
        type: Array,
        default: () => [],
    },
    auth: Object,
});

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-emerald-500',
        trial: 'bg-amber-500',
        suspended: 'bg-red-500',
        inactive: 'bg-gray-500',
    };
    return colors[status] || 'bg-gray-500';
};

const getSubscriptionLabel = (status) => {
    const labels = {
        active: 'Active',
        trial: 'Trial',
        suspended: 'Suspended',
        inactive: 'Inactive',
    };
    return labels[status] || status;
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
};
</script>

<template>
    <Head title="My Stores - ONYX" />

    <div class="min-h-screen bg-white text-black font-sans selection:bg-black selection:text-white">
        <!-- Navbar -->
        <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-black">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <Link href="/" class="flex-shrink-0">
                        <span class="font-black text-2xl tracking-widest uppercase">ONYX</span>
                    </Link>
                    <div class="flex items-center gap-6">
                        <Link 
                            href="/create-store" 
                            class="text-sm font-bold uppercase tracking-wider text-gray-600 hover:text-black transition-colors"
                        >
                            + New Store
                        </Link>
                        <span class="text-sm font-bold uppercase tracking-wider text-gray-500">
                            {{ auth.user.name }}
                        </span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pt-20">
            <div class="max-w-5xl mx-auto px-6 py-16">
                <!-- Header -->
                <div class="mb-12">
                    <div class="inline-flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 bg-black rounded-full"></span>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-500">Dashboard</span>
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl font-black uppercase tracking-tighter mb-4">
                        My Stores
                    </h1>
                    
                    <p class="text-lg font-medium text-gray-600">
                        Manage all your stores from one place.
                    </p>
                </div>

                <!-- Stores Grid -->
                <div v-if="stores.length > 0" class="space-y-6">
                    <div 
                        v-for="store in stores" 
                        :key="store.id"
                        class="group border-2 border-black bg-white hover:bg-gray-50 transition-colors"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <!-- Store Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-3">
                                        <!-- Store Icon -->
                                        <div class="w-12 h-12 bg-black flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        
                                        <div>
                                            <h2 class="text-xl font-black uppercase">{{ store.store_name }}</h2>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span 
                                                    :class="getStatusColor(store.subscription_status)"
                                                    class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white"
                                                >
                                                    {{ getSubscriptionLabel(store.subscription_status) }}
                                                </span>
                                                <span v-if="store.plan" class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                                    {{ store.plan.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- URL -->
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        <span class="font-mono">{{ store.domain }}</span>
                                        <button 
                                            @click="copyToClipboard(store.full_url)"
                                            class="p-1 hover:bg-gray-200 rounded transition-colors"
                                            title="Copy URL"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <p class="text-xs text-gray-400 mt-2">
                                        Created {{ store.created_at }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-col gap-2">
                                    <a 
                                        :href="store.full_url"
                                        target="_blank"
                                        class="px-6 py-3 bg-black text-white text-xs font-bold uppercase tracking-widest hover:scale-105 transition-transform text-center"
                                    >
                                        Visit Store →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-24 border-2 border-dashed border-gray-300">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black uppercase mb-2">No Stores Yet</h3>
                    <p class="text-gray-500 mb-8">Create your first store and start selling today.</p>
                    <Link 
                        href="/pricing"
                        class="inline-block px-8 py-4 bg-black text-white text-sm font-bold uppercase tracking-widest hover:scale-105 transition-transform"
                    >
                        Create Your First Store
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>
