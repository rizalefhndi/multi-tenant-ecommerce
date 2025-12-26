<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
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
    city_id: props.address?.city_id || '',
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

const submit = () => {
    if (isEditing.value && props.address) {
        form.put(route('addresses.update', props.address.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('saved', { ...form.data(), id: props.address.id });
            },
        });
    } else {
        form.post(route('addresses.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                // Get the newly created address from flash or just emit with form data
                emit('saved', form.data());
                form.reset();
            },
        });
    }
};

const close = () => {
    form.clearErrors();
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

                <!-- City & Province Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="city" value="Kota/Kabupaten" />
                        <TextInput
                            id="city"
                            v-model="form.city"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Nama kota"
                            required
                        />
                        <InputError :message="form.errors.city" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="province" value="Provinsi" />
                        <TextInput
                            id="province"
                            v-model="form.province"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Nama provinsi"
                            required
                        />
                        <InputError :message="form.errors.province" class="mt-1" />
                    </div>
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
