<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    sections: Array,
});

const icons = {
    theme: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>`,
    store: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>`,
    payment: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>`,
    shipping: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>`,
};
</script>

<template>
    <Head>
        <title>Pengaturan</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8 relative overflow-hidden">
                    <div class="absolute top-4 right-4 onyx-kicker">Merchant Setup</div>
                    <p class="onyx-kicker">Settings</p>
                    <div class="mt-2 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                        <div>
                            <h1 class="onyx-title text-2xl md:text-4xl">Configure Your Store</h1>
                            <p class="mt-2 max-w-2xl text-black/65">
                                Atur identitas brand, operasional pengiriman, dan metode pembayaran dalam satu command center.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 w-full sm:w-auto">
                            <div class="onyx-panel-soft px-4 py-3 text-center min-w-[150px]">
                                <p class="onyx-kicker">Section</p>
                                <p class="mt-1 text-2xl font-bold">{{ sections.length }}</p>
                            </div>
                            <div class="onyx-panel-soft px-4 py-3 text-center min-w-[150px]">
                                <p class="onyx-kicker">Status</p>
                                <p class="mt-1 text-sm font-semibold uppercase tracking-[0.1em]">Ready</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="onyx-panel p-4 md:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <p class="onyx-kicker">Choose Section</p>
                        <p class="text-xs uppercase tracking-[0.12em] text-black/55">{{ sections.length }} modules available</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Link
                            v-for="section in sections"
                            :key="section.key"
                            :href="route('settings.' + section.key)"
                            class="onyx-panel-soft p-5 md:p-6 flex items-start gap-4 group transition-transform hover:-translate-y-0.5"
                        >
                            <div
                                class="w-12 h-12 shrink-0 border border-black bg-[#d8def7] text-[#3f46e8] flex items-center justify-center transition-colors group-hover:bg-black group-hover:text-white"
                                v-html="icons[section.key]"
                            ></div>

                            <div class="flex-1 min-w-0">
                                <h3 class="onyx-title text-base md:text-lg group-hover:underline underline-offset-4">
                                    {{ section.title }}
                                </h3>
                                <p class="mt-1 text-sm text-black/60 leading-relaxed">
                                    {{ section.description }}
                                </p>
                            </div>

                            <div class="self-center">
                                <svg class="w-5 h-5 text-black/40 transition-transform group-hover:translate-x-1 group-hover:text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
