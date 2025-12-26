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
    <Head title="Pengaturan Pengiriman" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('settings.index')" class="text-gray-500 hover:text-gray-700">
                    Pengaturan
                </Link>
                <span class="text-gray-400">/</span>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Pengiriman
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Origin City -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Kota Asal Pengiriman</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Pilih kota asal untuk kalkulasi ongkos kirim.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                                <select 
                                    v-model="selectedProvince"
                                    @change="loadCities"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    :disabled="loadingProvinces"
                                >
                                    <option value="">{{ loadingProvinces ? 'Memuat...' : 'Pilih Provinsi' }}</option>
                                    <option v-for="prov in provinces" :key="prov.id" :value="prov.id">
                                        {{ prov.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
                                <select 
                                    v-model="form.origin_city_id"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    :disabled="!selectedProvince || loadingCities"
                                >
                                    <option value="">{{ loadingCities ? 'Memuat...' : 'Pilih Kota' }}</option>
                                    <option v-for="city in cities" :key="city.id" :value="city.id">
                                        {{ city.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Enabled Couriers -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Kurir Tersedia</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Pilih kurir yang ingin ditampilkan saat checkout.
                        </p>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <label 
                                v-for="(courier, code) in couriers" 
                                :key="code"
                                class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition-colors"
                                :class="[
                                    form.enabled_couriers.includes(code) 
                                        ? 'border-indigo-500 bg-indigo-50' 
                                        : 'border-gray-200 hover:border-gray-300',
                                    !courier.enabled ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <input 
                                    type="checkbox" 
                                    :checked="form.enabled_couriers.includes(code)"
                                    @change="toggleCourier(code)"
                                    :disabled="!courier.enabled"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <div>
                                    <span class="text-sm font-medium text-gray-700">{{ courier.name }}</span>
                                    <span 
                                        v-if="!courier.enabled" 
                                        class="block text-xs text-gray-400"
                                    >
                                        (Tidak tersedia)
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Free Shipping -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Gratis Ongkir</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Minimal Belanja untuk Gratis Ongkir
                            </label>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500">Rp</span>
                                <input 
                                    type="number" 
                                    v-model="form.free_shipping_min_order"
                                    min="0"
                                    step="10000"
                                    class="w-full md:w-1/3 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                Set 0 untuk menonaktifkan gratis ongkir.
                                <span v-if="form.free_shipping_min_order > 0">
                                    (Gratis ongkir untuk belanja minimal Rp {{ formatCurrency(form.free_shipping_min_order) }})
                                </span>
                            </p>
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
