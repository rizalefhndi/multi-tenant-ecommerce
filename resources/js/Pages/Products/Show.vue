<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    product: Object,
});

const isDeleting = ref(false);

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

const deleteProduct = () => {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        isDeleting.value = true;
        router.delete(route('products.destroy', props.product.id), {
            onFinish: () => {
                isDeleting.value = false;
            },
        });
    }
};

const toggleStatus = () => {
    router.patch(route('products.toggle-status', props.product.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Hero Header -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Link 
                                :href="route('products.index')"
                                class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center transition-colors"
                            >
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </Link>
                            <div>
                                <h1 class="text-2xl font-bold text-white">Product Details</h1>
                                <p class="text-white/80 mt-1">View and manage product information</p>
                            </div>
                        </div>
                        <!-- Admin Actions -->
                        <div class="flex items-center gap-3">
                            <Link
                                :href="route('products.edit', product.id)"
                                class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white font-medium rounded-xl transition-colors flex items-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </Link>
                            <button
                                @click="deleteProduct"
                                :disabled="isDeleting"
                                class="px-4 py-2 bg-red-500/80 hover:bg-red-600 text-white font-medium rounded-xl transition-colors flex items-center gap-2 disabled:opacity-50"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                {{ isDeleting ? 'Deleting...' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 -mt-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Product Image -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden">
                                <img
                                    v-if="product.image"
                                    :src="product.image"
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-24 h-24 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm">No image available</span>
                                </div>
                            </div>
                        </div>

                        <!-- Product Description -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Description</h3>
                                </div>
                            </div>
                            <p class="text-gray-600 leading-relaxed">
                                {{ product.description || 'No description available.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Product Info Card -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ product.name }}</h2>
                            
                            <!-- Status Badges -->
                            <div class="flex flex-wrap gap-2 mb-6">
                                <span
                                    :class="[
                                        'px-3 py-1 text-sm font-medium rounded-full',
                                        product.is_active
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-700'
                                    ]"
                                >
                                    {{ product.is_active ? '✓ Active' : '✗ Inactive' }}
                                </span>
                                <span
                                    :class="[
                                        'px-3 py-1 text-sm font-medium rounded-full',
                                        product.stock > 0
                                            ? 'bg-blue-100 text-blue-700'
                                            : 'bg-red-100 text-red-700'
                                    ]"
                                >
                                    {{ product.stock > 0 ? `${product.stock} in stock` : 'Out of Stock' }}
                                </span>
                            </div>

                            <!-- Price -->
                            <div class="mb-6">
                                <p class="text-sm text-gray-500 mb-1">Price</p>
                                <p class="text-3xl font-bold text-indigo-600">{{ formatPrice(product.price) }}</p>
                            </div>

                            <!-- Quick Stats -->
                            <div class="grid grid-cols-2 gap-4 mb-6 pt-6 border-t border-gray-100">
                                <div>
                                    <p class="text-sm text-gray-500">Stock Level</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ product.stock }} units</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Status</p>
                                    <p class="text-lg font-semibold" :class="product.is_active ? 'text-green-600' : 'text-gray-600'">
                                        {{ product.is_active ? 'Published' : 'Draft' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Toggle Status -->
                            <div class="pt-6 border-t border-gray-100">
                                <button
                                    @click="toggleStatus"
                                    :class="[
                                        'w-full py-3 font-semibold rounded-xl transition-all flex items-center justify-center gap-2',
                                        product.is_active
                                            ? 'bg-gray-100 hover:bg-gray-200 text-gray-700'
                                            : 'bg-green-600 hover:bg-green-700 text-white'
                                    ]"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="product.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ product.is_active ? 'Deactivate Product' : 'Activate Product' }}
                                </button>
                            </div>

                            <!-- Edit Button -->
                            <div class="mt-4">
                                <Link
                                    :href="route('products.edit', product.id)"
                                    class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Product
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
