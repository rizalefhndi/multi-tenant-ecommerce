<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
    fonts: Object,
});

const form = useForm({
    primary_color: props.settings.primary_color || '#6366f1',
    secondary_color: props.settings.secondary_color || '#8b5cf6',
    accent_color: props.settings.accent_color || '#f59e0b',
    background_color: props.settings.background_color || '#ffffff',
    text_color: props.settings.text_color || '#1f2937',
    font_family: props.settings.font_family || 'Inter',
    dark_mode: props.settings.dark_mode || false,
});

const logoFile = ref(null);
const faviconFile = ref(null);
const bannerFile = ref(null);

const submit = () => {
    form.post(route('settings.theme.update'), {
        preserveScroll: true,
    });
};

const uploadLogo = () => {
    if (!logoFile.value) return;
    
    const formData = new FormData();
    formData.append('logo', logoFile.value);
    
    router.post(route('settings.upload.logo'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            logoFile.value = null;
        },
    });
};

const uploadFavicon = () => {
    if (!faviconFile.value) return;
    
    const formData = new FormData();
    formData.append('favicon', faviconFile.value);
    
    router.post(route('settings.upload.favicon'), formData, {
        preserveScroll: true,
    });
};

const uploadBanner = () => {
    if (!bannerFile.value) return;
    
    const formData = new FormData();
    formData.append('banner', bannerFile.value);
    
    router.post(route('settings.upload.banner'), formData, {
        preserveScroll: true,
    });
};

const presetColors = [
    { name: 'Indigo', primary: '#6366f1', secondary: '#8b5cf6', accent: '#f59e0b' },
    { name: 'Blue', primary: '#3b82f6', secondary: '#06b6d4', accent: '#f97316' },
    { name: 'Green', primary: '#10b981', secondary: '#14b8a6', accent: '#eab308' },
    { name: 'Rose', primary: '#f43f5e', secondary: '#ec4899', accent: '#8b5cf6' },
    { name: 'Orange', primary: '#f97316', secondary: '#fb923c', accent: '#6366f1' },
];

const applyPreset = (preset) => {
    form.primary_color = preset.primary;
    form.secondary_color = preset.secondary;
    form.accent_color = preset.accent;
};
</script>

