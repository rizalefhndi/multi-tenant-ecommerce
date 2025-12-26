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
    <Head title="Informasi Toko" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('settings.index')" class="text-gray-500 hover:text-gray-700">
                    Pengaturan
                </Link>
                <span class="text-gray-400">/</span>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Informasi Toko
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Basic Info -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko</label>
                                <input 
                                    type="text" 
                                    v-model="form.store_name"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Nama toko Anda"
                                />
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                                <input 
                                    type="text" 
                                    v-model="form.store_tagline"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Slogan singkat toko"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                <textarea 
                                    v-model="form.store_description"
                                    rows="3"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Deskripsi lengkap tentang toko Anda"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontak</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input 
                                    type="email" 
                                    v-model="form.store_email"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="email@toko.com"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                                <input 
                                    type="text" 
                                    v-model="form.store_phone"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="08xxxxxxxxxx"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                <textarea 
                                    v-model="form.store_address"
                                    rows="2"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Alamat lengkap toko"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Media Sosial</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-pink-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                        Instagram
                                    </span>
                                </label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-500 text-sm">@</span>
                                    <input 
                                        type="text" 
                                        v-model="form.instagram"
                                        class="flex-1 border-gray-300 rounded-r-lg focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="username"
                                    />
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        Facebook
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    v-model="form.facebook"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="URL atau username"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        WhatsApp
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    v-model="form.whatsapp"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="628xxxxxxxxxx"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>
                                        TikTok
                                    </span>
                                </label>
                                <div class="flex">
                                    <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-500 text-sm">@</span>
                                    <input 
                                        type="text" 
                                        v-model="form.tiktok"
                                        class="flex-1 border-gray-300 rounded-r-lg focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="username"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
