<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStatusBadge from '@/Components/OrderStatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    orders: Object,
    statuses: Object,
    currentStatus: String,
    search: String,
    stats: Object,
});

const selectedStatus = ref(props.currentStatus || 'all');
const searchTerm = ref(props.search || '');

const applyFilters = () => {
    router.get(route('admin.orders.index'), {
        status: selectedStatus.value,
        search: searchTerm.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch(selectedStatus, () => {
    applyFilters();
});

let searchTimeout = null;
watch(searchTerm, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 450);
});

const hasOrders = computed(() => Array.isArray(props.orders?.data) && props.orders.data.length > 0);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const resetFilters = () => {
    selectedStatus.value = 'all';
    searchTerm.value = '';
    applyFilters();
};
</script>

<template>
    <Head>
        <title>Admin Orders</title>
    </Head>

    <AuthenticatedLayout>
        <div class="min-h-screen py-6 md:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="onyx-panel p-6 md:p-8">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                        <div>
                            <p class="onyx-kicker">Admin Ops</p>
                            <h1 class="onyx-title text-2xl md:text-4xl mt-2">Order Management</h1>
                            <p class="text-black/60 mt-2">Monitor payment pipeline, processing queue, and delivery execution from one board.</p>
                        </div>
                        <div class="onyx-chip">{{ orders?.total || 0 }} Total Orders</div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mt-6">
                        <div class="onyx-panel-soft p-3 text-center">
                            <p class="onyx-kicker">Pending</p>
                            <p class="text-2xl font-bold mt-1">{{ stats?.pending_payment || 0 }}</p>
                        </div>
                        <div class="onyx-panel-soft p-3 text-center">
                            <p class="onyx-kicker">Processing</p>
                            <p class="text-2xl font-bold mt-1">{{ stats?.needs_processing || 0 }}</p>
                        </div>
                        <div class="onyx-panel-soft p-3 text-center">
                            <p class="onyx-kicker">Shipping</p>
                            <p class="text-2xl font-bold mt-1">{{ stats?.needs_shipping || 0 }}</p>
                        </div>
                        <div class="onyx-panel-soft p-3 text-center">
                            <p class="onyx-kicker">Shipped</p>
                            <p class="text-2xl font-bold mt-1">{{ stats?.shipped || 0 }}</p>
                        </div>
                        <div class="onyx-panel-soft p-3 text-center">
                            <p class="onyx-kicker">Today</p>
                            <p class="text-2xl font-bold mt-1">{{ stats?.total_today || 0 }}</p>
                        </div>
                        <div class="onyx-panel-soft p-3 text-center">
                            <p class="onyx-kicker">Revenue</p>
                            <p class="text-sm font-bold mt-2 break-words">{{ formatCurrency(stats?.revenue_today || 0) }}</p>
                        </div>
                    </div>
                </section>

                <section class="onyx-panel p-5 md:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-[1fr_220px_auto] gap-3 items-end">
                        <div>
                            <p class="onyx-kicker mb-1.5">Search</p>
                            <input
                                v-model="searchTerm"
                                type="text"
                                placeholder="Find by order no, customer, or email"
                                class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none"
                            />
                        </div>

                        <div>
                            <p class="onyx-kicker mb-1.5">Status</p>
                            <select
                                v-model="selectedStatus"
                                class="h-11 w-full border border-black bg-white px-3 text-sm focus:outline-none"
                            >
                                <option value="all">All Status</option>
                                <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>

                        <button
                            @click="resetFilters"
                            class="onyx-action-ghost h-11 w-full md:w-auto"
                        >
                            Reset
                        </button>
                    </div>
                </section>

                <section v-if="hasOrders" class="space-y-4">
                    <article
                        v-for="order in orders.data"
                        :key="order.id"
                        class="onyx-panel overflow-hidden"
                    >
                        <div class="p-4 md:p-5 border-b border-black/10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.1em] text-black/55">Order Number</p>
                                <p class="font-mono font-semibold text-black">{{ order.order_number }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.1em] text-black/55">Customer</p>
                                <p class="font-medium text-black">{{ order.customer_name }}</p>
                                <p class="text-sm text-black/55">{{ order.customer_email }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.1em] text-black/55">Created</p>
                                <p class="text-black">{{ order.created_at }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-[0.1em] text-black/55">Total</p>
                                <p class="font-bold text-black">{{ order.formatted_total }}</p>
                            </div>
                            <OrderStatusBadge :status="order.status" :label="order.status_label" />
                        </div>

                        <div class="px-4 md:px-5 py-3 bg-black/[0.03] border-t border-black/10 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm text-black/65">
                                {{ order.total_items }} items • {{ order.payment_method || 'N/A' }}
                            </p>
                            <Link
                                :href="route('admin.orders.show', order.id)"
                                class="onyx-action"
                            >
                                Open Order
                            </Link>
                        </div>
                    </article>

                    <div v-if="orders.links && orders.links.length > 3" class="flex justify-center pt-2">
                        <nav class="flex flex-wrap gap-2" aria-label="Admin orders pagination">
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
                                >
                                    <span v-html="link.label"></span>
                                </span>
                            </template>
                        </nav>
                    </div>
                </section>

                <section v-else class="onyx-panel p-12 text-center">
                    <h3 class="onyx-title text-2xl">No Orders Found</h3>
                    <p class="text-black/60 mt-2">Try changing filter combinations or search keywords.</p>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