<template>
    <Head title="Tampilan Toko" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('settings.index')" class="text-gray-500 hover:text-gray-700">
                    Pengaturan
                </Link>
                <span class="text-gray-400">/</span>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Tampilan Toko
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Logo & Images -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Logo & Gambar</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Logo Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo Toko</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                <img 
                                    v-if="settings.logo_url" 
                                    :src="settings.logo_url" 
                                    alt="Logo" 
                                    class="h-16 mx-auto mb-2 object-contain"
                                />
                                <div v-else class="text-gray-400 mb-2">
                                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input 
                                    type="file" 
                                    @change="logoFile = $event.target.files[0]"
                                    accept="image/*"
                                    class="text-sm"
                                />
                                <button 
                                    v-if="logoFile"
                                    @click="uploadLogo"
                                    class="mt-2 px-3 py-1 bg-indigo-600 text-white text-sm rounded-lg"
                                >
                                    Upload
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Max 2MB, PNG/JPG/SVG</p>
                        </div>

                        <!-- Favicon Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Favicon</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                <img 
                                    v-if="settings.favicon_url" 
                                    :src="settings.favicon_url" 
                                    alt="Favicon" 
                                    class="h-8 w-8 mx-auto mb-2"
                                />
                                <div v-else class="text-gray-400 mb-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded mx-auto"></div>
                                </div>
                                <input 
                                    type="file" 
                                    @change="faviconFile = $event.target.files[0]"
                                    accept=".png,.ico"
                                    class="text-sm"
                                />
                                <button 
                                    v-if="faviconFile"
                                    @click="uploadFavicon"
                                    class="mt-2 px-3 py-1 bg-indigo-600 text-white text-sm rounded-lg"
                                >
                                    Upload
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">32x32, PNG/ICO</p>
                        </div>

                        <!-- Banner Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Banner</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                <img 
                                    v-if="settings.banner_url" 
                                    :src="settings.banner_url" 
                                    alt="Banner" 
                                    class="h-16 mx-auto mb-2 object-cover rounded"
                                />
                                <div v-else class="text-gray-400 mb-2">
                                    <div class="w-full h-12 bg-gray-200 rounded"></div>
                                </div>
                                <input 
                                    type="file" 
                                    @change="bannerFile = $event.target.files[0]"
                                    accept="image/*"
                                    class="text-sm"
                                />
                                <button 
                                    v-if="bannerFile"
                                    @click="uploadBanner"
                                    class="mt-2 px-3 py-1 bg-indigo-600 text-white text-sm rounded-lg"
                                >
                                    Upload
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Max 5MB, 1920x400</p>
                        </div>
                    </div>
                </div>

                <!-- Color Scheme -->
                <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Skema Warna</h3>

                    <!-- Presets -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preset Warna</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="preset in presetColors"
                                :key="preset.name"
                                type="button"
                                @click="applyPreset(preset)"
                                class="flex items-center gap-2 px-3 py-2 border rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                <div class="flex">
                                    <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: preset.primary }"></div>
                                    <div class="w-4 h-4 rounded-full -ml-1" :style="{ backgroundColor: preset.secondary }"></div>
                                    <div class="w-4 h-4 rounded-full -ml-1" :style="{ backgroundColor: preset.accent }"></div>
                                </div>
                                <span class="text-sm">{{ preset.name }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Color Pickers -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Primer</label>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="color" 
                                    v-model="form.primary_color"
                                    class="w-10 h-10 rounded cursor-pointer"
                                />
                                <input 
                                    type="text" 
                                    v-model="form.primary_color"
                                    class="flex-1 text-xs border-gray-300 rounded-lg"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sekunder</label>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="color" 
                                    v-model="form.secondary_color"
                                    class="w-10 h-10 rounded cursor-pointer"
                                />
                                <input 
                                    type="text" 
                                    v-model="form.secondary_color"
                                    class="flex-1 text-xs border-gray-300 rounded-lg"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Aksen</label>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="color" 
                                    v-model="form.accent_color"
                                    class="w-10 h-10 rounded cursor-pointer"
                                />
                                <input 
                                    type="text" 
                                    v-model="form.accent_color"
                                    class="flex-1 text-xs border-gray-300 rounded-lg"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Background</label>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="color" 
                                    v-model="form.background_color"
                                    class="w-10 h-10 rounded cursor-pointer"
                                />
                                <input 
                                    type="text" 
                                    v-model="form.background_color"
                                    class="flex-1 text-xs border-gray-300 rounded-lg"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teks</label>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="color" 
                                    v-model="form.text_color"
                                    class="w-10 h-10 rounded cursor-pointer"
                                />
                                <input 
                                    type="text" 
                                    v-model="form.text_color"
                                    class="flex-1 text-xs border-gray-300 rounded-lg"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="mb-6 p-4 rounded-lg" :style="{ backgroundColor: form.background_color }">
                        <p class="text-sm font-medium mb-2" :style="{ color: form.text_color }">Preview:</p>
                        <div class="flex gap-2">
                            <button 
                                type="button"
                                class="px-4 py-2 rounded-lg text-white text-sm font-medium"
                                :style="{ backgroundColor: form.primary_color }"
                            >
                                Tombol Primer
                            </button>
                            <button 
                                type="button"
                                class="px-4 py-2 rounded-lg text-white text-sm font-medium"
                                :style="{ backgroundColor: form.secondary_color }"
                            >
                                Tombol Sekunder
                            </button>
                            <button 
                                type="button"
                                class="px-4 py-2 rounded-lg text-white text-sm font-medium"
                                :style="{ backgroundColor: form.accent_color }"
                            >
                                Aksen
                            </button>
                        </div>
                    </div>

                    <!-- Font -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Font</label>
                        <select v-model="form.font_family" class="w-full md:w-1/3 border-gray-300 rounded-lg">
                            <option v-for="(name, key) in fonts" :key="key" :value="key">
                                {{ name }}
                            </option>
                        </select>
                    </div>

                    <!-- Dark Mode -->
                    <div class="mb-6">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" v-model="form.dark_mode" class="rounded border-gray-300 text-indigo-600">
                            <span class="text-sm font-medium text-gray-700">Aktifkan Dark Mode</span>
                        </label>
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
