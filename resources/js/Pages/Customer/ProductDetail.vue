<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    product: Object,
    relatedProducts: Array,
});

const quantity = ref(1);
const isAddingToCart = ref(false);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const increaseQuantity = () => {
    if (quantity.value < props.product.stock) {
        quantity.value++;
    }
};

const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const addToCart = () => {
    isAddingToCart.value = true;
    router.post(route('cart.add'), {
        product_id: props.product.id,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isAddingToCart.value = false;
        },
    });
};
</script>

<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Product Detail -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6 lg:p-8">
                        <!-- Image -->
                        <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden">
                            <img 
                                :src="product.image || 'https://via.placeholder.com/600'" 
                                :alt="product.name"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <!-- Info -->
                        <div class="flex flex-col">
                            <!-- Breadcrumb -->
                            <nav class="text-sm text-gray-500 mb-4">
                                <Link :href="route('customer.home')" class="hover:text-indigo-600">Shop</Link>
                                <span class="mx-2">/</span>
                                <span class="text-gray-900">{{ product.name }}</span>
                            </nav>

                            <!-- Title & Price -->
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
                            <p class="text-3xl font-bold text-indigo-600 mb-6">{{ formatCurrency(product.price) }}</p>

                            <!-- Stock Status -->
                            <div class="flex items-center gap-3 mb-6">
                                <span 
                                    v-if="product.stock > 0"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    In Stock ({{ product.stock }})
                                </span>
                                <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    Out of Stock
                                </span>
                            </div>

                            <!-- Description -->
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Product Description</h3>
                                <p class="text-gray-600 leading-relaxed">{{ product.description || 'No description available' }}</p>
                            </div>

                            <!-- Quantity & Add to Cart -->
                            <div v-if="product.stock > 0" class="mt-auto space-y-4">
                                <!-- Quantity Selector -->
                                <div class="flex items-center gap-4">
                                    <span class="text-gray-700 font-medium">Quantity:</span>
                                    <div class="flex items-center border border-gray-200 rounded-xl">
                                        <button 
                                            @click="decreaseQuantity"
                                            class="w-10 h-10 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-l-xl transition-colors"
                                            :disabled="quantity <= 1"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="w-12 text-center font-semibold text-gray-900">{{ quantity }}</span>
                                        <button 
                                            @click="increaseQuantity"
                                            class="w-10 h-10 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-r-xl transition-colors"
                                            :disabled="quantity >= product.stock"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Add to Cart Button -->
                                <button 
                                    @click="addToCart"
                                    :disabled="isAddingToCart"
                                    class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                                >
                                    <svg v-if="isAddingToCart" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ isAddingToCart ? 'Adding...' : 'Add to Cart' }}
                                </button>
                            </div>

                            <div v-else class="mt-auto">
                                <button 
                                    disabled
                                    class="w-full py-4 bg-gray-200 text-gray-500 font-semibold rounded-xl cursor-not-allowed"
                                >
                                    Out of Stock
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <div v-if="relatedProducts?.length > 0" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <Link 
                            v-for="related in relatedProducts" 
                            :key="related.id"
                            :href="route('products.show', related.id)"
                            class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow group"
                        >
                            <div class="aspect-square bg-gray-100 overflow-hidden">
                                <img 
                                    :src="related.image || 'https://via.placeholder.com/300'" 
                                    :alt="related.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                            </div>
                            <div class="p-3">
                                <h3 class="font-medium text-gray-900 text-sm truncate">{{ related.name }}</h3>
                                <p class="text-indigo-600 font-semibold text-sm">{{ formatCurrency(related.price) }}</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
