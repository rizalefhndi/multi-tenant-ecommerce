<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const form = useForm({
    id: '',
    name: '',
    email: '',
    domain: '',
});

// Auto-generate domain from id
watch(() => form.id, (newId) => {
    if (newId && !form.domain) {
        // Automatically default to the nip.io wildcard format if that is what they use
        form.domain = `${newId}.127.0.0.1.nip.io`;
    }
});

const submit = () => {
    form.post(route('landlord.tenants.store'));
};
</script>

<template>
    <Head title="Initialize Node" />

    <AuthenticatedLayout>
        <div class="bg-black min-h-screen text-white font-sans selection:bg-white selection:text-black">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16 border-b border-zinc-800 pb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                             <span class="w-3 h-3 bg-white rounded-full animate-pulse"></span>
                             <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">System Initialization</span>
                        </div>
                        <h2 class="text-5xl md:text-6xl font-black text-white tracking-tighter uppercase leading-[0.9]">
                            Initialize <br/> <span class="text-zinc-500">Tenant Node</span>
                        </h2>
                    </div>
                    <div class="flex gap-4">
                        <Link
                            :href="route('landlord.tenants.index')"
                            class="text-xs font-bold text-zinc-500 hover:text-white uppercase tracking-widest transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Abort Initialization
                        </Link>
                    </div>
                </div>

                <div class="bg-zinc-900/30 border border-zinc-800 p-8 md:p-12 relative overflow-hidden">
                    <!-- Deco lines -->
                    <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-zinc-700 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-zinc-700 to-transparent"></div>

                    <!-- System Protocol Info -->
                    <div class="mb-10 p-6 border border-blue-500/20 bg-blue-500/5 relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                        <h4 class="text-sm font-black text-blue-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Auto-Provisioning Protocol
                        </h4>
                        <div class="text-xs font-mono text-blue-300/80 space-y-2">
                            <p>// The initialization sequence will execute the following commands:</p>
                            <p class="pl-4 text-white hover:text-blue-200 transition-colors">1. ALLOCATE PostgreSQL database instance</p>
                            <p class="pl-4 text-white hover:text-blue-200 transition-colors">2. EXECUTE core platform migrations</p>
                            <p class="pl-4 text-white hover:text-blue-200 transition-colors">3. INJECT genesis data (users + catalog)</p>
                            <p class="pl-4 text-white hover:text-blue-200 transition-colors">4. BIND network domain mapping</p>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Registration ID -->
                            <div class="space-y-3">
                                <label for="id" class="block text-[10px] font-bold text-zinc-500 uppercase tracking-widest">
                                    Registration ID <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="id"
                                    v-model="form.id"
                                    type="text"
                                    class="w-full bg-black border-zinc-700 text-white font-mono text-sm focus:border-white focus:ring-0 transition-colors placeholder:text-zinc-700 py-3 px-4"
                                    required
                                    autofocus
                                    placeholder="e.g. store1, renjana3"
                                />
                                <div v-if="form.errors.id" class="text-red-500 text-xs font-mono mt-1">{{ form.errors.id }}</div>
                                <p class="text-xs font-mono text-zinc-600">// Identifier used for db naming: tenant_{id}</p>
                            </div>

                            <!-- Display Label -->
                            <div class="space-y-3">
                                <label for="name" class="block text-[10px] font-bold text-zinc-500 uppercase tracking-widest">
                                    Display Label <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full bg-black border-zinc-700 text-white font-mono text-sm focus:border-white focus:ring-0 transition-colors placeholder:text-zinc-700 py-3 px-4"
                                    required
                                    placeholder="e.g. Streetwear Empire"
                                />
                                <div v-if="form.errors.name" class="text-red-500 text-xs font-mono mt-1">{{ form.errors.name }}</div>
                                <p class="text-xs font-mono text-zinc-600">// Commercial alias for the dashboard</p>
                            </div>

                            <!-- Contact Relocator -->
                            <div class="space-y-3">
                                <label for="email" class="block text-[10px] font-bold text-zinc-500 uppercase tracking-widest">
                                    Contact Relocator (Email) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="w-full bg-black border-zinc-700 text-white font-mono text-sm focus:border-white focus:ring-0 transition-colors placeholder:text-zinc-700 py-3 px-4"
                                    required
                                    placeholder="e.g. sysadmin@label.com"
                                />
                                <div v-if="form.errors.email" class="text-red-500 text-xs font-mono mt-1">{{ form.errors.email }}</div>
                                <p class="text-xs font-mono text-zinc-600">// Primary communication channel</p>
                            </div>

                            <!-- Network Mapping -->
                            <div class="space-y-3">
                                <label for="domain" class="block text-[10px] font-bold text-zinc-500 uppercase tracking-widest">
                                    Network Domain <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="domain"
                                    v-model="form.domain"
                                    type="text"
                                    class="w-full bg-black border-zinc-700 text-white font-mono text-sm focus:border-white focus:ring-0 transition-colors placeholder:text-zinc-700 py-3 px-4"
                                    required
                                    placeholder="e.g. store1.127.0.0.1.nip.io"
                                />
                                <div v-if="form.errors.domain" class="text-red-500 text-xs font-mono mt-1">{{ form.errors.domain }}</div>
                                <p class="text-xs font-mono text-zinc-600">// FQDN for the tenant portal</p>
                            </div>
                        </div>

                        <!-- DNS Config Warning -->
                        <div class="p-6 border border-yellow-500/20 bg-yellow-500/5 relative">
                            <div class="absolute top-0 left-0 w-1 h-full bg-yellow-500"></div>
                            <h4 class="text-sm font-black text-yellow-400 uppercase tracking-widest mb-2">DNS Routing Notice</h4>
                            <p class="text-xs font-mono text-zinc-400 mb-3">Ensure local resolution is valid if not using a public wildcard DNS.</p>
                            <code class="block font-mono text-xs bg-black border border-zinc-800 text-zinc-300 p-3 selection:bg-yellow-500 selection:text-black">
                                127.0.0.1    {{ form.domain || '{registration_id}.localhost' }}
                            </code>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row items-center justify-end gap-6 pt-8 border-t border-zinc-800">
                            <!-- Processing Indicator -->
                            <div v-if="form.processing" class="flex items-center gap-3 text-xs font-bold text-zinc-500 uppercase tracking-widest">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Bootstrapping Environment...
                            </div>
                            
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto px-10 py-4 bg-white text-black font-black uppercase tracking-widest text-sm hover:bg-zinc-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed group relative"
                            >
                                <span class="relative z-10">{{ form.processing ? 'Executing...' : 'Initialize Node' }}</span>
                                <div class="absolute inset-0 bg-white blur-md opacity-0 group-hover:opacity-50 transition-opacity"></div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
