<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    revenueChart: Object,
    ordersChart: Object,
    orderStatusDistribution: Object,
    topProducts: Array,
    recentOrders: Array,
    monthlyRevenue: Object,
    salesByDayOfWeek: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value || 0);
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-amber-100 text-amber-900 border border-amber-900/30',
        processing: 'bg-sky-100 text-sky-900 border border-sky-900/30',
        shipped: 'bg-violet-100 text-violet-900 border border-violet-900/30',
        completed: 'bg-emerald-100 text-emerald-900 border border-emerald-900/30',
        cancelled: 'bg-rose-100 text-rose-900 border border-rose-900/30',
    };
    return colors[status] || 'bg-black/10 text-black border border-black/20';
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

const maxRevenue = computed(() => Math.max(...(props.monthlyRevenue?.data || [1])));
</script>

<template>
    <Head>
        <title>Analytics</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <p class="onyx-kicker">Admin Intelligence</p>
                            <h1 class="onyx-title text-2xl md:text-4xl mt-2">Analytics Dashboard</h1>
                            <p class="text-black/60 mt-2">Real-time view of revenue, order throughput, and best-selling products.</p>
                        </div>
                        <Link href="/admin/orders" class="onyx-action-ghost">Open Orders</Link>
                    </div>
                </section>

                <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="onyx-panel-soft p-5">
                        <p class="onyx-kicker">Revenue This Month</p>
                        <p class="text-3xl font-bold text-black mt-3 break-words">{{ formatCurrency(stats?.revenue?.this_month) }}</p>
                        <p class="text-xs uppercase tracking-[0.08em] mt-2" :class="stats?.revenue?.growth >= 0 ? 'text-emerald-700' : 'text-rose-700'">
                            {{ stats?.revenue?.growth >= 0 ? '+' : '' }}{{ stats?.revenue?.growth }}% growth
                        </p>
                    </div>

                    <div class="onyx-panel-soft p-5">
                        <p class="onyx-kicker">Orders This Month</p>
                        <p class="text-3xl font-bold text-black mt-3">{{ formatNumber(stats?.orders?.this_month) }}</p>
                        <p class="text-xs uppercase tracking-[0.08em] text-black/55 mt-2">{{ stats?.orders?.today }} today</p>
                    </div>

                    <div class="onyx-panel-soft p-5">
                        <p class="onyx-kicker">Pending Payment</p>
                        <p class="text-3xl font-bold text-black mt-3">{{ formatNumber(stats?.orders?.pending) }}</p>
                        <Link href="/admin/orders?status=pending_payment" class="inline-flex mt-3 text-xs uppercase tracking-[0.08em] text-black underline">View pending</Link>
                    </div>

                    <div class="onyx-panel-soft p-5">
                        <p class="onyx-kicker">Products</p>
                        <p class="text-3xl font-bold text-black mt-3">{{ formatNumber(stats?.products?.total) }}</p>
                        <p class="text-xs uppercase tracking-[0.08em] text-black/55 mt-2">{{ stats?.products?.active }} active • {{ stats?.products?.low_stock }} low stock</p>
                    </div>
                </section>

                <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="onyx-panel p-5 md:p-6">
                        <h3 class="onyx-title text-sm mb-5">Monthly Revenue</h3>
                        <div class="flex items-end justify-between h-52 gap-2">
                            <div
                                v-for="(value, index) in monthlyRevenue?.data"
                                :key="index"
                                class="flex-1 flex flex-col items-center"
                            >
                                <div
                                    class="w-full bg-black rounded-t-sm"
                                    :style="{ height: `${(value / maxRevenue) * 100}%`, minHeight: '4px' }"
                                ></div>
                                <span class="text-[10px] uppercase tracking-[0.06em] text-black/55 mt-2">{{ monthlyRevenue?.labels?.[index] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="onyx-panel p-5 md:p-6">
                        <h3 class="onyx-title text-sm mb-5">Sales by Day</h3>
                        <div class="space-y-3.5">
                            <div
                                v-for="(value, index) in salesByDayOfWeek?.data"
                                :key="index"
                                class="flex items-center gap-3"
                            >
                                <span class="w-16 text-xs uppercase tracking-[0.08em] text-black/65">{{ salesByDayOfWeek?.labels?.[index] }}</span>
                                <div class="flex-1 h-7 border border-black/15 bg-black/[0.04] overflow-hidden">
                                    <div
                                        class="h-full bg-black"
                                        :style="{ width: `${(value / Math.max(...salesByDayOfWeek?.data, 1)) * 100}%` }"
                                    ></div>
                                </div>
                                <span class="w-24 text-sm font-medium text-black text-right">{{ formatCurrency(value) }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="onyx-panel p-5 md:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="onyx-title text-sm">Top Products</h3>
                            <Link href="/products" class="onyx-action-ghost">Products</Link>
                        </div>
                        <div class="space-y-3.5">
                            <div
                                v-for="(product, index) in topProducts"
                                :key="product.id"
                                class="flex items-center gap-3"
                            >
                                <span class="w-6 h-6 border border-black/30 flex items-center justify-center text-xs font-bold text-black">{{ index + 1 }}</span>
                                <img
                                    :src="product.image || 'https://via.placeholder.com/48'"
                                    :alt="product.name"
                                    class="w-12 h-12 object-cover border border-black/15"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-black truncate">{{ product.name }}</p>
                                    <p class="text-sm text-black/60">{{ formatCurrency(product.price) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-black">{{ product.total_sold }}</p>
                                    <p class="text-xs uppercase tracking-[0.08em] text-black/55">sold</p>
                                </div>
                            </div>
                            <div v-if="!topProducts?.length" class="text-center py-8 text-black/55">
                                No sales data yet
                            </div>
                        </div>
                    </div>

                    <div class="onyx-panel p-5 md:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="onyx-title text-sm">Recent Orders</h3>
                            <Link href="/admin/orders" class="onyx-action">Orders</Link>
                        </div>
                        <div class="space-y-3.5">
                            <div
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="flex items-center justify-between p-3 border border-black/10 bg-black/[0.02]"
                            >
                                <div>
                                    <p class="font-medium text-black">{{ order.order_number }}</p>
                                    <p class="text-sm text-black/60">{{ order.customer }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-black">{{ formatCurrency(order.total) }}</p>
                                    <span class="inline-flex px-2 py-0.5 text-[10px] uppercase tracking-[0.08em] mt-1" :class="getStatusColor(order.status)">
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="!recentOrders?.length" class="text-center py-8 text-black/55">
                                No orders yet
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
