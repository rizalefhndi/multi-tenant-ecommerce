<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import OrderStatusBadge from '@/Components/OrderStatusBadge.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    order: Object,
    items: Array,
    transaction: Object,
    statusTimeline: Array,
});

// Upload proof modal
const showUploadModal = ref(false);
const uploadForm = useForm({
    transfer_proof: null,
});
const previewUrl = ref(null);

// Handle file selection
const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        uploadForm.transfer_proof = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

// Submit upload proof
const submitProof = () => {
    uploadForm.post(route('orders.upload-proof', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            showUploadModal.value = false;
            uploadForm.reset();
            previewUrl.value = null;
        },
    });
};

// Cancel order
const cancelOrder = () => {
    if (confirm('Yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat dibatalkan.')) {
        router.post(route('orders.cancel', props.order.id));
    }
};

// Reorder
const reorder = () => {
    router.post(route('orders.reorder', props.order.id));
};

// Confirm received
const confirmReceived = () => {
    if (confirm('Konfirmasi bahwa pesanan sudah diterima?')) {
        router.post(route('orders.confirm-received', props.order.id));
    }
};

// Copy to clipboard
const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('Disalin ke clipboard!');
};

// Check if can upload proof
const canUploadProof = computed(() => {
    return props.order.status === 'pending_payment' 
        && props.transaction 
        && props.transaction.payment_method === 'bank_transfer'
        && !props.transaction.has_proof;
});

// Check if payment is waiting for verification
const waitingVerification = computed(() => {
    return props.transaction 
        && props.transaction.payment_method === 'bank_transfer'
        && props.transaction.has_proof
        && props.transaction.status === 'pending';
});
</script>

