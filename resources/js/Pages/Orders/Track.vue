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
    <Head>
        <title>Tracking {{ order.order_number }}</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-5 md:p-7">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <Link
                                :href="route('orders.show', order.id)"
                                class="h-10 w-10 border border-black bg-white text-black hover:bg-black hover:text-white transition-colors flex items-center justify-center"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </Link>
                            <div>
                                <p class="onyx-kicker">Order Tracking</p>
                                <h1 class="onyx-title text-2xl mt-2">Lacak Pesanan</h1>
                                <p class="text-sm text-black/60 font-mono mt-2">{{ order.order_number }}</p>
                            </div>
                        </div>
                        <OrderStatusBadge :status="order.status" :label="order.status_label" size="lg" />
                    </div>
                </section>

                <section class="onyx-panel overflow-hidden">
                    <div class="p-6 border-b border-black/10">
                        <div class="flex items-center justify-between mb-4">
                            <p class="onyx-kicker">Informasi Pengiriman</p>
                        </div>

                        <div v-if="order.shipping_tracking_number" class="bg-black/[0.03] border border-black/10 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.1em] text-black/55">Nomor Resi</p>
                                    <p class="font-mono font-medium text-black text-lg">{{ order.shipping_tracking_number }}</p>
                                </div>
                                <button
                                    @click="copyToClipboard(order.shipping_tracking_number)"
                                    class="h-9 w-9 border border-black text-black hover:bg-black hover:text-white transition-colors flex items-center justify-center"
                                    title="Salin nomor resi"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-black/65 mt-2">
                                {{ order.shipping_courier }} {{ order.shipping_service }}
                            </p>
                        </div>
                    </div>

                    <div class="p-6">
                        <h3 class="onyx-title text-sm mb-4">Riwayat Pengiriman</h3>

                        <div v-if="tracking && tracking.history && tracking.history.length > 0" class="space-y-4">
                            <div
                                v-for="(event, index) in tracking.history"
                                :key="index"
                                class="flex gap-4"
                            >
                                <div class="relative flex flex-col items-center">
                                    <div
                                        class="w-3 h-3 rounded-full border"
                                        :class="index === 0 ? 'bg-black border-black' : 'bg-white border-black/40'"
                                    ></div>
                                    <div
                                        v-if="index < tracking.history.length - 1"
                                        class="w-0.5 flex-1 bg-black/20 my-1"
                                    ></div>
                                </div>
                                <div class="flex-1 pb-4">
                                    <p
                                        class="font-medium"
                                        :class="index === 0 ? 'text-black' : 'text-black/70'"
                                    >
                                        {{ event.description }}
                                    </p>
                                    <p class="text-sm text-black/55">{{ event.date }}</p>
                                    <p v-if="event.location" class="text-sm text-black/55">{{ event.location }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-8">
                            <svg class="mx-auto h-16 w-16 text-black/35 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <h4 class="text-black font-medium mb-1 uppercase tracking-[0.08em]">Belum ada informasi tracking</h4>
                            <p class="text-sm text-black/55">
                                Informasi tracking akan muncul setelah paket diproses oleh kurir
                            </p>
                        </div>
                    </div>

                    <div v-if="order.shipping_tracking_number" class="p-6 border-t border-black/10 bg-black/[0.03]">
                        <p class="text-sm text-black/65 mb-3">Lacak melalui website kurir:</p>
                        <div class="flex flex-wrap gap-2">
                            <a
                                v-if="order.shipping_courier?.toLowerCase().includes('jne')"
                                :href="`https://www.jne.co.id/id/tracking/trace?resi=${order.shipping_tracking_number}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border border-red-900 bg-red-700 text-white hover:bg-red-800 transition-colors"
                            >
                                Lacak di JNE
                            </a>
                            <a
                                v-if="order.shipping_courier?.toLowerCase().includes('jnt') || order.shipping_courier?.toLowerCase().includes('j&t')"
                                :href="`https://www.jet.co.id/track?no=${order.shipping_tracking_number}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border border-red-900 bg-red-700 text-white hover:bg-red-800 transition-colors"
                            >
                                Lacak di J&T
                            </a>
                            <a
                                v-if="order.shipping_courier?.toLowerCase().includes('sicepat')"
                                :href="`https://www.sicepat.com/checkAwb?awb=${order.shipping_tracking_number}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border border-orange-900 bg-orange-700 text-white hover:bg-orange-800 transition-colors"
                            >
                                Lacak di SiCepat
                            </a>
                            <a
                                v-if="order.shipping_courier?.toLowerCase().includes('anteraja')"
                                :href="`https://anteraja.id/tracking/${order.shipping_tracking_number}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border border-sky-900 bg-sky-700 text-white hover:bg-sky-800 transition-colors"
                            >
                                Lacak di AnterAja
                            </a>
                            <a
                                href="https://cekresi.com/"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border border-black bg-white text-black hover:bg-black hover:text-white transition-colors"
                            >
                                Cek Resi Lainnya
                            </a>
                        </div>
                    </div>
                </section>

                <div class="text-center">
                    <Link
                        :href="route('orders.show', order.id)"
                        class="onyx-action-ghost"
                    >
                        ← Kembali ke Detail Pesanan
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
