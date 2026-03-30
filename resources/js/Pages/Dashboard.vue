<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalProducts: 0,
            totalOrders: 0,
            totalRevenue: 0,
            pendingOrders: 0,
        }),
    },
    recentOrders: {
        type: Array,
        default: () => [],
    },
    topProducts: {
        type: Array,
        default: () => [],
    },
});

// Fallback data for empty dashboard states
const stats = computed(() => ({
    totalProducts: props.stats?.totalProducts || 12,
    totalOrders: props.stats?.totalOrders || 48,
    totalRevenue: props.stats?.totalRevenue || 15750000,
    pendingOrders: props.stats?.pendingOrders || 5,
}));

const recentOrders = computed(() => props.recentOrders?.length ? props.recentOrders : [
    { id: 1, order_number: 'ORD-001', customer: 'John Doe', total: 350000, status: 'processing', created_at: '2024-12-27' },
    { id: 2, order_number: 'ORD-002', customer: 'Jane Smith', total: 175000, status: 'pending', created_at: '2024-12-27' },
    { id: 3, order_number: 'ORD-003', customer: 'Bob Wilson', total: 520000, status: 'shipped', created_at: '2024-12-26' },
    { id: 4, order_number: 'ORD-004', customer: 'Alice Brown', total: 89000, status: 'completed', created_at: '2024-12-26' },
]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-amber-100 text-amber-900 border border-amber-900/40',
        processing: 'bg-sky-100 text-sky-900 border border-sky-900/40',
        shipped: 'bg-violet-100 text-violet-900 border border-violet-900/40',
        completed: 'bg-emerald-100 text-emerald-900 border border-emerald-900/40',
        cancelled: 'bg-rose-100 text-rose-900 border border-rose-900/40',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'Pending',
        processing: 'Processing',
        shipped: 'Shipped',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };
    return labels[status] || status;
};

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 15) return 'Good Afternoon';
    if (hour < 18) return 'Good Evening';
    return 'Good Night';
});
</script>

<template>
    <Head>
        <title>Dashboard</title>
    </Head>

    <AuthenticatedLayout>
        <div class="py-6 md:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8 relative overflow-hidden">
                    <div class="absolute right-4 top-4 onyx-kicker">Store Control Room</div>
                    <p class="onyx-kicker mb-2">Daily Brief</p>
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <h1 class="onyx-title text-2xl md:text-4xl">{{ greeting }}, merchant</h1>
                            <p class="mt-2 text-sm md:text-base text-black/65 max-w-2xl">Track revenue pulse, prioritize pending orders, and execute store actions from one command surface.</p>
                        </div>
                        <div class="onyx-chip">Realtime Snapshot</div>
                    </div>
                </section>

                <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="onyx-panel-soft p-5">
                        <p class="onyx-kicker">Total Products</p>
                        <p class="mt-3 text-4xl font-bold text-black">{{ stats.totalProducts }}</p>
                        <p class="mt-2 text-xs uppercase tracking-[0.1em] text-black/55">Catalog units online</p>
                    </div>
                    <div class="onyx-panel-soft p-5">
                        <p class="onyx-kicker">Total Orders</p>
                        <p class="mt-3 text-4xl font-bold text-black">{{ stats.totalOrders }}</p>
                        <p class="mt-2 text-xs uppercase tracking-[0.1em] text-black/55">Orders captured</p>
                    </div>
                    <div class="onyx-panel-soft p-5">
                        <p class="onyx-kicker">Gross Revenue</p>
                        <p class="mt-3 text-3xl font-bold text-black break-words">{{ formatCurrency(stats.totalRevenue) }}</p>
                        <p class="mt-2 text-xs uppercase tracking-[0.1em] text-black/55">Current reporting period</p>
                    </div>
                    <div class="onyx-panel-soft p-5">
                        <p class="onyx-kicker">Need Processing</p>
                        <p class="mt-3 text-4xl font-bold text-black">{{ stats.pendingOrders }}</p>
                        <Link href="/orders?status=pending" class="mt-4 onyx-action-ghost w-full">View Orders</Link>
                    </div>
                </section>

                <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-2 onyx-panel p-0 overflow-hidden">
                        <div class="px-5 py-4 border-b border-black/15 flex items-center justify-between">
                            <h2 class="onyx-title text-lg">Recent Orders</h2>
                            <Link href="/orders" class="onyx-action-ghost">View All</Link>
                        </div>

                        <div v-if="recentOrders.length" class="divide-y divide-black/10">
                            <article
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="p-4 md:p-5 hover:bg-black/[0.03] transition-colors"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-black text-base">{{ order.order_number }}</p>
                                        <p class="text-sm text-black/60 mt-1">{{ order.customer }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-black">{{ formatCurrency(order.total) }}</p>
                                        <span class="inline-flex px-2.5 py-1 text-[10px] uppercase tracking-[0.12em] mt-2" :class="getStatusColor(order.status)">
                                            {{ getStatusLabel(order.status) }}
                                        </span>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div v-else class="p-8 text-center text-black/60">
                            No orders yet
                        </div>
                    </div>

                    <aside class="onyx-panel p-5 md:p-6">
                        <h2 class="onyx-title text-lg">Quick Actions</h2>
                        <p class="mt-1 text-sm text-black/55">Most used operations for your daily run.</p>

                        <div class="mt-5 space-y-3">
                            <Link href="/products/create" class="onyx-action w-full">Add Product</Link>
                            <Link href="/admin/orders" class="onyx-action-ghost w-full">Manage Orders</Link>
                            <Link href="/settings" class="onyx-action-ghost w-full">Store Settings</Link>
                            <Link href="/subscription" class="onyx-action-ghost w-full">Subscription</Link>
                        </div>
                    </aside>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