<template>
    <Head :title="`Pesanan ${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link 
                    :href="route('orders.index')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pesanan</h2>
                    <p class="text-sm text-gray-500 font-mono">{{ order.order_number }}</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Status Timeline -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <template v-for="(step, index) in statusTimeline" :key="step.status">
                            <!-- Step -->
                            <div class="flex flex-col items-center flex-1">
                                <div 
                                    class="w-10 h-10 rounded-full flex items-center justify-center mb-2"
                                    :class="step.is_completed || step.is_current 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-200 text-gray-400'"
                                >
                                    <svg v-if="step.is_completed" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else-if="step.icon === 'clock'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else-if="step.icon === 'check-circle'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else-if="step.icon === 'package'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m-8-4l8 4m8-4v10l-8 4m-8-4V7m8 4v10" />
                                    </svg>
                                    <svg v-else-if="step.icon === 'truck'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span 
                                    class="text-xs font-medium text-center"
                                    :class="step.is_completed || step.is_current ? 'text-indigo-600' : 'text-gray-400'"
                                >
                                    {{ step.label }}
                                </span>
                            </div>
                            <!-- Connector -->
                            <div 
                                v-if="index < statusTimeline.length - 1"
                                class="flex-1 h-0.5 mx-2"
                                :class="step.is_completed ? 'bg-indigo-600' : 'bg-gray-200'"
                            ></div>
                        </template>
                    </div>
                </div>

                <!-- Order Status & Actions -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <OrderStatusBadge :status="order.status" :label="order.status_label" size="lg" />
                            <p class="text-sm text-gray-500 mt-2">Dibuat pada {{ order.created_at }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <!-- Upload Proof Button -->
                            <button
                                v-if="canUploadProof"
                                @click="showUploadModal = true"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12" />
                                </svg>
                                Upload Bukti Transfer
                            </button>

                            <!-- Confirm Received Button -->
                            <button
                                v-if="order.status === 'shipped'"
                                @click="confirmReceived"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Pesanan Diterima
                            </button>

                            <!-- Reorder Button -->
                            <button
                                v-if="order.status === 'delivered' || order.status === 'cancelled'"
                                @click="reorder"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Pesan Lagi
                            </button>

                            <!-- Cancel Button -->
                            <button
                                v-if="order.can_cancel"
                                @click="cancelOrder"
                                class="px-4 py-2 border border-red-300 text-red-600 hover:bg-red-50 font-medium rounded-lg transition-colors flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Batalkan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Waiting Verification Notice -->
                <div v-if="waitingVerification" class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="font-medium text-amber-800">Menunggu Verifikasi</h4>
                            <p class="text-sm text-amber-700 mt-1">Bukti transfer sudah diupload. Mohon tunggu verifikasi dari admin.</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Info (Bank Transfer) -->
                <div v-if="transaction && transaction.payment_method === 'bank_transfer' && !transaction.has_proof && order.status === 'pending_payment'" 
                     class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
                    <h4 class="font-semibold text-amber-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Menunggu Pembayaran
                    </h4>

                    <p class="text-sm text-amber-700 mb-4">
                        Silakan transfer ke rekening berikut sebelum <span class="font-medium">{{ transaction.expires_at }}</span>
                    </p>

                    <div v-if="transaction.bank_transfer_info" class="bg-white rounded-lg p-4 border border-amber-200 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Bank</span>
                            <span class="font-medium text-gray-900">{{ transaction.bank_transfer_info.bank_name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Nomor Rekening</span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-medium text-gray-900">{{ transaction.bank_transfer_info.account_number }}</span>
                                <button 
                                    @click="copyToClipboard(transaction.bank_transfer_info.account_number)"
                                    class="text-indigo-600 hover:text-indigo-800"
                                    title="Salin"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Atas Nama</span>
                            <span class="font-medium text-gray-900">{{ transaction.bank_transfer_info.account_holder }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                            <span class="text-sm text-gray-600">Jumlah Transfer</span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xl text-indigo-600">{{ transaction.formatted_amount }}</span>
                                <button 
                                    @click="copyToClipboard(transaction.amount.toString())"
                                    class="text-indigo-600 hover:text-indigo-800"
                                    title="Salin"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900">Item Pesanan</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div 
                            v-for="item in items" 
                            :key="item.id"
                            class="p-4 flex gap-4"
                        >
                            <!-- Product Image -->
                            <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                <img 
                                    v-if="item.product_image"
                                    :src="item.product_image" 
                                    :alt="item.product_name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Item Info -->
                            <div class="flex-1 min-w-0">
                                <Link 
                                    v-if="item.product_id"
                                    :href="route('products.show', item.product_id)"
                                    class="font-medium text-gray-900 hover:text-indigo-600"
                                >
                                    {{ item.product_name }}
                                </Link>
                                <p v-else class="font-medium text-gray-900">{{ item.product_name }}</p>
                                <p v-if="item.product_sku" class="text-xs text-gray-400 font-mono mt-0.5">SKU: {{ item.product_sku }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ item.quantity }} x {{ item.formatted_price }}</p>
                            </div>

                            <!-- Item Subtotal -->
                            <div class="text-right">
                                <p class="font-medium text-gray-900">{{ item.formatted_subtotal }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary & Shipping -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Shipping Address -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            Alamat Pengiriman
                        </h3>
                        <div v-if="order.shipping_address" class="text-gray-600">
                            <p class="font-medium text-gray-900">{{ order.shipping_address.recipient_name }}</p>
                            <p class="text-sm">{{ order.shipping_address.phone }}</p>
                            <p class="text-sm mt-2">{{ order.shipping_address.full_address || order.shipping_address.address }}</p>
                        </div>

                        <!-- Tracking Info -->
                        <div v-if="order.shipping_tracking_number" class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-500">Nomor Resi</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="font-mono font-medium text-gray-900">{{ order.shipping_tracking_number }}</span>
                                <button 
                                    @click="copyToClipboard(order.shipping_tracking_number)"
                                    class="text-indigo-600 hover:text-indigo-800"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ order.shipping_courier }} {{ order.shipping_service }}</p>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Ringkasan Pembayaran
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>{{ order.formatted_subtotal }}</span>
                            </div>
                            <div v-if="order.shipping_cost > 0" class="flex justify-between text-gray-600">
                                <span>Ongkos Kirim</span>
                                <span>{{ order.formatted_shipping_cost }}</span>
                            </div>
                            <div v-if="order.discount > 0" class="flex justify-between text-green-600">
                                <span>Diskon</span>
                                <span>-Rp {{ order.discount.toLocaleString('id-ID') }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-semibold text-gray-900 pt-3 border-t border-gray-200">
                                <span>Total</span>
                                <span class="text-indigo-600">{{ order.formatted_total }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-500">Metode Pembayaran</p>
                            <p class="font-medium text-gray-900">{{ order.payment_method === 'bank_transfer' ? 'Transfer Bank Manual' : order.payment_method }}</p>
                            <p v-if="order.paid_at" class="text-sm text-green-600 mt-1">Dibayar pada {{ order.paid_at }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer Notes -->
                <div v-if="order.customer_notes" class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Catatan</h3>
                    <p class="text-gray-600">{{ order.customer_notes }}</p>
                </div>
            </div>
        </div>

        <!-- Upload Proof Modal -->
        <Modal :show="showUploadModal" @close="showUploadModal = false" max-width="md">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Bukti Transfer</h3>

                <form @submit.prevent="submitProof">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Bukti Transfer</label>
                        
                        <!-- Preview -->
                        <div v-if="previewUrl" class="mb-3">
                            <img :src="previewUrl" class="max-h-60 rounded-lg border border-gray-300 mx-auto" />
                        </div>

                        <!-- File Input -->
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm text-gray-500">
                                        <span class="font-medium text-indigo-600">Pilih file</span> atau drag & drop
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">PNG, JPG (max. 2MB)</p>
                                </div>
                                <input 
                                    type="file" 
                                    class="hidden" 
                                    accept="image/*"
                                    @change="handleFileSelect"
                                />
                            </label>
                        </div>
                    </div>

                    <div v-if="uploadForm.errors.transfer_proof" class="text-sm text-red-600 mb-4">
                        {{ uploadForm.errors.transfer_proof }}
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showUploadModal = false"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="!uploadForm.transfer_proof || uploadForm.processing"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition-colors"
                        >
                            {{ uploadForm.processing ? 'Mengupload...' : 'Upload' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
