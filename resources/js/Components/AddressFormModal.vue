<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    address: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const isEditing = ref(!!props.address);

const form = useForm({
    label: props.address?.label || 'Home',
    recipient_name: props.address?.recipient_name || '',
    phone: props.address?.phone || '',
    address_line_1: props.address?.address_line_1 || '',
    address_line_2: props.address?.address_line_2 || '',
    city: props.address?.city || '',
    city_id: props.address?.city_id ? String(props.address.city_id) : '',
    province: props.address?.province || '',
    province_id: props.address?.province_id || '',
    postal_code: props.address?.postal_code || '',
    is_default: props.address?.is_default || false,
});

const labels = [
    { value: 'Home', label: '🏠 Rumah', icon: '🏠' },
    { value: 'Office', label: '🏢 Kantor', icon: '🏢' },
    { value: 'Other', label: '📍 Lainnya', icon: '📍' },
];

const searchQuery = ref('');
const destinations = ref([]);
const isSearching = ref(false);
const showDropdown = ref(false);
let searchTimeout = null;

// Initialize search query if editing
if (isEditing.value && props.address && props.address.city) {
    searchQuery.value = `${props.address.city}, ${props.address.province}`;
}

const onSearchInput = () => {
    form.city_id = '';
    form.city = '';
    form.province = '';
    
    if (searchQuery.value.length < 3) {
        destinations.value = [];
        showDropdown.value = false;
        return;
    }

    isSearching.value = true;
    showDropdown.value = true;

    if (searchTimeout) clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.get(route('api.shipping.search-destination', { q: searchQuery.value }));
            if (response.data.success) {
                destinations.value = response.data.data;
            }
        } catch (error) {
            console.error('Error fetching destinations:', error);
        } finally {
            isSearching.value = false;
        }
    }, 500);
};

const selectDestination = (dest) => {
    searchQuery.value = dest.label;
    form.city_id = String(dest.id);
    form.city = dest.district_name + ', ' + dest.city_name;
    form.province = dest.province_name;
    form.province_id = ''; // Not used in Komerce V2
    if (dest.zip_code) {
        form.postal_code = dest.zip_code;
    }
    showDropdown.value = false;
};

// Close dropdown when clicking outside
const closeDropdown = () => {
    setTimeout(() => {
        showDropdown.value = false;
    }, 200);
};

const submit = async () => {
    form.clearErrors();
    form.processing = true;

    try {
        if (isEditing.value && props.address) {
            // Jika ada endpoint update
            // (karena saat ini belum ada route api update, biarkan fallback ke inertia jika diperlukan atau panggil axios put)
            form.put(route('addresses.update', props.address.id), {
                preserveScroll: true,
                onSuccess: () => {
                    emit('saved', { ...form.data(), id: props.address.id });
                },
                onFinish: () => form.processing = false,
            });
        } else {
            const response = await axios.post(route('api.addresses.store'), form.data());
            if (response.data.success) {
                emit('saved', response.data.address);
                form.reset();
            }
        }
    } catch (error) {
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            for (const key in errors) {
                form.setError(key, errors[key][0]);
            }
        } else {
            console.error('Error saving address:', error);
            alert('Gagal menyimpan alamat. Silakan coba lagi.');
        }
    } finally {
        if (!isEditing.value) {
            form.processing = false;
        }
    }
};

