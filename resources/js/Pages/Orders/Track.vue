<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import OrderStatusBadge from '@/Components/OrderStatusBadge.vue';

const props = defineProps({
    order: Object,
    tracking: Object,
});

// Copy to clipboard
const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('Disalin ke clipboard!');
};
</script>

<template>
    <Head :title="`Lacak Pesanan ${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link 
                    :href="route('orders.show', order.id)"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lacak Pesanan</h2>
                    <p class="text-sm text-gray-500 font-mono">{{ order.order_number }}</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <!-- Tracking Info -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <OrderStatusBadge :status="order.status" :label="order.status_label" size="lg" />
                        </div>

                        <!-- Shipping Info -->
                        <div v-if="order.shipping_tracking_number" class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Nomor Resi</p>
                                    <p class="font-mono font-medium text-gray-900 text-lg">{{ order.shipping_tracking_number }}</p>
                                </div>
                                <button 
                                    @click="copyToClipboard(order.shipping_tracking_number)"
                                    class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                    title="Salin nomor resi"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">
                                {{ order.shipping_courier }} {{ order.shipping_service }}
                            </p>
                        </div>
                    </div>

                    <!-- Tracking Timeline -->
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Riwayat Pengiriman</h3>

                        <!-- If tracking data available -->
                        <div v-if="tracking && tracking.history && tracking.history.length > 0" class="space-y-4">
                            <div 
                                v-for="(event, index) in tracking.history" 
                                :key="index"
                                class="flex gap-4"
                            >
                                <div class="relative flex flex-col items-center">
                                    <div 
                                        class="w-3 h-3 rounded-full"
                                        :class="index === 0 ? 'bg-indigo-600' : 'bg-gray-300'"
                                    ></div>
                                    <div 
                                        v-if="index < tracking.history.length - 1"
                                        class="w-0.5 flex-1 bg-gray-200 my-1"
                                    ></div>
                                </div>
                                <div class="flex-1 pb-4">
                                    <p 
                                        class="font-medium"
                                        :class="index === 0 ? 'text-gray-900' : 'text-gray-600'"
                                    >
                                        {{ event.description }}
                                    </p>
                                    <p class="text-sm text-gray-500">{{ event.date }}</p>
                                    <p v-if="event.location" class="text-sm text-gray-500">{{ event.location }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- No tracking data yet -->
                        <div v-else class="text-center py-8">
                            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <h4 class="text-gray-900 font-medium mb-1">Belum ada informasi tracking</h4>
                            <p class="text-sm text-gray-500">
                                Informasi tracking akan muncul setelah paket diproses oleh kurir
                            </p>
                        </div>
                    </div>

                    <!-- External Tracking Link -->
                    <div v-if="order.shipping_tracking_number" class="p-6 border-t border-gray-100 bg-gray-50">
                        <p class="text-sm text-gray-600 mb-3">Lacak melalui website kurir:</p>
                        <div class="flex flex-wrap gap-2">
                            <a 
                                v-if="order.shipping_courier?.toLowerCase().includes('jne')"
                                :href="`https://www.jne.co.id/id/tracking/trace?resi=${order.shipping_tracking_number}`"
                                target="_blank"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                Lacak di JNE
                            </a>
                            <a 
                                v-if="order.shipping_courier?.toLowerCase().includes('jnt') || order.shipping_courier?.toLowerCase().includes('j&t')"
                                :href="`https://www.jet.co.id/track?no=${order.shipping_tracking_number}`"
                                target="_blank"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                Lacak di J&T
                            </a>
                            <a 
                                v-if="order.shipping_courier?.toLowerCase().includes('sicepat')"
                                :href="`https://www.sicepat.com/checkAwb?awb=${order.shipping_tracking_number}`"
                                target="_blank"
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                Lacak di SiCepat
                            </a>
                            <a 
                                v-if="order.shipping_courier?.toLowerCase().includes('anteraja')"
                                :href="`https://anteraja.id/tracking/${order.shipping_tracking_number}`"
                                target="_blank"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                Lacak di AnterAja
                            </a>
                            <a 
                                href="https://cekresi.com/"
                                target="_blank"
                                class="px-4 py-2 border border-gray-300 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                            >
                                Cek Resi Lainnya
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-6 text-center">
                    <Link
                        :href="route('orders.show', order.id)"
                        class="text-indigo-600 hover:text-indigo-800 font-medium"
                    >
                        ← Kembali ke Detail Pesanan
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
