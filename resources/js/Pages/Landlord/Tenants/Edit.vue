<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    tenant: Object,
});

const form = useForm({
    name: props.tenant.name,
    email: props.tenant.email,
});

const submit = () => {
    form.put(route('landlord.tenants.update', props.tenant.id));
};
</script>

<template>
    <Head title="Edit Node" />

    <AuthenticatedLayout>
        <div class="bg-black min-h-screen text-white font-sans selection:bg-white selection:text-black">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12 border-b border-zinc-800 pb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                             <span class="w-3 h-3 bg-zinc-500 rounded-full"></span>
                             <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Node Configuration</span>
                        </div>
                        <h2 class="text-4xl md:text-5xl font-black text-white tracking-tighter uppercase leading-[0.9]">
                            Reconfigure <br/>
                            <span class="text-zinc-500">Node</span>
                        </h2>
                    </div>
                    <div class="flex gap-4 items-center">
                        <Link
                            :href="route('landlord.tenants.show', tenant.id)"
                            class="text-xs font-bold text-zinc-500 hover:text-white uppercase tracking-widest transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Profile
                        </Link>
                    </div>
                </div>

                <div class="bg-zinc-900/30 border border-zinc-800">
                    <!-- Info Alert -->
                    <div class="p-4 border-b border-zinc-800 bg-yellow-500/10 flex items-start gap-4">
                        <svg class="h-5 w-5 text-yellow-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-[10px] font-bold text-yellow-500 uppercase tracking-widest leading-relaxed">
                                System Warning: Registration ID and Network Mappings are permanently locked post-initialization.
                            </p>
                        </div>
                    </div>

                    <div class="p-6 md:p-8">
                        <form @submit.prevent="submit" class="space-y-8">
                            
                            <!-- Tenant ID (Read-only) -->
                            <div class="space-y-2">
                                <label for="id" class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Registration ID (Locked)</label>
                                <input
                                    id="id"
                                    :value="tenant.id"
                                    type="text"
                                    class="w-full bg-black border border-zinc-800 text-zinc-500 text-sm font-mono px-4 py-3 cursor-not-allowed focus:ring-0 focus:border-zinc-800"
                                    disabled
                                />
                                <p class="text-[10px] text-zinc-600 font-mono tracking-wide">
                                    // Node identifier immutable
                                </p>
                            </div>

                            <!-- Tenant Name -->
                            <div class="space-y-2">
                                <label for="name" class="text-xs font-bold text-white uppercase tracking-widest">Node Label (Required)</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full bg-black border border-zinc-700 text-white text-sm font-bold tracking-wide px-4 py-3 placeholder:text-zinc-700 focus:ring-0 focus:border-white transition-colors"
                                    required
                                    autofocus
                                    placeholder="Enter node designation..."
                                />
                                <InputError class="mt-2 text-red-500 text-xs font-bold uppercase tracking-widest" :message="form.errors.name" />
                            </div>

                            <!-- Tenant Email -->
                            <div class="space-y-2">
                                <label for="email" class="text-xs font-bold text-white uppercase tracking-widest">Contact Relocator (Required)</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="w-full bg-black border border-zinc-700 text-white text-sm font-mono px-4 py-3 placeholder:text-zinc-700 focus:ring-0 focus:border-white transition-colors"
                                    required
                                    placeholder="admin@node.local"
                                />
                                <InputError class="mt-2 text-red-500 text-xs font-bold uppercase tracking-widest" :message="form.errors.email" />
                            </div>

                            <!-- Current Domains (Read-only) -->
                            <div class="space-y-4 pt-4 border-t border-zinc-800">
                                <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Active Network Mappings</label>
                                <div class="space-y-3">
                                    <div
                                        v-for="domain in tenant.domains"
                                        :key="domain.id"
                                        class="flex items-center gap-3 p-4 bg-black border border-zinc-800"
                                    >
                                        <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                        <span class="font-mono text-sm text-zinc-400">{{ domain.domain }}</span>
                                    </div>
                                    <div v-if="!tenant.domains || tenant.domains.length === 0" class="p-4 bg-black border border-zinc-800">
                                        <span class="font-mono text-sm text-zinc-500">No domains configured</span>
                                    </div>
                                </div>
                                <p class="text-[10px] text-zinc-600 font-mono tracking-wide">
                                    // Mappings are isolated; manage externally
                                </p>
                            </div>

                            <!-- Buttons -->
                            <div class="flex items-center justify-end gap-6 pt-8 border-t border-zinc-800">
                                <Link
                                    :href="route('landlord.tenants.show', tenant.id)"
                                    class="text-[10px] font-bold text-zinc-500 hover:text-white uppercase tracking-widest transition-colors"
                                >
                                    Abort
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-8 py-4 bg-white text-black text-xs font-black uppercase tracking-widest hover:bg-zinc-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors relative group"
                                >
                                    <span class="relative z-10">{{ form.processing ? 'Syncing...' : 'Commit Changes' }}</span>
                                    <div v-if="!form.processing" class="absolute inset-0 bg-white blur-md opacity-0 group-hover:opacity-50 transition-opacity"></div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
