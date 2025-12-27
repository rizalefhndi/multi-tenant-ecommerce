<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

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
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        shipped: 'bg-purple-100 text-purple-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'Menunggu',
        processing: 'Diproses',
        shipped: 'Dikirim',
        completed: 'Selesai',
        cancelled: 'Dibatalkan',
    };
    return labels[status] || status;
};

// Simple bar chart rendering
const maxRevenue = computed(() => Math.max(...(props.monthlyRevenue?.data || [1])));
const maxDailyRevenue = computed(() => Math.max(...(props.revenueChart?.data || [1])));
</script>

<template>
    <Head title="Analytics Dashboard" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Hero Header -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <h1 class="text-3xl font-bold text-white">Analytics Dashboard</h1>
                    <p class="text-white/80 mt-1">Pantau performa toko Anda secara real-time</p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Revenue Today -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span 
                                class="text-sm font-medium px-2 py-1 rounded-full"
                                :class="stats?.revenue?.growth >= 0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100'"
                            >
                                {{ stats?.revenue?.growth >= 0 ? '+' : '' }}{{ stats?.revenue?.growth }}%
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats?.revenue?.this_month) }}</p>
                        <p class="text-sm text-gray-500 mt-1">Pendapatan bulan ini</p>
                    </div>

                    <!-- Orders Today -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                                {{ stats?.orders?.today }} hari ini
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats?.orders?.this_month) }}</p>
                        <p class="text-sm text-gray-500 mt-1">Pesanan bulan ini</p>
                    </div>

                    <!-- Pending Orders -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats?.orders?.pending) }}</p>
                        <p class="text-sm text-gray-500 mt-1">Menunggu pembayaran</p>
                        <Link 
                            href="/admin/orders?status=pending" 
                            class="inline-flex items-center text-sm text-orange-600 font-medium mt-2 hover:text-orange-700"
                        >
                            Lihat semua
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>

                    <!-- Products -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <span 
                                v-if="stats?.products?.low_stock > 0"
                                class="text-sm font-medium text-red-600 bg-red-100 px-2 py-1 rounded-full"
                            >
                                {{ stats?.products?.low_stock }} stok rendah
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats?.products?.total) }}</p>
                        <p class="text-sm text-gray-500 mt-1">Total produk ({{ stats?.products?.active }} aktif)</p>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Monthly Revenue Chart -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Pendapatan Bulanan</h3>
                        <div class="flex items-end justify-between h-48 gap-2">
                            <div 
                                v-for="(value, index) in monthlyRevenue?.data" 
                                :key="index"
                                class="flex-1 flex flex-col items-center"
                            >
                                <div 
                                    class="w-full bg-gradient-to-t from-indigo-500 to-purple-500 rounded-t-lg transition-all hover:from-indigo-600 hover:to-purple-600"
                                    :style="{ height: `${(value / maxRevenue) * 100}%`, minHeight: '4px' }"
                                ></div>
                                <span class="text-xs text-gray-500 mt-2">{{ monthlyRevenue?.labels?.[index] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sales by Day of Week -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Penjualan per Hari</h3>
                        <div class="space-y-4">
                            <div 
                                v-for="(value, index) in salesByDayOfWeek?.data" 
                                :key="index"
                                class="flex items-center gap-4"
                            >
                                <span class="w-16 text-sm text-gray-600">{{ salesByDayOfWeek?.labels?.[index] }}</span>
                                <div class="flex-1 h-8 bg-gray-100 rounded-lg overflow-hidden">
                                    <div 
                                        class="h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg transition-all"
                                        :style="{ width: `${(value / Math.max(...salesByDayOfWeek?.data, 1)) * 100}%` }"
                                    ></div>
                                </div>
                                <span class="w-24 text-sm font-medium text-gray-900 text-right">{{ formatCurrency(value) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Products -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Produk Terlaris</h3>
                            <Link href="/products" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                Lihat Semua
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div 
                                v-for="(product, index) in topProducts" 
                                :key="product.id"
                                class="flex items-center gap-4"
                            >
                                <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-sm font-bold text-gray-500">
                                    {{ index + 1 }}
                                </span>
                                <img 
                                    :src="product.image || 'https://via.placeholder.com/48'" 
                                    :alt="product.name"
                                    class="w-12 h-12 rounded-lg object-cover"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ product.name }}</p>
                                    <p class="text-sm text-gray-500">{{ formatCurrency(product.price) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">{{ product.total_sold }}</p>
                                    <p class="text-xs text-gray-500">terjual</p>
                                </div>
                            </div>
                            <div v-if="!topProducts?.length" class="text-center py-8 text-gray-500">
                                Belum ada data penjualan
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Pesanan Terbaru</h3>
                            <Link href="/admin/orders" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                Lihat Semua
                            </Link>
                        </div>
                        <div class="space-y-4">
                            <div 
                                v-for="order in recentOrders" 
                                :key="order.id"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ order.order_number }}</p>
                                        <p class="text-sm text-gray-500">{{ order.customer }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">{{ formatCurrency(order.total) }}</p>
                                    <span 
                                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="getStatusColor(order.status)"
                                    >
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="!recentOrders?.length" class="text-center py-8 text-gray-500">
                                Belum ada pesanan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
