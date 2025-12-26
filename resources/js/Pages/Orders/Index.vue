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
    <Head title="Pesanan Saya" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pesanan Saya</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filter Tabs -->
                <div class="bg-white rounded-xl shadow-sm mb-6 overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                            <button
                                @click="selectedStatus = 'all'"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                                :class="selectedStatus === 'all' 
                                    ? 'border-indigo-500 text-indigo-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Semua
                            </button>
                            <button
                                v-for="(label, status) in statuses"
                                :key="status"
                                @click="selectedStatus = status"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                                :class="selectedStatus === status 
                                    ? 'border-indigo-500 text-indigo-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                {{ label }}
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Orders List -->
                <div v-if="hasOrders" class="space-y-4">
                    <div
                        v-for="order in orders.data"
                        :key="order.id"
                        class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow"
                    >
                        <!-- Order Header -->
                        <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">No. Pesanan</p>
                                    <p class="font-mono font-medium text-gray-900">{{ order.order_number }}</p>
                                </div>
                                <div class="hidden sm:block text-gray-300">|</div>
                                <div>
                                    <p class="text-sm text-gray-500">Tanggal</p>
                                    <p class="text-gray-900">{{ order.created_at }}</p>
                                </div>
                            </div>
                            <OrderStatusBadge :status="order.status" :label="order.status_label" />
                        </div>

                        <!-- Order Content -->
                        <Link :href="route('orders.show', order.id)" class="block p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex gap-4">
                                <!-- Product Image -->
                                <div v-if="order.first_item" class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    <img 
                                        v-if="order.first_item.product_image"
                                        :src="order.first_item.product_image" 
                                        :alt="order.first_item.product_name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Order Info -->
                                <div class="flex-1 min-w-0">
                                    <p v-if="order.first_item" class="font-medium text-gray-900 truncate">
                                        {{ order.first_item.product_name }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ order.total_items }} item
                                    </p>
                                    <div v-if="order.shipping_info" class="mt-2 text-xs text-gray-500">
                                        {{ order.shipping_info }}
                                    </div>
                                </div>

                                <!-- Total -->
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Total Belanja</p>
                                    <p class="text-lg font-bold text-gray-900">{{ order.formatted_total }}</p>
                                </div>
                            </div>
                        </Link>

                        <!-- Order Actions -->
                        <div class="px-4 py-3 bg-gray-50 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <!-- Status specific actions -->
                                <span v-if="order.status === 'pending_payment'" class="text-sm text-amber-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Menunggu pembayaran
                                </span>
                                <span v-else-if="order.status === 'shipped'" class="text-sm text-blue-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                    Dalam pengiriman
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('orders.show', order.id)"
                                    class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-colors"
                                >
                                    Lihat Detail
                                </Link>
                                
                                <!-- Conditional buttons based on status -->
                                <Link
                                    v-if="order.status === 'pending_payment'"
                                    :href="route('orders.show', order.id)"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors"
                                >
                                    Bayar Sekarang
                                </Link>
                                <Link
                                    v-if="order.status === 'shipped'"
                                    :href="route('orders.track', order.id)"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                                >
                                    Lacak
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.links && orders.links.length > 3" class="flex justify-center mt-8">
                        <nav class="flex items-center gap-1">
                            <template v-for="(link, index) in orders.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-4 py-2 text-sm rounded-lg transition-colors"
                                    :class="link.active 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'text-gray-600 hover:bg-gray-100'"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="px-4 py-2 text-sm text-gray-400"
                                    v-html="link.label"
                                />
                            </template>
                        </nav>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-2">Belum ada pesanan</h3>
                    <p class="text-gray-600 mb-6">
                        {{ selectedStatus === 'all' 
                            ? 'Ayo mulai belanja dan temukan produk favoritmu!' 
                            : `Tidak ada pesanan dengan status "${getStatusLabel(selectedStatus)}"` }}
                    </p>
                    <Link
                        :href="route('products.index')"
                        class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Mulai Belanja
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
