<script setup>
import { Link, router } from '@inertiajs/vue3';

/**
 * Props untuk ProductCard
 */
const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    showActions: {
        type: Boolean,
        default: true,
    },
});

/**
 * Format harga ke Rupiah
 */
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
};

/**
 * Add to cart
 */
const addToCart = () => {
    router.post(route('cart.add'), {
        product_id: props.product.id,
        quantity: 1,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Success message handled by flash
        },
    });
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
        <!-- Product Image -->
        <div class="relative h-48 bg-gray-200">
            <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <!-- Status Badges -->
            <div class="absolute top-2 right-2 flex gap-2">
                <span
                    v-if="!product.is_active"
                    class="px-2 py-1 text-xs font-semibold text-white bg-gray-500 rounded-full"
                >
                    Inactive
                </span>
                <span
                    v-if="product.stock <= 0"
                    class="px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded-full"
                >
                    Out of Stock
                </span>
                <span
                    v-else-if="product.stock < 10"
                    class="px-2 py-1 text-xs font-semibold text-white bg-orange-500 rounded-full"
                >
                    Low Stock
                </span>
            </div>
        </div>

        <!-- Product Info -->
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2 line-clamp-2">
                {{ product.name }}
            </h3>

            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                {{ product.description || 'No description available' }}
            </p>

            <div class="flex items-center justify-between mb-3">
                <span class="text-2xl font-bold text-indigo-600">
                    {{ formatPrice(product.price) }}
                </span>
                <span class="text-sm text-gray-500">
                    Stock: {{ product.stock }}
                </span>
            </div>

            <!-- Actions -->
            <div v-if="showActions" class="flex gap-2">
                <button
                    @click="addToCart"
                    :disabled="!product.is_active || product.stock <= 0"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200"
                >
                    <span v-if="product.stock > 0 && product.is_active">Add to Cart</span>
                    <span v-else>Unavailable</span>
                </button>

                <Link
                    :href="route('products.show', product.id)"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                >
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
