<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    products: Object,
});

const loadingProductId = ref(null);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const addToCart = (productId) => {
    loadingProductId.value = productId;
    router.post(route('cart.add'), {
        product_id: productId,
        quantity: 1,
    }, {
        preserveScroll: true,
        onFinish: () => {
            loadingProductId.value = null;
        },
    });
};
</script>

<template>
    <Head title="Shop" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Hero -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 class="text-4xl font-bold text-white mb-4">Welcome to MyStore</h1>
                    <p class="text-white/80 text-lg">Discover the best products at affordable prices</p>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Latest Products</h2>
                
                <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div 
                        v-for="product in products.data" 
                        :key="product.id"
                        class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow group"
                    >
                        <!-- Image (clickable) -->
                        <Link :href="route('products.show', product.id)" class="block aspect-square bg-gray-100 overflow-hidden cursor-pointer">
                            <img 
                                :src="product.image || 'https://via.placeholder.com/400'" 
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                        </Link>
                        
                        <!-- Info -->
                        <div class="p-4">
                            <Link :href="route('products.show', product.id)">
                                <h3 class="font-semibold text-gray-900 mb-1 truncate hover:text-indigo-600">{{ product.name }}</h3>
                            </Link>
                            <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ product.description }}</p>
                            
                            <div class="flex items-center justify-between">
                                <p class="text-lg font-bold text-indigo-600">{{ formatCurrency(product.price) }}</p>
                                <span 
                                    v-if="product.stock > 0"
                                    class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full"
                                >
                                    Stock: {{ product.stock }}
                                </span>
                                <span v-else class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                    Out of Stock
                                </span>
                            </div>
                            
                            <button 
                                v-if="product.stock > 0"
                                @click="addToCart(product.id)"
                                :disabled="loadingProductId === product.id"
                                class="w-full mt-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all disabled:opacity-50"
                            >
                                <span v-if="loadingProductId === product.id" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Adding...
                                </span>
                                <span v-else>+ Add to Cart</span>
                            </button>
                            <button 
                                v-else
                                disabled
                                class="w-full mt-4 py-2.5 bg-gray-200 text-gray-500 font-medium rounded-xl cursor-not-allowed"
                            >
                                Out of Stock
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-gray-500">No products available</p>
                </div>

                <!-- Pagination -->
                <div v-if="products.data.length > 0" class="mt-8 flex justify-center gap-2">
                    <template v-for="link in products.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                            :class="link.active 
                                ? 'bg-indigo-600 text-white' 
                                : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'"
                        >
                            <span v-html="link.label"></span>
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
