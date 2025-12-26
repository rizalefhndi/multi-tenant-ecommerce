<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    order: Object,
    items: Array,
    transaction: Object,
});

// Animation state
const showConfetti = ref(true);

onMounted(() => {
    // Hide confetti after 5 seconds
    setTimeout(() => {
        showConfetti.value = false;
    }, 5000);
});

// Copy to clipboard
const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('Disalin ke clipboard!');
};
</script>

<template>
    <Head title="Pesanan Berhasil" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Card -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <!-- Success Header -->
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-12 text-center text-white relative overflow-hidden">
                        <!-- Confetti Animation -->
                        <div v-if="showConfetti" class="absolute inset-0 pointer-events-none">
                            <div class="confetti-piece" v-for="i in 20" :key="i" :style="{ left: `${i * 5}%`, animationDelay: `${i * 0.1}s` }"></div>
                        </div>

                        <!-- Success Icon -->
                        <div class="relative z-10">
                            <div class="mx-auto w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mb-4 animate-bounce-once">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h1 class="text-3xl font-bold mb-2">Pesanan Berhasil!</h1>
                            <p class="text-green-100">Terima kasih atas pesanan Anda</p>
                        </div>
                    </div>

                    <!-- Order Info -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Nomor Pesanan</p>
                                <p class="text-lg font-semibold text-gray-900 font-mono">{{ order.order_number }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm text-gray-500">Tanggal Pesanan</p>
                                <p class="text-gray-900">{{ order.created_at }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info (for bank transfer) -->
                    <div v-if="transaction && transaction.payment_method === 'bank_transfer'" class="p-6 bg-amber-50 border-b border-amber-100">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-amber-800">Menunggu Pembayaran</h4>
                                <p class="text-sm text-amber-700 mt-1">
                                    Silakan transfer ke rekening berikut sebelum <span class="font-medium">{{ transaction.expires_at }}</span>
                                </p>

                                <div v-if="transaction.bank_transfer_info" class="mt-4 bg-white rounded-lg p-4 border border-amber-200">
                                    <div class="space-y-3">
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
                                                <span class="font-bold text-lg text-indigo-600">{{ transaction.formatted_amount }}</span>
                                                <button 
                                                    @click="copyToClipboard(transaction.amount.toString())"
                                                    class="text-indigo-600 hover:text-indigo-800"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-xs text-amber-600 mt-3">
                                    * Transfer dengan jumlah yang tepat agar pembayaran terverifikasi lebih cepat
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="p-6 border-b border-gray-100">
                        <h4 class="font-semibold text-gray-900 mb-4">Item Pesanan ({{ items.length }})</h4>
                        <div class="space-y-3">
                            <div 
                                v-for="item in items" 
                                :key="item.id"
                                class="flex gap-4 p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="w-16 h-16 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                    <img 
                                        v-if="item.product_image"
                                        :src="item.product_image" 
                                        :alt="item.product_name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900">{{ item.product_name }}</p>
                                    <p class="text-sm text-gray-500">{{ item.quantity }}x {{ item.formatted_price }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-gray-900">{{ item.formatted_subtotal }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div v-if="order.shipping_address" class="p-6 border-b border-gray-100">
                        <h4 class="font-semibold text-gray-900 mb-2">Alamat Pengiriman</h4>
                        <div class="text-gray-600">
                            <p class="font-medium text-gray-900">{{ order.shipping_address.recipient_name }}</p>
                            <p>{{ order.shipping_address.phone }}</p>
                            <p class="mt-1">{{ order.shipping_address.full_address || order.shipping_address.address }}</p>
                        </div>
                    </div>

                    <!-- Price Summary -->
                    <div class="p-6 border-b border-gray-100 bg-gray-50">
                        <div class="space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>{{ order.formatted_subtotal }}</span>
                            </div>
                            <div v-if="order.shipping_cost > 0" class="flex justify-between text-gray-600">
                                <span>Ongkos Kirim</span>
                                <span>{{ order.formatted_shipping_cost }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-semibold text-gray-900 pt-2 border-t border-gray-200">
                                <span>Total</span>
                                <span class="text-indigo-600">{{ order.formatted_total }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-6 flex flex-col sm:flex-row gap-4">
                        <Link
                            :href="route('orders.show', order.id)"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat Pesanan
                        </Link>
                        <Link
                            :href="route('products.index')"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold rounded-xl transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            Lanjut Belanja
                        </Link>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-6 text-center text-sm text-gray-500">
                    <p>Ada pertanyaan? <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium">Hubungi kami</a></p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes bounce-once {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.animate-bounce-once {
    animation: bounce-once 0.5s ease-in-out;
}

@keyframes confetti-fall {
    0% {
        transform: translateY(-100%) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(400%) rotate(720deg);
        opacity: 0;
    }
}

.confetti-piece {
    position: absolute;
    width: 10px;
    height: 10px;
    top: -20px;
    background-color: hsl(calc(var(--i, 0) * 36), 80%, 60%);
    animation: confetti-fall 3s ease-in-out forwards;
}

.confetti-piece:nth-child(1) { --i: 1; background-color: #f43f5e; }
.confetti-piece:nth-child(2) { --i: 2; background-color: #f97316; }
.confetti-piece:nth-child(3) { --i: 3; background-color: #eab308; }
.confetti-piece:nth-child(4) { --i: 4; background-color: #22c55e; }
.confetti-piece:nth-child(5) { --i: 5; background-color: #3b82f6; }
.confetti-piece:nth-child(6) { --i: 6; background-color: #8b5cf6; }
.confetti-piece:nth-child(7) { --i: 7; background-color: #ec4899; }
.confetti-piece:nth-child(8) { --i: 8; background-color: #14b8a6; }
.confetti-piece:nth-child(9) { --i: 9; background-color: #f43f5e; }
.confetti-piece:nth-child(10) { --i: 10; background-color: #f97316; }
.confetti-piece:nth-child(11) { --i: 11; background-color: #eab308; }
.confetti-piece:nth-child(12) { --i: 12; background-color: #22c55e; }
.confetti-piece:nth-child(13) { --i: 13; background-color: #3b82f6; }
.confetti-piece:nth-child(14) { --i: 14; background-color: #8b5cf6; }
.confetti-piece:nth-child(15) { --i: 15; background-color: #ec4899; }
.confetti-piece:nth-child(16) { --i: 16; background-color: #14b8a6; }
.confetti-piece:nth-child(17) { --i: 17; background-color: #f43f5e; }
.confetti-piece:nth-child(18) { --i: 18; background-color: #f97316; }
.confetti-piece:nth-child(19) { --i: 19; background-color: #eab308; }
.confetti-piece:nth-child(20) { --i: 20; background-color: #22c55e; }
</style>
