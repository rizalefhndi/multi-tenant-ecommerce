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

// Demo data jika props kosong
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
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Welcome Section -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">{{ greeting }}! 👋</h1>
                    <p class="text-gray-500 mt-1">Here's your store summary for today</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Products -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Products</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.totalProducts }}</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-green-600 font-medium">+12%</span>
                            <span class="text-gray-400 ml-2">from last month</span>
                        </div>
                    </div>

                    <!-- Total Orders -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Orders</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.totalOrders }}</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-green-600 font-medium">+8%</span>
                            <span class="text-gray-400 ml-2">from last month</span>
                        </div>
                    </div>

                    <!-- Revenue -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Revenue</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(stats.totalRevenue) }}</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-green-600 font-medium">+23%</span>
                            <span class="text-gray-400 ml-2">from last month</span>
                        </div>
                    </div>

                    <!-- Pending Orders -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Need Processing</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.pendingOrders }}</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <Link href="/orders?status=pending" class="mt-4 inline-flex items-center text-sm text-orange-600 font-medium hover:text-orange-700">
                            View orders
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Recent Orders -->
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
                                <Link href="/orders" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                    View All
                                </Link>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div 
                                v-for="order in recentOrders" 
                                :key="order.id"
                                class="p-4 hover:bg-gray-50 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1"
                                            :class="getStatusColor(order.status)"
                                        >
                                            {{ getStatusLabel(order.status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="recentOrders.length === 0" class="p-8 text-center text-gray-500">
                            No orders yet
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                        <div class="space-y-3">
                            <Link 
                                href="/products/create" 
                                class="flex items-center gap-4 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl hover:from-indigo-100 hover:to-purple-100 transition-colors group"
                            >
                                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Add Product</p>
                                    <p class="text-sm text-gray-500">Add a new product to store</p>
                                </div>
                            </Link>

                            <Link 
                                href="/admin/orders" 
                                class="flex items-center gap-4 p-4 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-xl hover:from-orange-100 hover:to-yellow-100 transition-colors group"
                            >
                                <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Manage Orders</p>
                                    <p class="text-sm text-gray-500">Process incoming orders</p>
                                </div>
                            </Link>

                            <Link 
                                href="/settings" 
                                class="flex items-center gap-4 p-4 bg-gradient-to-r from-gray-50 to-slate-50 rounded-xl hover:from-gray-100 hover:to-slate-100 transition-colors group"
                            >
                                <div class="w-10 h-10 bg-gray-600 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Store Settings</p>
                                    <p class="text-sm text-gray-500">Customize store appearance</p>
                                </div>
                            </Link>

                            <Link 
                                href="/subscription" 
                                class="flex items-center gap-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl hover:from-green-100 hover:to-emerald-100 transition-colors group"
                            >
                                <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Subscription</p>
                                    <p class="text-sm text-gray-500">View subscription plans</p>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
