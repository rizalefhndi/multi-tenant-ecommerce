<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import OrderStatusBadge from '@/Components/OrderStatusBadge.vue';

const props = defineProps({
    orders: Object,
    statuses: Object,
    currentStatus: String,
});

const selectedStatus = ref(props.currentStatus);

// Watch for filter changes
watch(selectedStatus, (newStatus) => {
    router.get(route('orders.index'), { status: newStatus }, {
        preserveState: true,
        replace: true,
    });
});

// Check if orders exist
const hasOrders = computed(() => {
    return props.orders.data && props.orders.data.length > 0;
});

// Get status counts (if available from backend)
const getStatusLabel = (status) => {
    return props.statuses[status] || status;
};
</script>

<template>
    <Head>
        <title>Orders</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-5 md:p-7">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="onyx-kicker">Order Center</p>
                            <h1 class="onyx-title text-2xl md:text-3xl mt-2">My Orders</h1>
                            <p class="text-black/60 mt-2">Track payment, shipping, and delivery progress in one place.</p>
                        </div>
                        <span class="onyx-chip">{{ orders.total || 0 }} Total Orders</span>
                    </div>
                </section>

                <section class="onyx-panel p-0 overflow-hidden">
                    <div class="border-b border-black/15">
                        <nav class="flex overflow-x-auto" aria-label="Tabs">
                            <button
                                @click="selectedStatus = 'all'"
                                class="whitespace-nowrap py-3 px-5 border-r border-black/10 text-xs font-semibold uppercase tracking-[0.1em] transition-colors"
                                :class="selectedStatus === 'all'
                                    ? 'bg-black text-white'
                                    : 'bg-white text-black hover:bg-black hover:text-white'"
                            >
                                All
                            </button>
                            <button
                                v-for="(label, status) in statuses"
                                :key="status"
                                @click="selectedStatus = status"
                                class="whitespace-nowrap py-3 px-5 border-r border-black/10 text-xs font-semibold uppercase tracking-[0.1em] transition-colors"
                                :class="selectedStatus === status
                                    ? 'bg-black text-white'
                                    : 'bg-white text-black hover:bg-black hover:text-white'"
                            >
                                {{ label }}
                            </button>
                        </nav>
                    </div>
                </section>

                <div v-if="hasOrders" class="space-y-4">
                    <article
                        v-for="order in orders.data"
                        :key="order.id"
                        class="onyx-panel overflow-hidden"
                    >
                        <div class="p-4 border-b border-black/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.1em] text-black/55">Order No.</p>
                                    <p class="font-mono font-medium text-black">{{ order.order_number }}</p>
                                </div>
                                <div class="hidden sm:block text-black/20">|</div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.1em] text-black/55">Date</p>
                                    <p class="text-black">{{ order.created_at }}</p>
                                </div>
                            </div>
                            <OrderStatusBadge :status="order.status" :label="order.status_label" />
                        </div>

                        <Link :href="route('orders.show', order.id)" class="block p-4 hover:bg-black/[0.03] transition-colors">
                            <div class="flex gap-4">
                                <div v-if="order.first_item" class="w-20 h-20 bg-black/5 border border-black/10 overflow-hidden flex-shrink-0">
                                    <img
                                        v-if="order.first_item.product_image"
                                        :src="order.first_item.product_image"
                                        :alt="order.first_item.product_name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-black/35" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p v-if="order.first_item" class="font-medium text-black truncate">
                                        {{ order.first_item.product_name }}
                                    </p>
                                    <p class="text-sm text-black/55 mt-1">
                                        {{ order.total_items }} item
                                    </p>
                                    <div v-if="order.shipping_info" class="mt-2 text-xs text-black/55 uppercase tracking-[0.08em]">
                                        {{ order.shipping_info }}
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-xs uppercase tracking-[0.08em] text-black/55">Order Total</p>
                                    <p class="text-lg font-bold text-black">{{ order.formatted_total }}</p>
                                </div>
                            </div>
                        </Link>

                        <div class="px-4 py-3 bg-black/[0.03] border-t border-black/10 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span v-if="order.status === 'pending_payment'" class="text-sm text-amber-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Awaiting payment
                                </span>
                                <span v-else-if="order.status === 'shipped'" class="text-sm text-sky-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                    In transit
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('orders.show', order.id)"
                                    class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border border-black bg-white text-black hover:bg-black hover:text-white transition-colors"
                                >
                                    View Details
                                </Link>

                                <Link
                                    v-if="order.status === 'pending_payment'"
                                    :href="route('orders.show', order.id)"
                                    class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border border-black bg-black text-white hover:bg-white hover:text-black transition-colors"
                                >
                                    Pay Now
                                </Link>
                                <Link
                                    v-if="order.status === 'shipped'"
                                    :href="route('orders.track', order.id)"
                                    class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border border-sky-900 bg-sky-700 text-white hover:bg-sky-800 transition-colors"
                                >
                                    Track
                                </Link>
                            </div>
                        </div>
                    </article>

                    <div v-if="orders.links && orders.links.length > 3" class="flex justify-center mt-8">
                        <nav class="flex items-center flex-wrap gap-2" aria-label="Order pagination">
                            <template v-for="(link, index) in orders.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border transition-colors"
                                    :class="link.active
                                        ? 'bg-black text-white border-black'
                                        : 'bg-white text-black border-black hover:bg-black hover:text-white'"
                                >
                                    <span v-html="link.label"></span>
                                </Link>
                                <span
                                    v-else
                                    class="px-3 py-2 text-xs font-semibold uppercase tracking-[0.1em] border border-black/20 bg-black/5 text-black/35"
                                    v-html="link.label"
                                />
                            </template>
                        </nav>
                    </div>
                </div>

                <section v-else class="onyx-panel p-12 text-center">
                    <svg class="mx-auto h-20 w-20 text-black/35 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="onyx-title text-2xl mb-2">No Orders Yet</h3>
                    <p class="text-black/65 mb-6">
                        {{ selectedStatus === 'all'
                            ? 'Start shopping and find your favorite products!'
                            : `No orders with status "${getStatusLabel(selectedStatus)}"` }}
                    </p>
                    <Link
                        :href="route('products.index')"
                        class="onyx-action"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Start Shopping
                    </Link>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
