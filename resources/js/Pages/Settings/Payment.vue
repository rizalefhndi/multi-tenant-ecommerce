<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
    paymentMethods: Object,
});

const form = useForm({
    enabled_payment_methods: props.settings.enabled_payment_methods || ['bank_transfer'],
    bank_name: props.settings.bank_name || '',
    bank_account_number: props.settings.bank_account_number || '',
    bank_account_holder: props.settings.bank_account_holder || '',
    midtrans_enabled: props.settings.midtrans_enabled || false,
});

const togglePaymentMethod = (method) => {
    const index = form.enabled_payment_methods.indexOf(method);
    if (index > -1) {
        form.enabled_payment_methods.splice(index, 1);
    } else {
        form.enabled_payment_methods.push(method);
    }
};

const submit = () => {
    form.post(route('settings.payment.update'), {
        preserveScroll: true,
    });
};

const bankList = [
    'BCA', 'BNI', 'BRI', 'Mandiri', 'CIMB Niaga', 
    'Permata', 'BTN', 'Danamon', 'OCBC NISP', 'Maybank'
];
</script>

<template>
    <Head title="Pengaturan Pembayaran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('settings.index')" class="text-gray-500 hover:text-gray-700">
                    Pengaturan
                </Link>
                <span class="text-gray-400">/</span>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Pembayaran
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                
                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- Payment Methods -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Metode Pembayaran Aktif</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <label 
                                v-for="(label, method) in paymentMethods" 
                                :key="method"
                                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-colors"
                                :class="form.enabled_payment_methods.includes(method) 
                                    ? 'border-indigo-500 bg-indigo-50' 
                                    : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input 
                                    type="checkbox" 
                                    :checked="form.enabled_payment_methods.includes(method)"
                                    @change="togglePaymentMethod(method)"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="text-sm font-medium text-gray-700">{{ label }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Bank Transfer Settings -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rekening Transfer Manual</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Rekening ini akan ditampilkan kepada pembeli untuk transfer manual.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bank</label>
                                <select 
                                    v-model="form.bank_name"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Pilih Bank</option>
                                    <option v-for="bank in bankList" :key="bank" :value="bank">
                                        {{ bank }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening</label>
                                <input 
                                    type="text" 
                                    v-model="form.bank_account_number"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="1234567890"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemilik</label>
                                <input 
                                    type="text" 
                                    v-model="form.bank_account_holder"
                                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Nama sesuai rekening"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Midtrans Settings -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Payment Gateway (Midtrans)</h3>
                                <p class="text-sm text-gray-500">
                                    Aktifkan untuk menerima pembayaran otomatis via VA, QRIS, e-Wallet
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    v-model="form.midtrans_enabled" 
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        <div v-if="form.midtrans_enabled" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-green-800">Midtrans Aktif</p>
                                    <p class="text-sm text-green-600">
                                        Payment gateway sudah dikonfigurasi. Pembeli bisa membayar via Virtual Account, QRIS, GoPay, ShopeePay, dan Kartu Kredit.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="text-sm text-gray-600">
                                Aktifkan Midtrans untuk menerima pembayaran otomatis. Hubungi admin untuk setup API key.
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
