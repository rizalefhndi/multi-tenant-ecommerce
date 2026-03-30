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
    <Head>
        <title>Tampilan Toko</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <p class="onyx-kicker">Settings / Theme</p>
                            <h1 class="onyx-title text-2xl md:text-4xl mt-2">Tampilan Toko</h1>
                            <p class="mt-2 text-black/60">Atur identitas visual brand untuk pengalaman pelanggan yang konsisten.</p>
                        </div>
                        <Link :href="route('settings.index')" class="onyx-action-ghost w-full md:w-auto">Kembali ke Settings</Link>
                    </div>
                </section>

                <section class="onyx-panel p-5 md:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="onyx-kicker">Asset Upload</p>
                        <p class="text-xs uppercase tracking-[0.1em] text-black/55">Logo, favicon, banner</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <article class="onyx-panel-soft p-4 space-y-3">
                            <p class="onyx-kicker">Logo Toko</p>
                            <div class="border border-dashed border-black/40 bg-white p-4 min-h-[130px] flex items-center justify-center">
                                <img v-if="settings.logo_url" :src="settings.logo_url" alt="Logo" class="h-16 object-contain" />
                                <span v-else class="text-xs uppercase tracking-[0.12em] text-black/50">No Logo</span>
                            </div>
                            <input type="file" @change="logoFile = $event.target.files[0]" accept="image/*" class="block w-full border border-black/30 bg-white px-3 py-2 text-sm" />
                            <button v-if="logoFile" @click="uploadLogo" class="onyx-action w-full">Upload Logo</button>
                            <p class="text-xs text-black/50">Max 2MB, PNG/JPG/SVG</p>
                        </article>

                        <article class="onyx-panel-soft p-4 space-y-3">
                            <p class="onyx-kicker">Favicon</p>
                            <div class="border border-dashed border-black/40 bg-white p-4 min-h-[130px] flex items-center justify-center">
                                <img v-if="settings.favicon_url" :src="settings.favicon_url" alt="Favicon" class="h-10 w-10 object-contain" />
                                <span v-else class="text-xs uppercase tracking-[0.12em] text-black/50">No Favicon</span>
                            </div>
                            <input type="file" @change="faviconFile = $event.target.files[0]" accept=".png,.ico" class="block w-full border border-black/30 bg-white px-3 py-2 text-sm" />
                            <button v-if="faviconFile" @click="uploadFavicon" class="onyx-action w-full">Upload Favicon</button>
                            <p class="text-xs text-black/50">32x32, PNG/ICO</p>
                        </article>

                        <article class="onyx-panel-soft p-4 space-y-3">
                            <p class="onyx-kicker">Banner</p>
                            <div class="border border-dashed border-black/40 bg-white p-4 min-h-[130px] flex items-center justify-center">
                                <img v-if="settings.banner_url" :src="settings.banner_url" alt="Banner" class="h-16 w-full object-cover" />
                                <span v-else class="text-xs uppercase tracking-[0.12em] text-black/50">No Banner</span>
                            </div>
                            <input type="file" @change="bannerFile = $event.target.files[0]" accept="image/*" class="block w-full border border-black/30 bg-white px-3 py-2 text-sm" />
                            <button v-if="bannerFile" @click="uploadBanner" class="onyx-action w-full">Upload Banner</button>
                            <p class="text-xs text-black/50">Max 5MB, 1920x400</p>
                        </article>
                    </div>
                </section>

                <form @submit.prevent="submit" class="onyx-panel p-5 md:p-6 space-y-6">
                    <div class="flex items-center justify-between">
                        <p class="onyx-kicker">Theme System</p>
                        <span class="onyx-chip">Live Preview</span>
                    </div>

                    <div>
                        <p class="onyx-kicker mb-2">Preset Warna</p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="preset in presetColors"
                                :key="preset.name"
                                type="button"
                                @click="applyPreset(preset)"
                                class="border border-black bg-white px-3 py-2 text-xs font-semibold uppercase tracking-[0.08em] hover:bg-black hover:text-white transition-colors"
                            >
                                {{ preset.name }}
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <p class="onyx-kicker mb-1.5 block">Primer</p>
                            <div class="flex gap-2">
                                <input type="color" v-model="form.primary_color" class="h-10 w-12 border border-black bg-white p-1" />
                                <input type="text" v-model="form.primary_color" class="h-10 w-full border border-black bg-white px-2 text-xs" />
                            </div>
                        </div>

                        <div>
                            <p class="onyx-kicker mb-1.5 block">Sekunder</p>
                            <div class="flex gap-2">
                                <input type="color" v-model="form.secondary_color" class="h-10 w-12 border border-black bg-white p-1" />
                                <input type="text" v-model="form.secondary_color" class="h-10 w-full border border-black bg-white px-2 text-xs" />
                            </div>
                        </div>

                        <div>
                            <p class="onyx-kicker mb-1.5 block">Aksen</p>
                            <div class="flex gap-2">
                                <input type="color" v-model="form.accent_color" class="h-10 w-12 border border-black bg-white p-1" />
                                <input type="text" v-model="form.accent_color" class="h-10 w-full border border-black bg-white px-2 text-xs" />
                            </div>
                        </div>

                        <div>
                            <p class="onyx-kicker mb-1.5 block">Background</p>
                            <div class="flex gap-2">
                                <input type="color" v-model="form.background_color" class="h-10 w-12 border border-black bg-white p-1" />
                                <input type="text" v-model="form.background_color" class="h-10 w-full border border-black bg-white px-2 text-xs" />
                            </div>
                        </div>

                        <div>
                            <p class="onyx-kicker mb-1.5 block">Teks</p>
                            <div class="flex gap-2">
                                <input type="color" v-model="form.text_color" class="h-10 w-12 border border-black bg-white p-1" />
                                <input type="text" v-model="form.text_color" class="h-10 w-full border border-black bg-white px-2 text-xs" />
                            </div>
                        </div>
                    </div>

                    <div class="onyx-panel-soft p-4" :style="{ backgroundColor: form.background_color }">
                        <p class="onyx-kicker mb-2" :style="{ color: form.text_color }">Preview</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="border border-black px-4 py-2 text-xs font-semibold uppercase tracking-[0.08em] text-white" :style="{ backgroundColor: form.primary_color }">Primary</button>
                            <button type="button" class="border border-black px-4 py-2 text-xs font-semibold uppercase tracking-[0.08em] text-white" :style="{ backgroundColor: form.secondary_color }">Secondary</button>
                            <button type="button" class="border border-black px-4 py-2 text-xs font-semibold uppercase tracking-[0.08em] text-white" :style="{ backgroundColor: form.accent_color }">Accent</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="onyx-kicker mb-1.5 block">Font</p>
                            <select v-model="form.font_family" class="h-11 w-full border border-black bg-white px-3 text-sm">
                                <option v-for="(name, key) in fonts" :key="key" :value="key">{{ name }}</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <label class="flex items-center gap-3 border border-black bg-white px-3 py-3 w-full h-11">
                                <input type="checkbox" v-model="form.dark_mode" class="rounded border-black text-black focus:ring-0" />
                                <span class="text-sm font-medium uppercase tracking-[0.08em]">Aktifkan Dark Mode</span>
                            </label>
                        </div>
                    </div>

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
