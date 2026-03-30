<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    settings: Object,
    couriers: Object,
});

const form = useForm({
    origin_city_id: props.settings.origin_city_id || '',
    enabled_couriers: props.settings.enabled_couriers || ['jne', 'tiki', 'pos'],
    free_shipping_min_order: props.settings.free_shipping_min_order || 0,
});

const provinces = ref([]);
const cities = ref([]);
const selectedProvince = ref('');
const loadingProvinces = ref(false);
const loadingCities = ref(false);

// Load provinces on mount
onMounted(async () => {
    try {
        loadingProvinces.value = true;
        const response = await axios.get(route('api.shipping.provinces'));
        provinces.value = response.data.data;
    } catch (error) {
        console.error('Error loading provinces:', error);
    } finally {
        loadingProvinces.value = false;
    }
});

// Load cities when province changes
const loadCities = async () => {
    if (!selectedProvince.value) {
        cities.value = [];
        return;
    }

    try {
        loadingCities.value = true;
        const response = await axios.get(route('api.shipping.cities', { provinceId: selectedProvince.value }));
        cities.value = response.data.data;
    } catch (error) {
        console.error('Error loading cities:', error);
    } finally {
        loadingCities.value = false;
    }
};

const toggleCourier = (code) => {
    const index = form.enabled_couriers.indexOf(code);
    if (index > -1) {
        form.enabled_couriers.splice(index, 1);
    } else {
        form.enabled_couriers.push(code);
    }
};

const submit = () => {
    form.post(route('settings.shipping.update'), {
        preserveScroll: true,
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};
</script>

<template>
    <Head>
        <title>Pengaturan Pengiriman</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <p class="onyx-kicker">Settings / Shipping</p>
                            <h1 class="onyx-title text-2xl md:text-4xl mt-2">Pengiriman</h1>
                            <p class="mt-2 text-black/60">Konfigurasikan asal pengiriman, kurir aktif, dan skema gratis ongkir.</p>
                        </div>
                        <Link :href="route('settings.index')" class="onyx-action-ghost w-full md:w-auto">Kembali ke Settings</Link>
                    </div>
                </section>

                <form @submit.prevent="submit" class="space-y-6">
                    <section class="onyx-panel p-5 md:p-6">
                        <p class="onyx-kicker mb-4">Kota Asal Pengiriman</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="onyx-kicker mb-1.5 block">Provinsi</p>
                                <select
                                    v-model="selectedProvince"
                                    @change="loadCities"
                                    class="h-11 w-full border border-black bg-white px-3 text-sm"
                                    :disabled="loadingProvinces"
                                >
                                    <option value="">{{ loadingProvinces ? 'Memuat...' : 'Pilih Provinsi' }}</option>
                                    <option v-for="prov in provinces" :key="prov.id" :value="prov.id">{{ prov.name }}</option>
                                </select>
                            </div>

                            <div>
                                <p class="onyx-kicker mb-1.5 block">Kota</p>
                                <select
                                    v-model="form.origin_city_id"
                                    class="h-11 w-full border border-black bg-white px-3 text-sm"
                                    :disabled="!selectedProvince || loadingCities"
                                >
                                    <option value="">{{ loadingCities ? 'Memuat...' : 'Pilih Kota' }}</option>
                                    <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="onyx-panel p-5 md:p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <p class="onyx-kicker">Kurir Tersedia</p>
                            <span class="onyx-chip">{{ form.enabled_couriers.length }} aktif</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                            <label
                                v-for="(courier, code) in couriers"
                                :key="code"
                                class="flex items-center gap-3 p-3 border cursor-pointer transition-colors"
                                :class="[
                                    form.enabled_couriers.includes(code) ? 'border-black bg-black text-white' : 'border-black/30 bg-white hover:border-black',
                                    !courier.enabled ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <input
                                    type="checkbox"
                                    :checked="form.enabled_couriers.includes(code)"
                                    @change="toggleCourier(code)"
                                    :disabled="!courier.enabled"
                                    class="rounded border-black text-black focus:ring-0"
                                />
                                <div>
                                    <span class="text-sm font-semibold uppercase tracking-[0.06em]">{{ courier.name }}</span>
                                    <span v-if="!courier.enabled" class="block text-[11px] uppercase tracking-[0.08em]">Tidak tersedia</span>
                                </div>
                            </label>
                        </div>
                    </section>

                    <section class="onyx-panel p-5 md:p-6">
                        <p class="onyx-kicker mb-4">Gratis Ongkir</p>

                        <div class="grid grid-cols-1 md:grid-cols-[220px_1fr] gap-4 items-end">
                            <div>
                                <p class="onyx-kicker mb-1.5 block">Minimal Belanja</p>
                                <input
                                    type="number"
                                    v-model="form.free_shipping_min_order"
                                    min="0"
                                    step="10000"
                                    class="h-11 w-full border border-black bg-white px-3 text-sm"
                                />
                            </div>

                            <div class="onyx-panel-soft p-3">
                                <p class="text-sm text-black/70">
                                    Set nilai <span class="font-semibold text-black">0</span> untuk menonaktifkan gratis ongkir.
                                    <span v-if="form.free_shipping_min_order > 0" class="block mt-1 font-semibold text-black">
                                        Gratis ongkir aktif untuk belanja minimal Rp {{ formatCurrency(form.free_shipping_min_order) }}
                                    </span>
                                </p>
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