const close = () => {
    form.clearErrors();
    form.reset();
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close" max-width="lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                {{ isEditing ? 'Edit Alamat' : 'Tambah Alamat Baru' }}
            </h3>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Label Selection -->
                <div>
                    <InputLabel value="Simpan Sebagai" />
                    <div class="mt-2 flex gap-3">
                        <button
                            v-for="labelOption in labels"
                            :key="labelOption.value"
                            type="button"
                            @click="form.label = labelOption.value"
                            class="flex-1 py-2 px-4 rounded-lg border-2 transition-colors text-sm font-medium"
                            :class="form.label === labelOption.value 
                                ? 'border-indigo-600 bg-indigo-50 text-indigo-700' 
                                : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                        >
                            {{ labelOption.label }}
                        </button>
                    </div>
                    <InputError :message="form.errors.label" class="mt-1" />
                </div>

                <!-- Recipient Name -->
                <div>
                    <InputLabel for="recipient_name" value="Nama Penerima" />
                    <TextInput
                        id="recipient_name"
                        v-model="form.recipient_name"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Nama lengkap penerima"
                        required
                    />
                    <InputError :message="form.errors.recipient_name" class="mt-1" />
                </div>

                <!-- Phone -->
                <div>
                    <InputLabel for="phone" value="Nomor Telepon" />
                    <TextInput
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="mt-1 block w-full"
                        placeholder="08xxxxxxxxxx"
                        required
                    />
                    <InputError :message="form.errors.phone" class="mt-1" />
                </div>

                <!-- Address Line 1 -->
                <div>
                    <InputLabel for="address_line_1" value="Alamat Lengkap" />
                    <textarea
                        id="address_line_1"
                        v-model="form.address_line_1"
                        rows="2"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="Nama jalan, nomor rumah, RT/RW"
                        required
                    ></textarea>
                    <InputError :message="form.errors.address_line_1" class="mt-1" />
                </div>

                <!-- Address Line 2 -->
                <div>
                    <InputLabel for="address_line_2" value="Detail Tambahan (Opsional)" />
                    <TextInput
                        id="address_line_2"
                        v-model="form.address_line_2"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Gedung, lantai, patokan"
                    />
                    <InputError :message="form.errors.address_line_2" class="mt-1" />
                </div>

                <!-- Destination Search -->
                <div class="relative">
                    <InputLabel for="destination" value="Kecamatan / Kota Tujuan" />
                    <TextInput
                        id="destination"
                        v-model="searchQuery"
                        @input="onSearchInput"
                        @blur="closeDropdown"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Ketik nama kecamatan atau kota..."
                        required
                    />
                    
                    <!-- Dropdown list -->
                    <div v-if="showDropdown" class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200 max-h-60 overflow-y-auto">
                        <div v-if="isSearching" class="p-3 text-sm text-gray-500 text-center">
                            Mencari...
                        </div>
                        <div v-else-if="destinations.length === 0" class="p-3 text-sm text-gray-500 text-center">
                            Tidak ditemukan.
                        </div>
                        <ul v-else class="py-1">
                            <li 
                                v-for="dest in destinations" 
                                :key="dest.id"
                                @click="selectDestination(dest)"
                                class="px-4 py-2 hover:bg-indigo-50 cursor-pointer text-sm"
                            >
                                <div class="font-medium text-gray-900">{{ dest.subdistrict_name }}, {{ dest.district_name }}</div>
                                <div class="text-gray-500 text-xs">{{ dest.city_name }}, {{ dest.province_name }} ({{ dest.zip_code }})</div>
                            </li>
                        </ul>
                    </div>
                    
                    <InputError :message="form.errors.city_id" class="mt-1" />
                    <p v-if="form.city_id" class="mt-1 text-xs text-green-600 font-medium">✓ Lokasi terpilih: {{ form.city }}, {{ form.province }}</p>
                </div>

                <!-- Postal Code -->
                <div>
                    <InputLabel for="postal_code" value="Kode Pos" />
                    <TextInput
                        id="postal_code"
                        v-model="form.postal_code"
                        type="text"
                        class="mt-1 block w-full sm:w-1/3"
                        placeholder="12345"
                        required
                        maxlength="5"
                    />
                    <InputError :message="form.errors.postal_code" class="mt-1" />
                </div>

                <!-- Set as Default -->
                <div class="flex items-center gap-2">
                    <input
                        id="is_default"
                        v-model="form.is_default"
                        type="checkbox"
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                    />
                    <label for="is_default" class="text-sm text-gray-700">
                        Jadikan alamat utama
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <SecondaryButton type="button" @click="close">
                        Batal
                    </SecondaryButton>
                    <PrimaryButton type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Update' : 'Simpan') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
