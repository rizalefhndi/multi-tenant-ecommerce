<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    store_name: props.settings.store_name || '',
    store_description: props.settings.store_description || '',
    store_tagline: props.settings.store_tagline || '',
    store_email: props.settings.store_email || '',
    store_phone: props.settings.store_phone || '',
    store_address: props.settings.store_address || '',
    instagram: props.settings.instagram || '',
    facebook: props.settings.facebook || '',
    whatsapp: props.settings.whatsapp || '',
    twitter: props.settings.twitter || '',
    tiktok: props.settings.tiktok || '',
});

const submit = () => {
    form.post(route('settings.store.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head>
        <title>Informasi Toko</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <p class="onyx-kicker">Settings / Store</p>
                            <h1 class="onyx-title text-2xl md:text-4xl mt-2">Informasi Toko</h1>
                            <p class="mt-2 text-black/60">Kelola profil bisnis dan kanal komunikasi utama toko Anda.</p>
                        </div>
                        <Link :href="route('settings.index')" class="onyx-action-ghost w-full md:w-auto">Kembali ke Settings</Link>
                    </div>
                </section>

                <form @submit.prevent="submit" class="space-y-6">
                    <section class="onyx-panel p-5 md:p-6">
                        <p class="onyx-kicker mb-4">Informasi Dasar</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <p class="onyx-kicker mb-1.5 block">Nama Toko</p>
                                <input type="text" v-model="form.store_name" class="h-11 w-full border border-black bg-white px-3 text-sm" placeholder="Nama toko Anda" />
                            </div>

                            <div class="md:col-span-2">
                                <p class="onyx-kicker mb-1.5 block">Tagline</p>
                                <input type="text" v-model="form.store_tagline" class="h-11 w-full border border-black bg-white px-3 text-sm" placeholder="Slogan singkat toko" />
                            </div>

                            <div class="md:col-span-2">
                                <p class="onyx-kicker mb-1.5 block">Deskripsi</p>
                                <textarea v-model="form.store_description" rows="4" class="w-full border border-black bg-white px-3 py-2 text-sm" placeholder="Deskripsi lengkap tentang toko Anda"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="onyx-panel p-5 md:p-6">
                        <p class="onyx-kicker mb-4">Kontak</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="onyx-kicker mb-1.5 block">Email</p>
                                <input type="email" v-model="form.store_email" class="h-11 w-full border border-black bg-white px-3 text-sm" placeholder="email@toko.com" />
                            </div>

                            <div>
                                <p class="onyx-kicker mb-1.5 block">Telepon</p>
                                <input type="text" v-model="form.store_phone" class="h-11 w-full border border-black bg-white px-3 text-sm" placeholder="08xxxxxxxxxx" />
                            </div>

                            <div class="md:col-span-2">
                                <p class="onyx-kicker mb-1.5 block">Alamat</p>
                                <textarea v-model="form.store_address" rows="3" class="w-full border border-black bg-white px-3 py-2 text-sm" placeholder="Alamat lengkap toko"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="onyx-panel p-5 md:p-6">
                        <p class="onyx-kicker mb-4">Media Sosial</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="onyx-kicker mb-1.5 block">Instagram</p>
                                <div class="flex">
                                    <span class="h-11 inline-flex items-center border border-r-0 border-black bg-black px-3 text-xs font-semibold uppercase tracking-[0.08em] text-white">@</span>
                                    <input type="text" v-model="form.instagram" class="h-11 flex-1 border border-black bg-white px-3 text-sm" placeholder="username" />
                                </div>
                            </div>

                            <div>
                                <p class="onyx-kicker mb-1.5 block">Facebook</p>
                                <input type="text" v-model="form.facebook" class="h-11 w-full border border-black bg-white px-3 text-sm" placeholder="URL atau username" />
                            </div>

                            <div>
                                <p class="onyx-kicker mb-1.5 block">WhatsApp</p>
                                <input type="text" v-model="form.whatsapp" class="h-11 w-full border border-black bg-white px-3 text-sm" placeholder="628xxxxxxxxxx" />
                            </div>

                            <div>
                                <p class="onyx-kicker mb-1.5 block">Twitter / X</p>
                                <input type="text" v-model="form.twitter" class="h-11 w-full border border-black bg-white px-3 text-sm" placeholder="username" />
                            </div>

                            <div class="md:col-span-2">
                                <p class="onyx-kicker mb-1.5 block">TikTok</p>
                                <div class="flex">
                                    <span class="h-11 inline-flex items-center border border-r-0 border-black bg-black px-3 text-xs font-semibold uppercase tracking-[0.08em] text-white">@</span>
                                    <input type="text" v-model="form.tiktok" class="h-11 flex-1 border border-black bg-white px-3 text-sm" placeholder="username" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing" class="onyx-action disabled:opacity-60">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
